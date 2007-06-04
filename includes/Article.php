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
