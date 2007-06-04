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
