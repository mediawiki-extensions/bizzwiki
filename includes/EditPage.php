<?php
/**
 * Contains the EditPage class
 */

/**
 * The edit page/HTML interface (split from Article)
 * The actual database and text munging is still in Article,
 * but it should get easier to call those from alternate
 * interfaces.
 */
class EditPage {
	var $mArticle;
	var $mTitle;
	var $mMetaData = '';
	var $isConflict = false;
	var $isCssJsSubpage = false;
	var $deletedSinceEdit = false;
	var $formtype;
	var $firsttime;
	var $lastDelete;
	var $mTokenOk = false;
	var $mTriedSave = false;
	var $tooBig = false;
	var $kblength = false;
	var $missingComment = false;
	var $missingSummary = false;
	var $allowBlankSummary = false;
	var $autoSumm = '';
	var $hookError = '';
	var $mPreviewTemplates;

	# Form values
	var $save = false, $preview = false, $diff = false;
	var $minoredit = false, $watchthis = false, $recreate = false;
	var $textbox1 = '', $textbox2 = '', $summary = '';
	var $edittime = '', $section = '', $starttime = '';
	var $oldid = 0, $editintro = '', $scrolltop = null;

	# Placeholders for text injection by hooks (must be HTML)
	# extensions should take care to _append_ to the present value
	public $editFormPageTop; // Before even the preview
	public $editFormTextTop;
	public $editFormTextAfterWarn;
	public $editFormTextAfterTools;
	public $editFormTextBottom;

	/**
	 * @todo document
	 * @param $article
	 */
	function EditPage( $article ) {
		$this->mArticle =& $article;
		global $wgTitle;
		$this->mTitle =& $wgTitle;

		# Placeholders for text injection by hooks (empty per default)
		$this->editFormPageTop =
		$this->editFormTextTop =
		$this->editFormTextAfterWarn =
		$this->editFormTextAfterTools =
		$this->editFormTextBottom = "";
	}
	
	/**
	 * Fetch initial editing page content.
	 */
	private function getContent( $def_text = '' ) {
		global $wgOut, $wgRequest, $wgParser;

		# Get variables from query string :P
		$section = $wgRequest->getVal( 'section' );
		$preload = $wgRequest->getVal( 'preload' );
		$undoafter = $wgRequest->getVal( 'undoafter' );
		$undo = $wgRequest->getVal( 'undo' );

		wfProfileIn( __METHOD__ );

		$text = '';
		if( !$this->mTitle->exists() ) {
			if ( $this->mTitle->getNamespace() == NS_MEDIAWIKI ) {
				# If this is a system message, get the default text. 
				$text = wfMsgWeirdKey ( $this->mTitle->getText() ) ;
			} else {
				# If requested, preload some text.
				$text = $this->getPreloadedText( $preload );
			}
			# We used to put MediaWiki:Newarticletext here if
			# $text was empty at this point.
			# This is now shown above the edit box instead.
		} else {
			// FIXME: may be better to use Revision class directly
			// But don't mess with it just yet. Article knows how to
			// fetch the page record from the high-priority server,
			// which is needed to guarantee we don't pick up lagged
			// information.

			$text = $this->mArticle->getContent();

			if ( $undo > 0 && $undo > $undoafter ) {
				# Undoing a specific edit overrides section editing; section-editing
				# doesn't work with undoing.
				if ( $undoafter ) {
					$undorev = Revision::newFromId($undo);
					$oldrev = Revision::newFromId($undoafter);
				} else {
					$undorev = Revision::newFromId($undo);
					$oldrev = $undorev ? $undorev->getPrevious() : null;
				}

				#Sanity check, make sure it's the right page.
				# Otherwise, $text will be left as-is.
				if ( !is_null($undorev) && !is_null($oldrev) && $undorev->getPage()==$oldrev->getPage() && $undorev->getPage()==$this->mArticle->getID() ) {
					$undorev_text = $undorev->getText();
					$oldrev_text = $oldrev->getText();
					$currev_text = $text;

					#No use doing a merge if it's just a straight revert.
					if ( $currev_text != $undorev_text ) {
						$result = wfMerge($undorev_text, $oldrev_text, $currev_text, $text);
					} else {
						$text = $oldrev_text;
						$result = true;
					}
				} else {
					// Failed basic sanity checks.
					// Older revisions may have been removed since the link
					// was created, or we may simply have got bogus input.
					$result = false;
				}

				if( $result ) {
					# Inform the user of our success and set an automatic edit summary
					$this->editFormPageTop .= $wgOut->parse( wfMsgNoTrans( 'undo-success' ) );
					$firstrev = $oldrev->getNext();
					# If we just undid one rev, use an autosummary
					if ( $firstrev->mId == $undo ) {
						$this->summary = wfMsgForContent('undo-summary', $undo, $undorev->getUserText());
					}
					$this->formtype = 'diff';
				} else {
					# Warn the user that something went wrong
					$this->editFormPageTop .= $wgOut->parse( wfMsgNoTrans( 'undo-failure' ) );
				}
			} else if( $section != '' ) {
				if( $section == 'new' ) {
					$text = $this->getPreloadedText( $preload );
				} else {
					$text = $wgParser->getSection( $text, $section, $def_text );
				}
			}
		}

		wfProfileOut( __METHOD__ );
		return $text;
	}

	/**
	 * Get the contents of a page from its title and remove includeonly tags
	 *
	 * @param $preload String: the title of the page.
	 * @return string The contents of the page.
	 */
	private function getPreloadedText($preload) {
		if ( $preload === '' )
			return '';
		else {
			$preloadTitle = Title::newFromText( $preload );
			if ( isset( $preloadTitle ) && $preloadTitle->userCanRead() ) {
				$rev=Revision::newFromTitle($preloadTitle);
				if ( is_object( $rev ) ) {
					$text = $rev->getText();
					// TODO FIXME: AAAAAAAAAAA, this shouldn't be implementing
					// its own mini-parser! -ævar
					$text = preg_replace( '~</?includeonly>~', '', $text );
					return $text;
				} else
					return '';
			}
		}
	}

	/**
	 * This is the function that extracts metadata from the article body on the first view.
	 * To turn the feature on, set $wgUseMetadataEdit = true ; in LocalSettings
	 *  and set $wgMetadataWhitelist to the *full* title of the template whitelist
	 */
	function extractMetaDataFromArticle () {
		global $wgUseMetadataEdit , $wgMetadataWhitelist , $wgLang ;
		$this->mMetaData = '' ;
		if ( !$wgUseMetadataEdit ) return ;
		if ( $wgMetadataWhitelist == '' ) return ;
		$s = '' ;
		$t = $this->getContent();

		# MISSING : <nowiki> filtering

		# Categories and language links
		$t = explode ( "\n" , $t ) ;
		$catlow = strtolower ( $wgLang->getNsText ( NS_CATEGORY ) ) ;
		$cat = $ll = array() ;
		foreach ( $t AS $key => $x )
		{
			$y = trim ( strtolower ( $x ) ) ;
			while ( substr ( $y , 0 , 2 ) == '[[' )
			{
				$y = explode ( ']]' , trim ( $x ) ) ;
				$first = array_shift ( $y ) ;
				$first = explode ( ':' , $first ) ;
				$ns = array_shift ( $first ) ;
				$ns = trim ( str_replace ( '[' , '' , $ns ) ) ;
				if ( strlen ( $ns ) == 2 OR strtolower ( $ns ) == $catlow )
				{
					$add = '[[' . $ns . ':' . implode ( ':' , $first ) . ']]' ;
					if ( strtolower ( $ns ) == $catlow ) $cat[] = $add ;
					else $ll[] = $add ;
					$x = implode ( ']]' , $y ) ;
					$t[$key] = $x ;
					$y = trim ( strtolower ( $x ) ) ;
				}
			}
		}
		if ( count ( $cat ) ) $s .= implode ( ' ' , $cat ) . "\n" ;
		if ( count ( $ll ) ) $s .= implode ( ' ' , $ll ) . "\n" ;
		$t = implode ( "\n" , $t ) ;

		# Load whitelist
		$sat = array () ; # stand-alone-templates; must be lowercase
		$wl_title = Title::newFromText ( $wgMetadataWhitelist ) ;
		$wl_article = new Article ( $wl_title ) ;
		$wl = explode ( "\n" , $wl_article->getContent() ) ;
		foreach ( $wl AS $x )
		{
			$isentry = false ;
			$x = trim ( $x ) ;
			while ( substr ( $x , 0 , 1 ) == '*' )
			{
				$isentry = true ;
				$x = trim ( substr ( $x , 1 ) ) ;
			}
			if ( $isentry )
			{
				$sat[] = strtolower ( $x ) ;
			}

		}

		# Templates, but only some
		$t = explode ( '{{' , $t ) ;
		$tl = array () ;
		foreach ( $t AS $key => $x )
		{
			$y = explode ( '}}' , $x , 2 ) ;
			if ( count ( $y ) == 2 )
			{
				$z = $y[0] ;
				$z = explode ( '|' , $z ) ;
				$tn = array_shift ( $z ) ;
				if ( in_array ( strtolower ( $tn ) , $sat ) )
				{
					$tl[] = '{{' . $y[0] . '}}' ;
					$t[$key] = $y[1] ;
					$y = explode ( '}}' , $y[1] , 2 ) ;
				}
				else $t[$key] = '{{' . $x ;
			}
			else if ( $key != 0 ) $t[$key] = '{{' . $x ;
			else $t[$key] = $x ;
		}
		if ( count ( $tl ) ) $s .= implode ( ' ' , $tl ) ;
		$t = implode ( '' , $t ) ;

		$t = str_replace ( "\n\n\n" , "\n" , $t ) ;
		$this->mArticle->mContent = $t ;
		$this->mMetaData = $s ;
	}

	function submit() {
		$this->edit();
	}

	/**
	 * This is the function that gets called for "action=edit". It
	 * sets up various member variables, then passes execution to
	 * another function, usually showEditForm()
	 *
	 * The edit form is self-submitting, so that when things like
	 * preview and edit conflicts occur, we get the same form back
	 * with the extra stuff added.  Only when the final submission
	 * is made and all is well do we actually save and redirect to
	 * the newly-edited page.
	 */
	function edit() {
		global $wgOut, $wgUser, $wgRequest, $wgTitle;
		global $wgEmailConfirmToEdit;

		if ( ! wfRunHooks( 'AlternateEdit', array( &$this ) ) )
			return;

		$fname = 'EditPage::edit';
		wfProfileIn( $fname );
		wfDebug( "$fname: enter\n" );

		// this is not an article
		$wgOut->setArticleFlag(false);

		$this->importFormData( $wgRequest );
		$this->firsttime = false;

		if( $this->live ) {
			$this->livePreview();
			wfProfileOut( $fname );
			return;
		}

		if ( ! $this->mTitle->userCan( 'edit' ) ) {
			wfDebug( "$fname: user can't edit\n" );
			$wgOut->readOnlyPage( $this->getContent(), true );
			wfProfileOut( $fname );
			return;
		}
		wfDebug( "$fname: Checking blocks\n" );
		if ( !$this->preview && !$this->diff && $wgUser->isBlockedFrom( $this->mTitle, !$this->save ) ) {
			# When previewing, don't check blocked state - will get caught at save time.
			# Also, check when starting edition is done against slave to improve performance.
			wfDebug( "$fname: user is blocked\n" );
			$this->blockedPage();
			wfProfileOut( $fname );
			return;
		}
		if ( !$wgUser->isAllowed('edit') ) {
			if ( $wgUser->isAnon() ) {
				wfDebug( "$fname: user must log in\n" );
				$this->userNotLoggedInPage();
				wfProfileOut( $fname );
				return;
			} else {
				wfDebug( "$fname: read-only page\n" );
				$wgOut->readOnlyPage( $this->getContent(), true );
				wfProfileOut( $fname );
				return;
			}
		}
		if ($wgEmailConfirmToEdit && !$wgUser->isEmailConfirmed()) {
			wfDebug("$fname: user must confirm e-mail address\n");
			$this->userNotConfirmedPage();
			wfProfileOut($fname);
			return;
		}
		if ( !$this->mTitle->userCan( 'create' ) && !$this->mTitle->exists() ) {
			wfDebug( "$fname: no create permission\n" );
			$this->noCreatePermission();
			wfProfileOut( $fname );
			return;
		}
		if ( wfReadOnly() ) {
			wfDebug( "$fname: read-only mode is engaged\n" );
			if( $this->save || $this->preview ) {
				$this->formtype = 'preview';
			} else if ( $this->diff ) {
				$this->formtype = 'diff';
			} else {
				$wgOut->readOnlyPage( $this->getContent() );
				wfProfileOut( $fname );
				return;
			}
		} else {
			if ( $this->save ) {
				$this->formtype = 'save';
			} else if ( $this->preview ) {
				$this->formtype = 'preview';
			} else if ( $this->diff ) {
				$this->formtype = 'diff';
			} else { # First time through
				$this->firsttime = true;
				if( $this->previewOnOpen() ) {
					$this->formtype = 'preview';
				} else {
					$this->extractMetaDataFromArticle () ;
					$this->formtype = 'initial';
				}
			}
		}

		wfProfileIn( "$fname-business-end" );

		$this->isConflict = false;
		// css / js subpages of user pages get a special treatment
		$this->isCssJsSubpage      = $wgTitle->isCssJsSubpage();
		$this->isValidCssJsSubpage = $wgTitle->isValidCssJsSubpage();

		/* Notice that we can't use isDeleted, because it returns true if article is ever deleted
		 * no matter it's current state
		 */
		$this->deletedSinceEdit = false;
		if ( $this->edittime != '' ) {
			/* Note that we rely on logging table, which hasn't been always there,
			 * but that doesn't matter, because this only applies to brand new
			 * deletes. This is done on every preview and save request. Move it further down
			 * to only perform it on saves
			 */
			if ( $this->mTitle->isDeleted() ) {
				$this->lastDelete = $this->getLastDelete();
				if ( !is_null($this->lastDelete) ) {
					$deletetime = $this->lastDelete->log_timestamp;
					if ( ($deletetime - $this->starttime) > 0 ) {
						$this->deletedSinceEdit = true;
					}
				}
			}
		}

		if(!$this->mTitle->getArticleID() && ('initial' == $this->formtype || $this->firsttime )) { # new article
			$this->showIntro();
		}
		if( $this->mTitle->isTalkPage() ) {
			$wgOut->addWikiText( wfMsg( 'talkpagetext' ) );
		}

		# Attempt submission here.  This will check for edit conflicts,
		# and redundantly check for locked database, blocked IPs, etc.
		# that edit() already checked just in case someone tries to sneak
		# in the back door with a hand-edited submission URL.

		if ( 'save' == $this->formtype ) {
			if ( !$this->attemptSave() ) {
				wfProfileOut( "$fname-business-end" );
				wfProfileOut( $fname );
				return;
			}
		}

		# First time through: get contents, set time for conflict
		# checking, etc.
		if ( 'initial' == $this->formtype || $this->firsttime ) {
			if ($this->initialiseForm() === false) {
				$this->noSuchSectionPage();
				wfProfileOut( "$fname-business-end" );
				wfProfileOut( $fname );
				return;
			}
			if( !$this->mTitle->getArticleId() ) 
				wfRunHooks( 'EditFormPreloadText', array( &$this->textbox1, &$this->mTitle ) );
		}

		$this->showEditForm();
		wfProfileOut( "$fname-business-end" );
		wfProfileOut( $fname );
	}
	/**
	 * Return true if this page should be previewed when the edit form
	 * is initially opened.
	 * @return bool
	 * @private
	 */
	function previewOnOpen() {
		global $wgUser;
		return $this->section != 'new' &&
			( ( $wgUser->getOption( 'previewonfirst' ) && $this->mTitle->exists() ) ||
				( $this->mTitle->getNamespace() == NS_CATEGORY &&
					!$this->mTitle->exists() ) );
	}

	/**
	 * @todo document
	 * @param $request
	 */
	function importFormData( &$request ) {
		global $wgLang, $wgUser;
		$fname = 'EditPage::importFormData';
		wfProfileIn( $fname );

		if( $request->wasPosted() ) {
			# These fields need to be checked for encoding.
			# Also remove trailing whitespace, but don't remove _initial_
			# whitespace from the text boxes. This may be significant formatting.
			$this->textbox1 = $this->safeUnicodeInput( $request, 'wpTextbox1' );
			$this->textbox2 = $this->safeUnicodeInput( $request, 'wpTextbox2' );
			$this->mMetaData = rtrim( $request->getText( 'metadata'   ) );
			# Truncate for whole multibyte characters. +5 bytes for ellipsis
			$this->summary   = $wgLang->truncate( $request->getText( 'wpSummary'  ), 250 );

			$this->edittime = $request->getVal( 'wpEdittime' );
			$this->starttime = $request->getVal( 'wpStarttime' );

			$this->scrolltop = $request->getIntOrNull( 'wpScrolltop' );

			if( is_null( $this->edittime ) ) {
				# If the form is incomplete, force to preview.
				wfDebug( "$fname: Form data appears to be incomplete\n" );
				wfDebug( "POST DATA: " . var_export( $_POST, true ) . "\n" );
				$this->preview  = true;
			} else {
				/* Fallback for live preview */
				$this->preview = $request->getCheck( 'wpPreview' ) || $request->getCheck( 'wpLivePreview' );
				$this->diff = $request->getCheck( 'wpDiff' );

				// Remember whether a save was requested, so we can indicate
				// if we forced preview due to session failure.
				$this->mTriedSave = !$this->preview;

				if ( $this->tokenOk( $request ) ) {
					# Some browsers will not report any submit button
					# if the user hits enter in the comment box.
					# The unmarked state will be assumed to be a save,
					# if the form seems otherwise complete.
					wfDebug( "$fname: Passed token check.\n" );
				} else if ( $this->diff ) {
					# Failed token check, but only requested "Show Changes".
					wfDebug( "$fname: Failed token check; Show Changes requested.\n" );
				} else {
					# Page might be a hack attempt posted from
					# an external site. Preview instead of saving.
					wfDebug( "$fname: Failed token check; forcing preview\n" );
					$this->preview = true;
				}
			}
			$this->save    = ! ( $this->preview OR $this->diff );
			if( !preg_match( '/^\d{14}$/', $this->edittime )) {
				$this->edittime = null;
			}

			if( !preg_match( '/^\d{14}$/', $this->starttime )) {
				$this->starttime = null;
			}

			$this->recreate  = $request->getCheck( 'wpRecreate' );

			$this->minoredit = $request->getCheck( 'wpMinoredit' );
			$this->watchthis = $request->getCheck( 'wpWatchthis' );

			# Don't force edit summaries when a user is editing their own user or talk page
			if( ( $this->mTitle->mNamespace == NS_USER || $this->mTitle->mNamespace == NS_USER_TALK ) && $this->mTitle->getText() == $wgUser->getName() ) {
				$this->allowBlankSummary = true;
			} else {
				$this->allowBlankSummary = $request->getBool( 'wpIgnoreBlankSummary' );
			}

			$this->autoSumm = $request->getText( 'wpAutoSummary' );	
		} else {
			# Not a posted form? Start with nothing.
			wfDebug( "$fname: Not a posted form.\n" );
			$this->textbox1  = '';
			$this->textbox2  = '';
			$this->mMetaData = '';
			$this->summary   = '';
			$this->edittime  = '';
			$this->starttime = wfTimestampNow();
			$this->preview   = false;
			$this->save      = false;
			$this->diff	 = false;
			$this->minoredit = false;
			$this->watchthis = false;
			$this->recreate  = false;
		}

		$this->oldid = $request->getInt( 'oldid' );

		# Section edit can come from either the form or a link
		$this->section = $request->getVal( 'wpSection', $request->getVal( 'section' ) );

		$this->live = $request->getCheck( 'live' );
		$this->editintro = $request->getText( 'editintro' );

		wfProfileOut( $fname );
	}

	/**
	 * Make sure the form isn't faking a user's credentials.
	 *
	 * @param $request WebRequest
	 * @return bool
	 * @private
	 */
	function tokenOk( &$request ) {
		global $wgUser;
		if( $wgUser->isAnon() ) {
			# Anonymous users may not have a session
			# open. Check for suffix anyway.
			$this->mTokenOk = ( EDIT_TOKEN_SUFFIX == $request->getVal( 'wpEditToken' ) );
		} else {
			$this->mTokenOk = $wgUser->matchEditToken( $request->getVal( 'wpEditToken' ) );
		}
		return $this->mTokenOk;
	}

	/** */
	function showIntro() {
		global $wgOut, $wgUser;
		$addstandardintro=true;
		if($this->editintro) {
			$introtitle=Title::newFromText($this->editintro);
			if(isset($introtitle) && $introtitle->userCanRead()) {
				$rev=Revision::newFromTitle($introtitle);
				if($rev) {
					$wgOut->addSecondaryWikiText($rev->getText());
					$addstandardintro=false;
				}
			}
		}
		if($addstandardintro) {
			if ( $wgUser->isLoggedIn() )
				$wgOut->addWikiText( wfMsg( 'newarticletext' ) );
			else
				$wgOut->addWikiText( wfMsg( 'newarticletextanon' ) );
		}
	}

	/**
	 * Attempt submission
	 * @return bool false if output is done, true if the rest of the form should be displayed
	 */
	function attemptSave() {
		global $wgSpamRegex, $wgFilterCallback, $wgUser, $wgOut;
		global $wgMaxArticleSize;

		$fname = 'EditPage::attemptSave';
		wfProfileIn( $fname );
		wfProfileIn( "$fname-checks" );

		if( !wfRunHooks( 'EditPage::attemptSave', array( &$this ) ) )
		{
			wfDebug( "Hook 'EditPage::attemptSave' aborted article saving" );
			return false;
		}

		# Reintegrate metadata
		if ( $this->mMetaData != '' ) $this->textbox1 .= "\n" . $this->mMetaData ;
		$this->mMetaData = '' ;

		# Check for spam
		$matches = array();
		if ( $wgSpamRegex && preg_match( $wgSpamRegex, $this->textbox1, $matches ) ) {
			$this->spamPage ( $matches[0] );
			wfProfileOut( "$fname-checks" );
			wfProfileOut( $fname );
			return false;
		}
		if ( $wgFilterCallback && $wgFilterCallback( $this->mTitle, $this->textbox1, $this->section ) ) {
			# Error messages or other handling should be performed by the filter function
			wfProfileOut( $fname );
			wfProfileOut( "$fname-checks" );
			return false;
		}
		if ( !wfRunHooks( 'EditFilter', array( $this, $this->textbox1, $this->section, &$this->hookError ) ) ) {
			# Error messages etc. could be handled within the hook...
			wfProfileOut( $fname );
			wfProfileOut( "$fname-checks" );
			return false;
		} elseif( $this->hookError != '' ) {
			# ...or the hook could be expecting us to produce an error
			wfProfileOut( "$fname-checks " );
			wfProfileOut( $fname );
			return true;
		}
		if ( $wgUser->isBlockedFrom( $this->mTitle, false ) ) {
			# Check block state against master, thus 'false'.
			$this->blockedPage();
			wfProfileOut( "$fname-checks" );
			wfProfileOut( $fname );
			return false;
		}
		$this->kblength = (int)(strlen( $this->textbox1 ) / 1024);
		if ( $this->kblength > $wgMaxArticleSize ) {
			// Error will be displayed by showEditForm()
			$this->tooBig = true;
			wfProfileOut( "$fname-checks" );
			wfProfileOut( $fname );
			return true;
		}

		if ( !$wgUser->isAllowed('edit') ) {
			if ( $wgUser->isAnon() ) {
				$this->userNotLoggedInPage();
				wfProfileOut( "$fname-checks" );
				wfProfileOut( $fname );
				return false;
			}
			else {
				$wgOut->readOnlyPage();
				wfProfileOut( "$fname-checks" );
				wfProfileOut( $fname );
				return false;
			}
		}

		if ( wfReadOnly() ) {
			$wgOut->readOnlyPage();
			wfProfileOut( "$fname-checks" );
			wfProfileOut( $fname );
			return false;
		}
		if ( $wgUser->pingLimiter() ) {
			$wgOut->rateLimited();
			wfProfileOut( "$fname-checks" );
			wfProfileOut( $fname );
			return false;
		}

		# If the article has been deleted while editing, don't save it without
		# confirmation
		if ( $this->deletedSinceEdit && !$this->recreate ) {
			wfProfileOut( "$fname-checks" );
			wfProfileOut( $fname );
			return true;
		}

		wfProfileOut( "$fname-checks" );

		# If article is new, insert it.
		$aid = $this->mTitle->getArticleID( GAID_FOR_UPDATE );
		if ( 0 == $aid ) {

			// Late check for create permission, just in case *PARANOIA*
			if ( !$this->mTitle->userCan( 'create' ) ) {
				wfDebug( "$fname: no create permission\n" );
				$this->noCreatePermission();
				wfProfileOut( $fname );
				return;
			}

			# Don't save a new article if it's blank.
			if ( ( '' == $this->textbox1 ) ) {
					$wgOut->redirect( $this->mTitle->getFullURL() );
					wfProfileOut( $fname );
					return false;
			}

			$isComment=($this->section=='new');
			$this->mArticle->insertNewArticle( $this->textbox1, $this->summary,
				$this->minoredit, $this->watchthis, false, $isComment);

			wfProfileOut( $fname );
			return false;
		}

		# Article exists. Check for edit conflict.

		$this->mArticle->clear(); # Force reload of dates, etc.
		$this->mArticle->forUpdate( true ); # Lock the article

		wfDebug("timestamp: {$this->mArticle->getTimestamp()}, edittime: {$this->edittime}\n");

		if( $this->mArticle->getTimestamp() != $this->edittime ) {
			$this->isConflict = true;
			if( $this->section == 'new' ) {
				if( $this->mArticle->getUserText() == $wgUser->getName() &&
					$this->mArticle->getComment() == $this->summary ) {
					// Probably a duplicate submission of a new comment.
					// This can happen when squid resends a request after
					// a timeout but the first one actually went through.
					wfDebug( "EditPage::editForm duplicate new section submission; trigger edit conflict!\n" );
				} else {
					// New comment; suppress conflict.
					$this->isConflict = false;
					wfDebug( "EditPage::editForm conflict suppressed; new section\n" );
				}
			}
		}
		$userid = $wgUser->getID();

		if ( $this->isConflict) {
			wfDebug( "EditPage::editForm conflict! getting section '$this->section' for time '$this->edittime' (article time '" .
				$this->mArticle->getTimestamp() . "'\n" );
			$text = $this->mArticle->replaceSection( $this->section, $this->textbox1, $this->summary, $this->edittime);
		}
		else {
			wfDebug( "EditPage::editForm getting section '$this->section'\n" );
			$text = $this->mArticle->replaceSection( $this->section, $this->textbox1, $this->summary);
		}
		if( is_null( $text ) ) {
			wfDebug( "EditPage::editForm activating conflict; section replace failed.\n" );
			$this->isConflict = true;
			$text = $this->textbox1;
		}

		# Suppress edit conflict with self, except for section edits where merging is required.
		if ( ( $this->section == '' ) && ( 0 != $userid ) && ( $this->mArticle->getUser() == $userid ) ) {
			wfDebug( "Suppressing edit conflict, same user.\n" );
			$this->isConflict = false;
		} else {
			# switch from section editing to normal editing in edit conflict
			if($this->isConflict) {
				# Attempt merge
				if( $this->mergeChangesInto( $text ) ){
					// Successful merge! Maybe we should tell the user the good news?
					$this->isConflict = false;
					wfDebug( "Suppressing edit conflict, successful merge.\n" );
				} else {
					$this->section = '';
					$this->textbox1 = $text;
					wfDebug( "Keeping edit conflict, failed merge.\n" );
				}
			}
		}

		if ( $this->isConflict ) {
			wfProfileOut( $fname );
			return true;
		}

		$oldtext = $this->mArticle->getContent();

		# Handle the user preference to force summaries here, but not for null edits
		if( $this->section != 'new' && !$this->allowBlankSummary && $wgUser->getOption( 'forceeditsummary')
			&&  0 != strcmp($oldtext, $text) && !Article::getRedirectAutosummary( $text )) {
			if( md5( $this->summary ) == $this->autoSumm ) {
				$this->missingSummary = true;
				wfProfileOut( $fname );
				return( true );
			}
		}

		#And a similar thing for new sections
		if( $this->section == 'new' && !$this->allowBlankSummary && $wgUser->getOption( 'forceeditsummary' ) ) {
			if (trim($this->summary) == '') {
				$this->missingSummary = true;
				wfProfileOut( $fname );
				return( true );
			}
		}

		# All's well
		wfProfileIn( "$fname-sectionanchor" );
		$sectionanchor = '';
		if( $this->section == 'new' ) {
			if ( $this->textbox1 == '' ) {
				$this->missingComment = true;
				return true;
			}
			if( $this->summary != '' ) {
				$sectionanchor = $this->sectionAnchor( $this->summary );
			}
		} elseif( $this->section != '' ) {
			# Try to get a section anchor from the section source, redirect to edited section if header found
			# XXX: might be better to integrate this into Article::replaceSection
			# for duplicate heading checking and maybe parsing
			$hasmatch = preg_match( "/^ *([=]{1,6})(.*?)(\\1) *\\n/i", $this->textbox1, $matches );
			# we can't deal with anchors, includes, html etc in the header for now,
			# headline would need to be parsed to improve this
			if($hasmatch and strlen($matches[2]) > 0) {
				$sectionanchor = $this->sectionAnchor( $matches[2] );
			}
		}
		wfProfileOut( "$fname-sectionanchor" );

		// Save errors may fall down to the edit form, but we've now
		// merged the section into full text. Clear the section field
		// so that later submission of conflict forms won't try to
		// replace that into a duplicated mess.
		$this->textbox1 = $text;
		$this->section = '';

		// Check for length errors again now that the section is merged in
		$this->kblength = (int)(strlen( $text ) / 1024);
		if ( $this->kblength > $wgMaxArticleSize ) {
			$this->tooBig = true;
			wfProfileOut( $fname );
			return true;
		}

		# update the article here
		if( $this->mArticle->updateArticle( $text, $this->summary, $this->minoredit,
			$this->watchthis, '', $sectionanchor ) ) {
			wfProfileOut( $fname );
			return false;
		} else {
			$this->isConflict = true;
		}
		wfProfileOut( $fname );
		return true;
	}

	/**
	 * Initialise form fields in the object
	 * Called on the first invocation, e.g. when a user clicks an edit link
	 */
	function initialiseForm() {
		$this->edittime = $this->mArticle->getTimestamp();
		$this->summary = '';
		$this->textbox1 = $this->getContent(false);
		if ($this->textbox1 === false) return false;

		if ( !$this->mArticle->exists() && $this->mArticle->mTitle->getNamespace() == NS_MEDIAWIKI )
			$this->textbox1 = wfMsgWeirdKey( $this->mArticle->mTitle->getText() );
		wfProxyCheck();
		return true;
	}

	/**
	 * Send the edit form and related headers to $wgOut
	 * @param $formCallback Optional callable that takes an OutputPage
	 *                      parameter; will be called during form output
	 *                      near the top, for captchas and the like.
	 */
	function showEditForm( $formCallback=null ) {
		global $wgOut, $wgUser, $wgLang, $wgContLang, $wgMaxArticleSize;

		$fname = 'EditPage::showEditForm';
		wfProfileIn( $fname );

		$sk = $wgUser->getSkin();

		wfRunHooks( 'EditPage::showEditForm:initial', array( &$this ) ) ;

		$wgOut->setRobotpolicy( 'noindex,nofollow' );

		# Enabled article-related sidebar, toplinks, etc.
		$wgOut->setArticleRelated( true );

		if ( $this->isConflict ) {
			$s = wfMsg( 'editconflict', $this->mTitle->getPrefixedText() );
			$wgOut->setPageTitle( $s );
			$wgOut->addWikiText( wfMsg( 'explainconflict' ) );

			$this->textbox2 = $this->textbox1;
			$this->textbox1 = $this->getContent();
			$this->edittime = $this->mArticle->getTimestamp();
		} else {

			if( $this->section != '' ) {
				if( $this->section == 'new' ) {
					$s = wfMsg('editingcomment', $this->mTitle->getPrefixedText() );
				} else {
					$s = wfMsg('editingsection', $this->mTitle->getPrefixedText() );
					$matches = array();
					if( !$this->summary && !$this->preview && !$this->diff ) {
						preg_match( "/^(=+)(.+)\\1/mi",
							$this->textbox1,
							$matches );
						if( !empty( $matches[2] ) ) {
							$this->summary = "/* ". trim($matches[2])." */ ";
						}
					}
				}
			} else {
				$s = wfMsg( 'editing', $this->mTitle->getPrefixedText() );
			}
			$wgOut->setPageTitle( $s );

			if ( $this->missingComment ) {
				$wgOut->addWikiText( wfMsg( 'missingcommenttext' ) );
			}

			if( $this->missingSummary && $this->section != 'new' ) {
				$wgOut->addWikiText( wfMsg( 'missingsummary' ) );
			}

			if( $this->missingSummary && $this->section == 'new' ) {
				$wgOut->addWikiText( wfMsg( 'missingcommentheader' ) );
			}

			if( !$this->hookError == '' ) {
				$wgOut->addWikiText( $this->hookError );
			}

			if ( !$this->checkUnicodeCompliantBrowser() ) {
				$wgOut->addWikiText( wfMsg( 'nonunicodebrowser') );
			}
			if ( isset( $this->mArticle ) && isset( $this->mArticle->mRevision ) ) {
			// Let sysop know that this will make private content public if saved
				if( $this->mArticle->mRevision->isDeleted( Revision::DELETED_TEXT ) ) {
					$wgOut->addWikiText( wfMsg( 'rev-deleted-text-view' ) );
				}
				if( !$this->mArticle->mRevision->isCurrent() ) {
					$this->mArticle->setOldSubtitle( $this->mArticle->mRevision->getId() );
					$wgOut->addWikiText( wfMsg( 'editingold' ) );
				}
			}
		}

		if( wfReadOnly() ) {
			$wgOut->addWikiText( wfMsg( 'readonlywarning' ) );
		} elseif( $wgUser->isAnon() && $this->formtype != 'preview' ) {
			$wgOut->addWikiText( wfMsg( 'anoneditwarning' ) );
		} else {
			if( $this->isCssJsSubpage && $this->formtype != 'preview' ) {
				# Check the skin exists
				if( $this->isValidCssJsSubpage ) {
					$wgOut->addWikiText( wfMsg( 'usercssjsyoucanpreview' ) );
				} else {
					$wgOut->addWikiText( wfMsg( 'userinvalidcssjstitle', $this->mTitle->getSkinFromCssJsSubpage() ) );
				}
			}
		}

		if( $this->mTitle->getNamespace() == NS_MEDIAWIKI ) {
			# Show a warning if editing an interface message
			$wgOut->addWikiText( wfMsg( 'editinginterface' ) );
		} elseif( $this->mTitle->isProtected( 'edit' ) ) {
			# Is the title semi-protected?
			if( $this->mTitle->isSemiProtected() ) {
				$notice = wfMsg( 'semiprotectedpagewarning' );
				if( wfEmptyMsg( 'semiprotectedpagewarning', $notice ) || $notice == '-' )
					$notice = '';
			} else {
			# Then it must be protected based on static groups (regular)
				$notice = wfMsg( 'protectedpagewarning' );
			}
			$wgOut->addWikiText( $notice );
		}
		if ( $this->mTitle->isCascadeProtected() ) {
			# Is this page under cascading protection from some source pages?
			list($cascadeSources, $restrictions) = $this->mTitle->getCascadeProtectionSources();
			if ( count($cascadeSources) > 0 ) {
				# Explain, and list the titles responsible
				$notice = wfMsgExt( 'cascadeprotectedwarning', array('parsemag'), count($cascadeSources) ) . "\n";
				foreach( $cascadeSources as $id => $page )
					$notice .= '* [[:' . $page->getPrefixedText() . "]]\n";
				}
			$wgOut->addWikiText( $notice );
		}

		if ( $this->kblength === false ) {
			$this->kblength = (int)(strlen( $this->textbox1 ) / 1024);
		}
		if ( $this->tooBig || $this->kblength > $wgMaxArticleSize ) {
			$wgOut->addWikiText( wfMsg( 'longpageerror', $wgLang->formatNum( $this->kblength ), $wgMaxArticleSize ) );
		} elseif( $this->kblength > 29 ) {
			$wgOut->addWikiText( wfMsg( 'longpagewarning', $wgLang->formatNum( $this->kblength ) ) );
		}

		#need to parse the preview early so that we know which templates are used,
		#otherwise users with "show preview after edit box" will get a blank list
		if ( $this->formtype == 'preview' ) {
			$previewOutput = $this->getPreviewText();
		}

		$rows = $wgUser->getIntOption( 'rows' );
		$cols = $wgUser->getIntOption( 'cols' );

		$ew = $wgUser->getOption( 'editwidth' );
		if ( $ew ) $ew = " style=\"width:100%\"";
		else $ew = '';

		$q = 'action=submit';
		#if ( "no" == $redirect ) { $q .= "&redirect=no"; }
		$action = $this->mTitle->escapeLocalURL( $q );

		$summary = wfMsg('summary');
		$subject = wfMsg('subject');

		$cancel = $sk->makeKnownLink( $this->mTitle->getPrefixedText(),
				wfMsgExt('cancel', array('parseinline')) );
		$edithelpurl = Skin::makeInternalOrExternalUrl( wfMsgForContent( 'edithelppage' ));
		$edithelp = '<a target="helpwindow" href="'.$edithelpurl.'">'.
			htmlspecialchars( wfMsg( 'edithelp' ) ).'</a> '.
			htmlspecialchars( wfMsg( 'newwindow' ) );

		global $wgRightsText;
		$copywarn = "<div id=\"editpage-copywarn\">\n" .
			wfMsg( $wgRightsText ? 'copyrightwarning' : 'copyrightwarning2',
				'[[' . wfMsgForContent( 'copyrightpage' ) . ']]',
				$wgRightsText ) . "\n</div>";

		if( $wgUser->getOption('showtoolbar') and !$this->isCssJsSubpage ) {
			# prepare toolbar for edit buttons
			$toolbar = $this->getEditToolbar();
		} else {
			$toolbar = '';
		}

		// activate checkboxes if user wants them to be always active
		if( !$this->preview && !$this->diff ) {
			# Sort out the "watch" checkbox
			if( $wgUser->getOption( 'watchdefault' ) ) {
				# Watch all edits
				$this->watchthis = true;
			} elseif( $wgUser->getOption( 'watchcreations' ) && !$this->mTitle->exists() ) {
				# Watch creations
				$this->watchthis = true;
			} elseif( $this->mTitle->userIsWatching() ) {
				# Already watched
				$this->watchthis = true;
			}

			if( $wgUser->getOption( 'minordefault' ) ) $this->minoredit = true;
		}

		$wgOut->addHTML( $this->editFormPageTop );

		if ( $wgUser->getOption( 'previewontop' ) ) {

			if ( 'preview' == $this->formtype ) {
				$this->showPreview( $previewOutput );
			} else {
				$wgOut->addHTML( '<div id="wikiPreview"></div>' );
			}

			if ( 'diff' == $this->formtype ) {
				$wgOut->addHTML( $this->getDiff() );
			}
		}


		$wgOut->addHTML( $this->editFormTextTop );

		# if this is a comment, show a subject line at the top, which is also the edit summary.
		# Otherwise, show a summary field at the bottom
		$summarytext = htmlspecialchars( $wgContLang->recodeForEdit( $this->summary ) ); # FIXME
		if( $this->section == 'new' ) {
			$commentsubject="<span id='wpSummaryLabel'><label for='wpSummary'>{$subject}:</label></span>\n<div class='editOptions'>\n<input tabindex='1' type='text' value=\"$summarytext\" name='wpSummary' id='wpSummary' maxlength='200' size='60' /><br />";
			$editsummary = '';
			$subjectpreview = $summarytext && $this->preview ? "<div class=\"mw-summary-preview\">".wfMsg('subject-preview').':'.$sk->commentBlock( $this->summary, $this->mTitle )."</div>\n" : '';
			$summarypreview = '';
		} else {
			$commentsubject = '';
			$editsummary="<span id='wpSummaryLabel'><label for='wpSummary'>{$summary}:</label></span>\n<div class='editOptions'>\n<input tabindex='2' type='text' value=\"$summarytext\" name='wpSummary' id='wpSummary' maxlength='200' size='60' /><br />";
			$summarypreview = $summarytext && $this->preview ? "<div class=\"mw-summary-preview\">".wfMsg('summary-preview').':'.$sk->commentBlock( $this->summary, $this->mTitle )."</div>\n" : '';
			$subjectpreview = '';
		}

		# Set focus to the edit box on load, except on preview or diff, where it would interfere with the display
		if( !$this->preview && !$this->diff ) {
			$wgOut->setOnloadHandler( 'document.editform.wpTextbox1.focus()' );
		}
		$templates = ($this->preview || $this->section) ? $this->mPreviewTemplates : $this->mArticle->getUsedTemplates();
		$formattedtemplates = $sk->formatTemplates( $templates, $this->preview, $this->section != '');

		global $wgUseMetadataEdit ;
		if ( $wgUseMetadataEdit ) {
			$metadata = $this->mMetaData ;
			$metadata = htmlspecialchars( $wgContLang->recodeForEdit( $metadata ) ) ;
			$top = wfMsgWikiHtml( 'metadata_help' );
			$metadata = $top . "<textarea name='metadata' rows='3' cols='{$cols}'{$ew}>{$metadata}</textarea>" ;
		}
		else $metadata = "" ;

		$hidden = '';
		$recreate = '';
		if ($this->deletedSinceEdit) {
			if ( 'save' != $this->formtype ) {
				$wgOut->addWikiText( wfMsg('deletedwhileediting'));
			} else {
				// Hide the toolbar and edit area, use can click preview to get it back
				// Add an confirmation checkbox and explanation.
				$toolbar = '';
				$hidden = 'type="hidden" style="display:none;"';
				$recreate = $wgOut->parse( wfMsg( 'confirmrecreate',  $this->lastDelete->user_name , $this->lastDelete->log_comment ));
				$recreate .=
					"<br /><input tabindex='1' type='checkbox' value='1' name='wpRecreate' id='wpRecreate' />".
					"<label for='wpRecreate' title='".wfMsg('tooltip-recreate')."'>". wfMsg('recreate')."</label>";
			}
		}

		$tabindex = 2;

		$checkboxes = self::getCheckboxes( $tabindex, $sk,
			array( 'minor' => $this->minoredit, 'watch' => $this->watchthis ) );

		$checkboxhtml = implode( $checkboxes, "\n" );

		$buttons = $this->getEditButtons( $tabindex );
		$buttonshtml = implode( $buttons, "\n" );

		$safemodehtml = $this->checkUnicodeCompliantBrowser()
			? '' : Xml::hidden( 'safemode', '1' );

		$wgOut->addHTML( <<<END
{$toolbar}
<form id="editform" name="editform" method="post" action="$action" enctype="multipart/form-data">
END
);

		if( is_callable( $formCallback ) ) {
			call_user_func_array( $formCallback, array( &$wgOut ) );
		}

		wfRunHooks( 'EditPage::showEditForm:fields', array( &$this, &$wgOut ) );

		// Put these up at the top to ensure they aren't lost on early form submission
		$wgOut->addHTML( "
<input type='hidden' value=\"" . htmlspecialchars( $this->section ) . "\" name=\"wpSection\" />
<input type='hidden' value=\"{$this->starttime}\" name=\"wpStarttime\" />\n
<input type='hidden' value=\"{$this->edittime}\" name=\"wpEdittime\" />\n
<input type='hidden' value=\"{$this->scrolltop}\" name=\"wpScrolltop\" id=\"wpScrolltop\" />\n" );

		$wgOut->addHTML( <<<END
$recreate
{$commentsubject}
{$subjectpreview}
<textarea tabindex='1' accesskey="," name="wpTextbox1" id="wpTextbox1" rows='{$rows}'
cols='{$cols}'{$ew} $hidden>
END
. htmlspecialchars( $this->safeUnicodeOutput( $this->textbox1 ) ) .
"
</textarea>
		" );

		$wgOut->addWikiText( $copywarn );
		$wgOut->addHTML( $this->editFormTextAfterWarn );
		$wgOut->addHTML( "
{$metadata}
{$editsummary}
{$summarypreview}
{$checkboxhtml}
{$safemodehtml}
");

		$wgOut->addHTML(
"<div class='editButtons'>
{$buttonshtml}
	<span class='editHelp'>{$cancel} | {$edithelp}</span>
</div><!-- editButtons -->
</div><!-- editOptions -->");

		$wgOut->addHtml( '<div class="mw-editTools">' );
		$wgOut->addWikiText( wfMsgForContent( 'edittools' ) );
		$wgOut->addHtml( '</div>' );

		$wgOut->addHTML( $this->editFormTextAfterTools );

		$wgOut->addHTML( "
<div class='templatesUsed'>
{$formattedtemplates}
</div>
" );

		/**
		 * To make it harder for someone to slip a user a page
		 * which submits an edit form to the wiki without their
		 * knowledge, a random token is associated with the login
		 * session. If it's not passed back with the submission,
		 * we won't save the page, or render user JavaScript and
		 * CSS previews.
		 *
		 * For anon editors, who may not have a session, we just
		 * include the constant suffix to prevent editing from
		 * broken text-mangling proxies.
		 */
		if ( $wgUser->isLoggedIn() )
			$token = htmlspecialchars( $wgUser->editToken() );
		else
			$token = EDIT_TOKEN_SUFFIX;
		$wgOut->addHTML( "\n<input type='hidden' value=\"$token\" name=\"wpEditToken\" />\n" );


		# If a blank edit summary was previously provided, and the appropriate
		# user preference is active, pass a hidden tag here. This will stop the
		# user being bounced back more than once in the event that a summary
		# is not required.
		if( $this->missingSummary ) {
			$wgOut->addHTML( "<input type=\"hidden\" name=\"wpIgnoreBlankSummary\" value=\"1\" />\n" );
		}

		# For a bit more sophisticated detection of blank summaries, hash the
		# automatic one and pass that in a hidden field.
		$autosumm = $this->autoSumm ? $this->autoSumm : md5( $this->summary );
		$wgOut->addHtml( wfHidden( 'wpAutoSummary', $autosumm ) );

		if ( $this->isConflict ) {
			$wgOut->addWikiText( '==' . wfMsg( "yourdiff" ) . '==' );

			$de = new DifferenceEngine( $this->mTitle );
			$de->setText( $this->textbox2, $this->textbox1 );
			$de->showDiff( wfMsg( "yourtext" ), wfMsg( "storedversion" ) );

			$wgOut->addWikiText( '==' . wfMsg( "yourtext" ) . '==' );
			$wgOut->addHTML( "<textarea tabindex=6 id='wpTextbox2' name=\"wpTextbox2\" rows='{$rows}' cols='{$cols}' wrap='virtual'>"
				. htmlspecialchars( $this->safeUnicodeOutput( $this->textbox2 ) ) . "\n</textarea>" );
		}
		$wgOut->addHTML( $this->editFormTextBottom );
		$wgOut->addHTML( "</form>\n" );
		if ( !$wgUser->getOption( 'previewontop' ) ) {

			if ( $this->formtype == 'preview') {
				$this->showPreview( $previewOutput );
			} else {
				$wgOut->addHTML( '<div id="wikiPreview"></div>' );
			}

			if ( $this->formtype == 'diff') {
				$wgOut->addHTML( $this->getDiff() );
			}

		}

		wfProfileOut( $fname );
	}

	/**
	 * Append preview output to $wgOut.
	 * Includes category rendering if this is a category page.
	 *
	 * @param string $text The HTML to be output for the preview.
	 */
	private function showPreview( $text ) {
		global $wgOut;

		$wgOut->addHTML( '<div id="wikiPreview">' );
		if($this->mTitle->getNamespace() == NS_CATEGORY) {
			$this->mArticle->openShowCategory();
		}
		$wgOut->addHTML( $text );
		if($this->mTitle->getNamespace() == NS_CATEGORY) {
			$this->mArticle->closeShowCategory();
		}
		$wgOut->addHTML( '</div>' );
	}

