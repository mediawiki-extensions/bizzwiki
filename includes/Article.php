<?php
/*
	Origin:	MW 1.10
	-------
	
	BizzWiki: $Id$

*/

/**
 * File for articles
 */

/**
 * Class representing a MediaWiki article and history.
 *
 * See design.txt for an overview.
 * Note: edit user interface and cache support functions have been
 * moved to separate EditPage and HTMLFileCache classes.
 *
 */
class Article {
	/**@{{
	 * @private
	 */
	var $mComment;			//!<
	var $mContent;			//!<
	var $mContentLoaded;	//!<
	var $mCounter;			//!<
	var $mForUpdate;		//!<
	var $mGoodAdjustment;	//!<
	var $mLatest;			//!<
	var $mMinorEdit;		//!<
	var $mOldId;			//!<
	var $mRedirectedFrom;	//!<
	var $mRedirectUrl;		//!<
	var $mRevIdFetched;		//!<
	var $mRevision;			//!<
	var $mTimestamp;		//!<
	var $mTitle;			//!<
	var $mTotalAdjustment;	//!<
	var $mTouched;			//!<
	var $mUser;				//!<
	var $mUserText;			//!<
	/**@}}*/

	/**
	 * Constructor and clear the article
	 * @param $title Reference to a Title object.
	 * @param $oldId Integer revision ID, null to fetch from request, zero for current
	 */
	function __construct( &$title, $oldId = null ) {
		$this->mTitle =& $title;
		$this->mOldId = $oldId;
		$this->clear();
	}

	/**
	 * Tell the page view functions that this view was redirected
	 * from another page on the wiki.
	 * @param $from Title object.
	 */
	function setRedirectedFrom( $from ) {
		$this->mRedirectedFrom = $from;
	}

	/**
	 * @return mixed false, Title of in-wiki target, or string with URL
	 */
	function followRedirect() {
		$text = $this->getContent();
		$rt = Title::newFromRedirect( $text );

		# process if title object is valid and not special:userlogout
		if( $rt ) {
			if( $rt->getInterwiki() != '' ) {
				if( $rt->isLocal() ) {
					// Offsite wikis need an HTTP redirect.
					//
					// This can be hard to reverse and may produce loops,
					// so they may be disabled in the site configuration.

					$source = $this->mTitle->getFullURL( 'redirect=no' );
					return $rt->getFullURL( 'rdfrom=' . urlencode( $source ) );
				}
			} else {
				if( $rt->getNamespace() == NS_SPECIAL ) {
					// Gotta handle redirects to special pages differently:
					// Fill the HTTP response "Location" header and ignore
					// the rest of the page we're on.
					//
					// This can be hard to reverse, so they may be disabled.

					if( $rt->isSpecial( 'Userlogout' ) ) {
						// rolleyes
					} else {
						return $rt->getFullURL();
					}
				}
				return $rt;
			}
		}

		// No or invalid redirect
		return false;
	}

	/**
	 * get the title object of the article
	 */
	function getTitle() {
		return $this->mTitle;
	}

	/**
	  * Clear the object
	  * @private
	  */
	function clear() {
		$this->mDataLoaded    = false;
		$this->mContentLoaded = false;

		$this->mCurID = $this->mUser = $this->mCounter = -1; # Not loaded
		$this->mRedirectedFrom = null; # Title object if set
		$this->mUserText =
		$this->mTimestamp = $this->mComment = '';
		$this->mGoodAdjustment = $this->mTotalAdjustment = 0;
		$this->mTouched = '19700101000000';
		$this->mForUpdate = false;
		$this->mIsRedirect = false;
		$this->mRevIdFetched = 0;
		$this->mRedirectUrl = false;
		$this->mLatest = false;
	}

	/**
	 * Note that getContent/loadContent do not follow redirects anymore.
	 * If you need to fetch redirectable content easily, try
	 * the shortcut in Article::followContent()
	 * FIXME
	 * @todo There are still side-effects in this!
	 *        In general, you should use the Revision class, not Article,
	 *        to fetch text for purposes other than page views.
	 *
	 * @return Return the text of this revision
	*/
	function getContent() {
		global $wgUser, $wgOut;

		wfProfileIn( __METHOD__ );

		if ( 0 == $this->getID() ) {
			wfProfileOut( __METHOD__ );
			$wgOut->setRobotpolicy( 'noindex,nofollow' );

			if ( $this->mTitle->getNamespace() == NS_MEDIAWIKI ) {
				$ret = wfMsgWeirdKey ( $this->mTitle->getText() ) ;
			} else {
				$ret = wfMsg( $wgUser->isLoggedIn() ? 'noarticletext' : 'noarticletextanon' );
			}

			return "<div class='noarticletext'>$ret</div>";
		} else {
			$this->loadContent();
			wfProfileOut( __METHOD__ );
			return $this->mContent;
		}
	}

	/**
	 * This function returns the text of a section, specified by a number ($section).
	 * A section is text under a heading like == Heading == or \<h1\>Heading\</h1\>, or
	 * the first section before any such heading (section 0).
	 *
	 * If a section contains subsections, these are also returned.
	 *
	 * @param $text String: text to look in
	 * @param $section Integer: section number
	 * @return string text of the requested section
	 * @deprecated
	 */
	function getSection($text,$section) {
		global $wgParser;
		return $wgParser->getSection( $text, $section );
	}

	/**
	 * @return int The oldid of the article that is to be shown, 0 for the
	 *             current revision
	 */
	function getOldID() {
		if ( is_null( $this->mOldId ) ) {
			$this->mOldId = $this->getOldIDFromRequest();
		}
		return $this->mOldId;
	}

	/**
	 * Sets $this->mRedirectUrl to a correct URL if the query parameters are incorrect
	 *
	 * @return int The old id for the request
	 */
	function getOldIDFromRequest() {
		global $wgRequest;
		$this->mRedirectUrl = false;
		$oldid = $wgRequest->getVal( 'oldid' );
		if ( isset( $oldid ) ) {
			$oldid = intval( $oldid );
			if ( $wgRequest->getVal( 'direction' ) == 'next' ) {
				$nextid = $this->mTitle->getNextRevisionID( $oldid );
				if ( $nextid  ) {
					$oldid = $nextid;
				} else {
					$this->mRedirectUrl = $this->mTitle->getFullURL( 'redirect=no' );
				}
			} elseif ( $wgRequest->getVal( 'direction' ) == 'prev' ) {
				$previd = $this->mTitle->getPreviousRevisionID( $oldid );
				if ( $previd ) {
					$oldid = $previd;
				} else {
					# TODO
				}
			}
			# unused:
			# $lastid = $oldid;
		}

		if ( !$oldid ) {
			$oldid = 0;
		}
		return $oldid;
	}

	/**
	 * Load the revision (including text) into this object
	 */
	function loadContent() {
		if ( $this->mContentLoaded ) return;

		# Query variables :P
		$oldid = $this->getOldID();

		# Pre-fill content with error message so that if something
		# fails we'll have something telling us what we intended.
		$this->mOldId = $oldid;
		$this->fetchContent( $oldid );
	}


	/**
	 * Fetch a page record with the given conditions
	 * @param Database $dbr
	 * @param array    $conditions
	 * @private
	 */
	function pageData( $dbr, $conditions ) {
		$fields = array(
				'page_id',
				'page_namespace',
				'page_title',
				'page_restrictions',
				'page_counter',
				'page_is_redirect',
				'page_is_new',
				'page_random',
				'page_touched',
				'page_latest',
				'page_len' ) ;
		wfRunHooks( 'ArticlePageDataBefore', array( &$this , &$fields ) )	;
		$row = $dbr->selectRow( 'page',
			$fields,
			$conditions,
			'Article::pageData' );
		wfRunHooks( 'ArticlePageDataAfter', array( &$this , &$row ) )	;
		return $row ;
	}

	/**
	 * @param Database $dbr
	 * @param Title $title
	 */
	function pageDataFromTitle( $dbr, $title ) {
		return $this->pageData( $dbr, array(
			'page_namespace' => $title->getNamespace(),
			'page_title'     => $title->getDBkey() ) );
	}

	/**
	 * @param Database $dbr
	 * @param int $id
	 */
	function pageDataFromId( $dbr, $id ) {
		return $this->pageData( $dbr, array( 'page_id' => $id ) );
	}

	/**
	 * Set the general counter, title etc data loaded from
	 * some source.
	 *
	 * @param object $data
	 * @private
	 */
	function loadPageData( $data = 'fromdb' ) {
		if ( $data === 'fromdb' ) {
			$dbr = $this->getDB();
			$data = $this->pageDataFromId( $dbr, $this->getId() );
		}

		$lc =& LinkCache::singleton();
		if ( $data ) {
			$lc->addGoodLinkObj( $data->page_id, $this->mTitle );

			$this->mTitle->mArticleID = $data->page_id;

			# Old-fashioned restrictions.
			$this->mTitle->loadRestrictions( $data->page_restrictions );

			$this->mCounter     = $data->page_counter;
			$this->mTouched     = wfTimestamp( TS_MW, $data->page_touched );
			$this->mIsRedirect  = $data->page_is_redirect;
			$this->mLatest      = $data->page_latest;
		} else {
			if ( is_object( $this->mTitle ) ) {
				$lc->addBadLinkObj( $this->mTitle );
			}
			$this->mTitle->mArticleID = 0;
		}

		$this->mDataLoaded  = true;
	}

	/**
	 * Get text of an article from database
	 * Does *NOT* follow redirects.
	 * @param int $oldid 0 for whatever the latest revision is
	 * @return string
	 */
	function fetchContent( $oldid = 0 ) {
		if ( $this->mContentLoaded ) {
			return $this->mContent;
		}

		$dbr = $this->getDB();

		# Pre-fill content with error message so that if something
		# fails we'll have something telling us what we intended.
		$t = $this->mTitle->getPrefixedText();
		if( $oldid ) {
			$t .= ',oldid='.$oldid;
		}
		$this->mContent = wfMsg( 'missingarticle', $t ) ;

		if( $oldid ) {
			$revision = Revision::newFromId( $oldid );
			if( is_null( $revision ) ) {
				wfDebug( __METHOD__." failed to retrieve specified revision, id $oldid\n" );
				return false;
			}
			$data = $this->pageDataFromId( $dbr, $revision->getPage() );
			if( !$data ) {
				wfDebug( __METHOD__." failed to get page data linked to revision id $oldid\n" );
				return false;
			}
			$this->mTitle = Title::makeTitle( $data->page_namespace, $data->page_title );
			$this->loadPageData( $data );
		} else {
			if( !$this->mDataLoaded ) {
				$data = $this->pageDataFromTitle( $dbr, $this->mTitle );
				if( !$data ) {
					wfDebug( __METHOD__." failed to find page data for title " . $this->mTitle->getPrefixedText() . "\n" );
					return false;
				}
				$this->loadPageData( $data );
			}
			$revision = Revision::newFromId( $this->mLatest );
			if( is_null( $revision ) ) {
				wfDebug( __METHOD__." failed to retrieve current page, rev_id {$data->page_latest}\n" );
				return false;
			}
		}

		// FIXME: Horrible, horrible! This content-loading interface just plain sucks.
		// We should instead work with the Revision object when we need it...
		$this->mContent = $revision->userCan( Revision::DELETED_TEXT ) ? $revision->getRawText() : "";
		//$this->mContent   = $revision->getText();

		$this->mUser      = $revision->getUser();
		$this->mUserText  = $revision->getUserText();
		$this->mComment   = $revision->getComment();
		$this->mTimestamp = wfTimestamp( TS_MW, $revision->getTimestamp() );

		$this->mRevIdFetched = $revision->getID();
		$this->mContentLoaded = true;
		$this->mRevision =& $revision;

		wfRunHooks( 'ArticleAfterFetchContent', array( &$this, &$this->mContent ) ) ;

		return $this->mContent;
	}

	/**
	 * Read/write accessor to select FOR UPDATE
	 *
	 * @param $x Mixed: FIXME
	 */
	function forUpdate( $x = NULL ) {
		return wfSetVar( $this->mForUpdate, $x );
	}

	/**
	 * Get the database which should be used for reads
	 *
	 * @return Database
	 */
	function getDB() {
		return wfGetDB( DB_MASTER );
	}

	/**
	 * Get options for all SELECT statements
	 *
	 * @param $options Array: an optional options array which'll be appended to
	 *                       the default
	 * @return Array: options
	 */
	function getSelectOptions( $options = '' ) {
		if ( $this->mForUpdate ) {
			if ( is_array( $options ) ) {
				$options[] = 'FOR UPDATE';
			} else {
				$options = 'FOR UPDATE';
			}
		}
		return $options;
	}

	/**
	 * @return int Page ID
	 */
	function getID() {
		if( $this->mTitle ) {
			return $this->mTitle->getArticleID();
		} else {
			return 0;
		}
	}

	/**
	 * @return bool Whether or not the page exists in the database
	 */
	function exists() {
		return $this->getId() != 0;
	}

	/**
	 * @return int The view count for the page
	 */
	function getCount() {
		if ( -1 == $this->mCounter ) {
			$id = $this->getID();
			if ( $id == 0 ) {
				$this->mCounter = 0;
			} else {
				$dbr = wfGetDB( DB_SLAVE );
				$this->mCounter = $dbr->selectField( 'page', 'page_counter', array( 'page_id' => $id ),
					'Article::getCount', $this->getSelectOptions() );
			}
		}
		return $this->mCounter;
	}

	/**
	 * Determine whether a page  would be suitable for being counted as an
	 * article in the site_stats table based on the title & its content
	 *
	 * @param $text String: text to analyze
	 * @return bool
	 */
	function isCountable( $text ) {
		global $wgUseCommaCount;

		$token = $wgUseCommaCount ? ',' : '[[';
		return
			$this->mTitle->isContentPage()
			&& !$this->isRedirect( $text )
			&& in_string( $token, $text );
	}

	/**
	 * Tests if the article text represents a redirect
	 *
	 * @param $text String: FIXME
	 * @return bool
	 */
	function isRedirect( $text = false ) {
		if ( $text === false ) {
			$this->loadContent();
			$titleObj = Title::newFromRedirect( $this->fetchContent() );
		} else {
			$titleObj = Title::newFromRedirect( $text );
		}
		return $titleObj !== NULL;
	}

	/**
	 * Returns true if the currently-referenced revision is the current edit
	 * to this page (and it exists).
	 * @return bool
	 */
	function isCurrent() {
		return $this->exists() &&
			isset( $this->mRevision ) &&
			$this->mRevision->isCurrent();
	}

	/**
	 * Loads everything except the text
	 * This isn't necessary for all uses, so it's only done if needed.
	 * @private
	 */
	function loadLastEdit() {
		if ( -1 != $this->mUser )
			return;

		# New or non-existent articles have no user information
		$id = $this->getID();
		if ( 0 == $id ) return;

		$this->mLastRevision = Revision::loadFromPageId( $this->getDB(), $id );
		if( !is_null( $this->mLastRevision ) ) {
			$this->mUser      = $this->mLastRevision->getUser();
			$this->mUserText  = $this->mLastRevision->getUserText();
			$this->mTimestamp = $this->mLastRevision->getTimestamp();
			$this->mComment   = $this->mLastRevision->getComment();
			$this->mMinorEdit = $this->mLastRevision->isMinor();
			$this->mRevIdFetched = $this->mLastRevision->getID();
		}
	}

	function getTimestamp() {
		// Check if the field has been filled by ParserCache::get()
		if ( !$this->mTimestamp ) {
			$this->loadLastEdit();
		}
		return wfTimestamp(TS_MW, $this->mTimestamp);
	}

	function getUser() {
		$this->loadLastEdit();
		return $this->mUser;
	}

	function getUserText() {
		$this->loadLastEdit();
		return $this->mUserText;
	}

	function getComment() {
		$this->loadLastEdit();
		return $this->mComment;
	}

	function getMinorEdit() {
		$this->loadLastEdit();
		return $this->mMinorEdit;
	}

	function getRevIdFetched() {
		$this->loadLastEdit();
		return $this->mRevIdFetched;
	}

	/**
	 * @todo Document, fixme $offset never used.
	 * @param $limit Integer: default 0.
	 * @param $offset Integer: default 0.
	 */
	function getContributors($limit = 0, $offset = 0) {
		# XXX: this is expensive; cache this info somewhere.

		$contribs = array();
		$dbr = wfGetDB( DB_SLAVE );
		$revTable = $dbr->tableName( 'revision' );
		$userTable = $dbr->tableName( 'user' );
		$user = $this->getUser();
		$pageId = $this->getId();

		$sql = "SELECT rev_user, rev_user_text, user_real_name, MAX(rev_timestamp) as timestamp
			FROM $revTable LEFT JOIN $userTable ON rev_user = user_id
			WHERE rev_page = $pageId
			AND rev_user != $user
			GROUP BY rev_user, rev_user_text, user_real_name
			ORDER BY timestamp DESC";

		if ($limit > 0) { $sql .= ' LIMIT '.$limit; }
		$sql .= ' '. $this->getSelectOptions();

		$res = $dbr->query($sql, __METHOD__);

		while ( $line = $dbr->fetchObject( $res ) ) {
			$contribs[] = array($line->rev_user, $line->rev_user_text, $line->user_real_name);
		}

		$dbr->freeResult($res);
		return $contribs;
	}

	/**
	 * This is the default action of the script: just view the page of
	 * the given title.
	*/
	function view()	{
		global $wgUser, $wgOut, $wgRequest, $wgContLang;
		global $wgEnableParserCache, $wgStylePath, $wgUseRCPatrol, $wgParser;
		global $wgUseTrackbacks, $wgNamespaceRobotPolicies;
		$sk = $wgUser->getSkin();

		wfProfileIn( __METHOD__ );

		$parserCache =& ParserCache::singleton();
		$ns = $this->mTitle->getNamespace(); # shortcut

		# Get variables from query string
		$oldid = $this->getOldID();

		# getOldID may want us to redirect somewhere else
		if ( $this->mRedirectUrl ) {
			$wgOut->redirect( $this->mRedirectUrl );
			wfProfileOut( __METHOD__ );
			return;
		}

		$diff = $wgRequest->getVal( 'diff' );
		$rcid = $wgRequest->getVal( 'rcid' );
		$rdfrom = $wgRequest->getVal( 'rdfrom' );
		$diffOnly = $wgRequest->getBool( 'diffonly', $wgUser->getOption( 'diffonly' ) );

		$wgOut->setArticleFlag( true );

		# Discourage indexing of printable versions, but encourage following
		if( $wgOut->isPrintable() ) {
			$policy = 'noindex,follow';
		} elseif( isset( $wgNamespaceRobotPolicies[$ns] ) ) {
			# Honour customised robot policies for this namespace
			$policy = $wgNamespaceRobotPolicies[$ns];
		} else {
			# Default to encourage indexing and following links
			$policy = 'index,follow';
		}
		$wgOut->setRobotPolicy( $policy );

		# If we got diff and oldid in the query, we want to see a
		# diff page instead of the article.

		if ( !is_null( $diff ) ) {
			$wgOut->setPageTitle( $this->mTitle->getPrefixedText() );

			$de = new DifferenceEngine( $this->mTitle, $oldid, $diff, $rcid );
			// DifferenceEngine directly fetched the revision:
			$this->mRevIdFetched = $de->mNewid;
			$de->showDiffPage( $diffOnly );

			// Needed to get the page's current revision
			$this->loadPageData();
			if( $diff == 0 || $diff == $this->mLatest ) {
				# Run view updates for current revision only
				$this->viewUpdates();
			}
			wfProfileOut( __METHOD__ );
			return;
		}

		if ( empty( $oldid ) && $this->checkTouched() ) {
			$wgOut->setETag($parserCache->getETag($this, $wgUser));

			if( $wgOut->checkLastModified( $this->mTouched ) ){
				wfProfileOut( __METHOD__ );
				return;
			} else if ( $this->tryFileCache() ) {
				# tell wgOut that output is taken care of
				$wgOut->disable();
				$this->viewUpdates();
				wfProfileOut( __METHOD__ );
				return;
			}
		}

		# Should the parser cache be used?
		$pcache = $wgEnableParserCache &&
			intval( $wgUser->getOption( 'stubthreshold' ) ) == 0 &&
			$this->exists() &&
			empty( $oldid );
		wfDebug( 'Article::view using parser cache: ' . ($pcache ? 'yes' : 'no' ) . "\n" );
		if ( $wgUser->getOption( 'stubthreshold' ) ) {
			wfIncrStats( 'pcache_miss_stub' );
		}

		$wasRedirected = false;
		if ( isset( $this->mRedirectedFrom ) ) {
			// This is an internally redirected page view.
			// We'll need a backlink to the source page for navigation.
			if ( wfRunHooks( 'ArticleViewRedirect', array( &$this ) ) ) {
				$sk = $wgUser->getSkin();
				$redir = $sk->makeKnownLinkObj( $this->mRedirectedFrom, '', 'redirect=no' );
				$s = wfMsg( 'redirectedfrom', $redir );
				$wgOut->setSubtitle( $s );

				// Set the fragment if one was specified in the redirect
				if ( strval( $this->mTitle->getFragment() ) != '' ) {
					$fragment = Xml::escapeJsString( $this->mTitle->getFragmentForURL() );
					$wgOut->addInlineScript( "redirectToFragment(\"$fragment\");" );
				}
				$wasRedirected = true;
			}
		} elseif ( !empty( $rdfrom ) ) {
			// This is an externally redirected view, from some other wiki.
			// If it was reported from a trusted site, supply a backlink.
			global $wgRedirectSources;
			if( $wgRedirectSources && preg_match( $wgRedirectSources, $rdfrom ) ) {
				$sk = $wgUser->getSkin();
				$redir = $sk->makeExternalLink( $rdfrom, $rdfrom );
				$s = wfMsg( 'redirectedfrom', $redir );
				$wgOut->setSubtitle( $s );
				$wasRedirected = true;
			}
		}

		$outputDone = false;
		wfRunHooks( 'ArticleViewHeader', array( &$this ) );
		if ( $pcache ) {
			if ( $wgOut->tryParserCache( $this, $wgUser ) ) {
				$outputDone = true;
			}
		}
		if ( !$outputDone ) {
			$text = $this->getContent();
			if ( $text === false ) {
				# Failed to load, replace text with error message
				$t = $this->mTitle->getPrefixedText();
				if( $oldid ) {
					$t .= ',oldid='.$oldid;
					$text = wfMsg( 'missingarticle', $t );
				} else {
					$text = wfMsg( 'noarticletext', $t );
				}
			}

			# Another whitelist check in case oldid is altering the title
			if ( !$this->mTitle->userCanRead() ) {
				$wgOut->loginToUse();
				$wgOut->output();
				exit;
			}

			# We're looking at an old revision

			if ( !empty( $oldid ) ) {
				$wgOut->setRobotpolicy( 'noindex,nofollow' );
				if( is_null( $this->mRevision ) ) {
					// FIXME: This would be a nice place to load the 'no such page' text.
				} else {
					$this->setOldSubtitle( isset($this->mOldId) ? $this->mOldId : $oldid );
					if( $this->mRevision->isDeleted( Revision::DELETED_TEXT ) ) {
						if( !$this->mRevision->userCan( Revision::DELETED_TEXT ) ) {
							$wgOut->addWikiText( wfMsg( 'rev-deleted-text-permission' ) );
							$wgOut->setPageTitle( $this->mTitle->getPrefixedText() );
							return;
						} else {
							$wgOut->addWikiText( wfMsg( 'rev-deleted-text-view' ) );
							// and we are allowed to see...
						}
					}
				}

			}
		}
		if( !$outputDone ) {
			$wgOut->setRevisionId( $this->getRevIdFetched() );
			# wrap user css and user js in pre and don't parse
			# XXX: use $this->mTitle->usCssJsSubpage() when php is fixed/ a workaround is found
			if (
				$ns == NS_USER &&
				preg_match('/\\/[\\w]+\\.(?:css|js)$/', $this->mTitle->getDBkey())
			) {
				$wgOut->addWikiText( wfMsg('clearyourcache'));
				$wgOut->addHTML( '<pre>'.htmlspecialchars($this->mContent)."\n</pre>" );
			} else if ( $rt = Title::newFromRedirect( $text ) ) {
				# Display redirect
				$imageDir = $wgContLang->isRTL() ? 'rtl' : 'ltr';
				$imageUrl = $wgStylePath.'/common/images/redirect' . $imageDir . '.png';
				# Don't overwrite the subtitle if this was an old revision
				if( !$wasRedirected && $this->isCurrent() ) {
					$wgOut->setSubtitle( wfMsgHtml( 'redirectpagesub' ) );
				}
				$link = $sk->makeLinkObj( $rt, $rt->getFullText() );

				$wgOut->addHTML( '<img src="'.$imageUrl.'" alt="#REDIRECT " />' .
				  '<span class="redirectText">'.$link.'</span>' );

				$parseout = $wgParser->parse($text, $this->mTitle, ParserOptions::newFromUser($wgUser));
				$wgOut->addParserOutputNoText( $parseout );
			} else if ( $pcache ) {
				# Display content and save to parser cache
				$this->outputWikiText( $text );
			} else {
				# Display content, don't attempt to save to parser cache
				# Don't show section-edit links on old revisions... this way lies madness.
				if( !$this->isCurrent() ) {
					$oldEditSectionSetting = $wgOut->parserOptions()->setEditSection( false );
				}
				# Display content and don't save to parser cache
				# With timing hack -- TS 2006-07-26
				$time = -wfTime();
				$this->outputWikiText( $text, false );
				$time += wfTime();

				# Timing hack
				if ( $time > 3 ) {
					wfDebugLog( 'slow-parse', sprintf( "%-5.2f %s", $time,
						$this->mTitle->getPrefixedDBkey()));
				}

				if( !$this->isCurrent() ) {
					$wgOut->parserOptions()->setEditSection( $oldEditSectionSetting );
				}

			}
		}
		/* title may have been set from the cache */
		$t = $wgOut->getPageTitle();
		if( empty( $t ) ) {
			$wgOut->setPageTitle( $this->mTitle->getPrefixedText() );
		}

		# check if we're displaying a [[User talk:x.x.x.x]] anonymous talk page
		if( $ns == NS_USER_TALK &&
			User::isIP( $this->mTitle->getText() ) ) {
			$wgOut->addWikiText( wfMsg('anontalkpagetext') );
		}

		# If we have been passed an &rcid= parameter, we want to give the user a
		# chance to mark this new article as patrolled.
		if ( $wgUseRCPatrol && !is_null( $rcid ) && $rcid != 0 && $wgUser->isAllowed( 'patrol' ) ) {
			$wgOut->addHTML(
				"<div class='patrollink'>" .
					wfMsgHtml( 'markaspatrolledlink',
					$sk->makeKnownLinkObj( $this->mTitle, wfMsgHtml('markaspatrolledtext'),
						"action=markpatrolled&rcid=$rcid" )
			 		) .
				'</div>'
			 );
		}

		# Trackbacks
		if ($wgUseTrackbacks)
			$this->addTrackbacks();

		$this->viewUpdates();
		wfProfileOut( __METHOD__ );
	}

	function addTrackbacks() {
		global $wgOut, $wgUser;

		$dbr = wfGetDB(DB_SLAVE);
		$tbs = $dbr->select(
				/* FROM   */ 'trackbacks',
				/* SELECT */ array('tb_id', 'tb_title', 'tb_url', 'tb_ex', 'tb_name'),
				/* WHERE  */ array('tb_page' => $this->getID())
		);

		if (!$dbr->numrows($tbs))
			return;

		$tbtext = "";
		while ($o = $dbr->fetchObject($tbs)) {
			$rmvtxt = "";
			if ($wgUser->isAllowed( 'trackback' )) {
				$delurl = $this->mTitle->getFullURL("action=deletetrackback&tbid="
						. $o->tb_id . "&token=" . $wgUser->editToken());
				$rmvtxt = wfMsg('trackbackremove', $delurl);
			}
			$tbtext .= wfMsg(strlen($o->tb_ex) ? 'trackbackexcerpt' : 'trackback',
					$o->tb_title,
					$o->tb_url,
					$o->tb_ex,
					$o->tb_name,
					$rmvtxt);
		}
		$wgOut->addWikitext(wfMsg('trackbackbox', $tbtext));
	}

	function deletetrackback() {
		global $wgUser, $wgRequest, $wgOut, $wgTitle;

		if (!$wgUser->matchEditToken($wgRequest->getVal('token'))) {
			$wgOut->addWikitext(wfMsg('sessionfailure'));
			return;
		}

		if ((!$wgUser->isAllowed('delete'))) {
			$wgOut->permissionRequired( 'delete' );
			return;
		}

		if (wfReadOnly()) {
			$wgOut->readOnlyPage();
			return;
		}

		$db = wfGetDB(DB_MASTER);
		$db->delete('trackbacks', array('tb_id' => $wgRequest->getInt('tbid')));
		$wgTitle->invalidateCache();
		$wgOut->addWikiText(wfMsg('trackbackdeleteok'));
	}

	function render() {
		global $wgOut;

		$wgOut->setArticleBodyOnly(true);
		$this->view();
	}

	/**
	 * Handle action=purge
	 */
	function purge() {
		global $wgUser, $wgRequest, $wgOut;

		if ( $wgUser->isAllowed( 'purge' ) || $wgRequest->wasPosted() ) {
			if( wfRunHooks( 'ArticlePurge', array( &$this ) ) ) {
				$this->doPurge();
			}
		} else {
			$msg = $wgOut->parse( wfMsg( 'confirm_purge' ) );
			$action = $this->mTitle->escapeLocalURL( 'action=purge' );
			$button = htmlspecialchars( wfMsg( 'confirm_purge_button' ) );
			$msg = str_replace( '$1',
				"<form method=\"post\" action=\"$action\">\n" .
				"<input type=\"submit\" name=\"submit\" value=\"$button\" />\n" .
				"</form>\n", $msg );

			$wgOut->setPageTitle( $this->mTitle->getPrefixedText() );
			$wgOut->setRobotpolicy( 'noindex,nofollow' );
			$wgOut->addHTML( $msg );
		}
	}

	/**
	 * Perform the actions of a page purging
	 */
	function doPurge() {
		global $wgUseSquid;
		// Invalidate the cache
		$this->mTitle->invalidateCache();

		if ( $wgUseSquid ) {
			// Commit the transaction before the purge is sent
			$dbw = wfGetDB( DB_MASTER );
			$dbw->immediateCommit();

			// Send purge
			$update = SquidUpdate::newSimplePurge( $this->mTitle );
			$update->doUpdate();
		}
		$this->view();
	}

	/**
	 * Insert a new empty page record for this article.
	 * This *must* be followed up by creating a revision
	 * and running $this->updateToLatest( $rev_id );
	 * or else the record will be left in a funky state.
	 * Best if all done inside a transaction.
	 *
	 * @param Database $dbw
	 * @return int     The newly created page_id key
	 * @private
	 */
	function insertOn( $dbw ) {
		wfProfileIn( __METHOD__ );

		$page_id = $dbw->nextSequenceValue( 'page_page_id_seq' );
		$dbw->insert( 'page', array(
			'page_id'           => $page_id,
			'page_namespace'    => $this->mTitle->getNamespace(),
			'page_title'        => $this->mTitle->getDBkey(),
			'page_counter'      => 0,
			'page_restrictions' => '',
			'page_is_redirect'  => 0, # Will set this shortly...
			'page_is_new'       => 1,
			'page_random'       => wfRandom(),
			'page_touched'      => $dbw->timestamp(),
			'page_latest'       => 0, # Fill this in shortly...
			'page_len'          => 0, # Fill this in shortly...
		), __METHOD__ );
		$newid = $dbw->insertId();

		$this->mTitle->resetArticleId( $newid );

		wfProfileOut( __METHOD__ );
		return $newid;
	}

