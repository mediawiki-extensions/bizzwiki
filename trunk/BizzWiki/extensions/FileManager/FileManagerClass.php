<?php
/*
 * FileManagerClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 * 
 */

class FileManagerClass extends ExtensionClass
{
	// constants.
	const thisName = 'FileManager';
	const thisType = 'other';
	const id       = '$Id$';	
	  
	const actionCommit = 'commitfile';
	const actionRead   = 'readfile';

	const mNoCommit    = '__NOCOMMIT__';

	// error code constants
	const msg_nons = 1;
	const msg_folder_not_writable = 2;

	public static function &singleton()
	{ return parent::singleton( );	}
	
	function FileManagerClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits['other'][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Manages the files in a Mediawiki installation. Namespace for filesystem is ',
			'url' => self::getFullUrl(__FILE__),
		);
	}
	public function setup() 
	{ 
		parent::setup();
		
		// Keep this 'true' until I get around to doing
		// the 'commit' functionality.
		$this->docommit = true;

		# Add a new log type
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]                           = 'commitscript';
		$wgLogNames  ['commitfil']              = 'commitfil'.'logpage';
		$wgLogHeaders['commitfil']              = 'commitfil'.'logpagetext';
		$wgLogActions['commitfil/commitfil']    = 'commitfil'.'logentry';
		$wgLogActions['commitfil/commitok']     = 'commitfil'.'-commitok-entry';
		$wgLogActions['commitfil/commitfail']   = 'commitfil'.'-commitfail-entry';
		
		global $wgMessageCache, $wgFileManagerLogMessages;
		foreach( $wgFileManagerLogMessages as $key => $value )
			$wgMessageCache->addMessages( $wgFileManagerLogMessages[$key], $key );		
	}
		public function hUpdateExtensionCredits( &$sp, &$extensionTypes )
	// setup of this hook occurs in 'ExtensionClass' base class.
	{
		global $wgExtensionCredits;

		// first check if the proper rights management class is in place.
		if (defined('NS_FILESYSTEM'))
			$hresult = 'defined.';
		else
			$hresult = '<b>not defined!</b>';

		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if (isset($el['name']))		
				if ($el['name']==self::thisName)
					$el['description'].=$hresult;
				
		return true; // continue hook-chain.
	}
	public function hArticleSave( &$article, &$user, &$text, &$summary, $minor, $dontcare1, $dontcare2, &$flags )
	// This hook is used to capture the source file & save it also in the file system.
	{
		global $IP;
		
		// check if we are in the right namespace
		$ns = $article->mTitle->getNamespace();
		if ($ns != NS_FILESYSTEM) return true;

		// does the user have the right to commit scripts?
		// i.e. commit the changes to the file system.
		if (! $article->mTitle->userCan(self::actionCommit) ) return true;  

		// we are in the right namespace,
		// but are we committing to file?
		if (!$this->docommit) return true;
		
		// do we have a 'no commit' command in the text?
		$r = preg_match('/'.self::mNoCommit.'/si', $text);
		if ($r==1) return true;
		
		// we can attempt commit then.
		$titre = $article->mTitle->getText();
		$r = file_put_contents( $IP.'/'.$titre, $text );
		
		// write a log entry with the action result.
		// -----------------------------------------
		$action  = ($r === FALSE ? 'commitfail':'commitok' );
		$nsname  = Namespace::getCanonicalName( $ns );	
		$message = wfMsgForContent( 'commitfil-commit-text', $nsname, $titre );
		
		// we need to limit the text to 'commitscr' because of the database schema.
		$log = new LogPage( 'commitfil' );
		$log->addEntry( $action, $user->getUserPage(), $message );
		
		// disable auto summary
		// (security issue ...)
		$flags = ($flags & (~EDIT_AUTOSUMMARY));
		
		return true; // continue hook-chain.
	}
	public function hArticleFromTitle( &$title, &$article )
	// This hook is used to:
	// - Verify if a file is available in the filesystem
	// - Verify if a file is available in the database system
	{
		global $IP;
		
		// Paranoia
		if (empty($title)) return true; // let somebody else deal with this.
		
		// Are we in the right namespace at all??
		$ns = $title->getNamespace();
		if ($ns != NS_FILESYSTEM) return true; // continue hook chain.

		// get the original title name
		global $wgRequest, $wgTitle;
		$titre = $wgRequest->getVal( 'title' );
		$wgTitle = Title::newFromURL( $titre );

		// restore state.
		#$wgCapitalLinks = state;

		// If article is present in the database, used it.
		// Permissions are checked through normal flow.
		$a = new Article( $wgTitle );
		if ( $a->getId() !=0 ) 
		{
			$article = $a; // might as well return the object since we already created it!
			return true;
		}

		// Can the current user even 'read' the article page at all??
		// An extension can verify permission against namespace e.g.
		// 'Hierarchical Namespace Permissions'
		if (! $title->userCan(self::actionRead) ) return true;		
		
		// From this point, we know the article does not
		// exist in the database... let's check the filesystem.
		$filename = $title->getText();
		$result   = @fopen( $IP.'/'.$filename,'r' );
		if ($result !== FALSE) { fclose($result); $result = TRUE; }

		$id = $result ? 'filemanager-script-exists':'filemanager-script-notexists';
		$message = wfMsgForContent( $id, $filename );

		// display a nice message to the user about the state of the script in the filesystem.
		global $wgOut;
		$wgOut->setSubtitle( $message );

		return true; // continue hook-chain.
	}
	public function hEditFormPreloadText( &$text, &$title )
	// This hook is called to preload text upon initial page creation.
	// If we are in the NS_FILESYSTEM namespace and no article is found ('initial creation')
	// then let's check if the underlying file exists and preload it.
	//
	// NOTE that the 'edit' permission is assumed to be checked prior to entering this hook.
	//
	{
		// Are we in the right namespace at all??
		$ns = $title->getNamespace();
		if ($ns != NS_FILESYSTEM) return true; // continue hook chain.

		// Paranoia: Is the user allowed committing??
		// We shouldn't even get here if the 'edit' permission gets
		// verified adequately.
		if (! $title->userCan(self::actionCommit) ) return true;		

		$text = self::getFileContentsFromTitle( $title );
	
		return true; // be nice.
	}
	static function getFileContentsFromTitle( &$title )
	{
		global $IP;
		$filename = $title->getText();
		$text = @file_get_contents( $IP.'/'.$filename );
		
		return $text;
	}
	function hOutputPageBeforeHTML( &$op, &$text )
	// make sure we disable client side caching for NS_FILESYSTEM namespace.
	{
		global $wgTitle;
		
		// Are we in the right namespace at all??
		$ns = $wgTitle->getNamespace();
		if ($ns != NS_FILESYSTEM) return true; // continue hook chain.

		$op->enableClientCache(false);
		
		return true;
	}
	/**
		Place the 'reload' tab.
	 */
	public function hSkinTemplateTabs( &$st , &$content_actions )
	{
		// make sure we are in the right namespace.
		$ns = $st->mTitle->getNamespace();
		if ($ns != NS_FILESYSTEM) return true; // continue hook chain.
		
		// second, make sure the user has the 'reload' right.
		global $wgUser;
		if ( !$wgUser->isAllowed('reload') )
			return true;
		
		$content_actions['reload'] = array(
			'text' => 'reload',
			'href' => $st->mTitle->getLocalUrl( 'action=reload' )
		);

		return true;
	}
	
	/**
		This hook handles 'action=reload' query.
	 */	
	public function hUnknownAction( $action, $article )
	{
		// make sure we are in the right namespace.
		$ns = $article->mTitle->getNamespace();
		if ($ns != NS_FILESYSTEM) return true; // continue hook chain.
		
		// second, make sure the user has the 'reload' right.
		global $wgUser;
		if ( !$wgUser->isAllowed('reload') )
			return true;
			
		$text = self::getFileContentsFromTitle( $article->mTitle );
		
		$article->updateArticle( $text, '', false, false );
		
		return false;
	}
	
} // END CLASS DEFINITION
?>