<?php
/*
 * FileManagerClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 * $LastChangedRevision$
 */

class FileManagerClass extends ExtensionClass
{
	// constants.
	const thisName = 'FileManager';
	const thisType = 'other';
	  
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
			'version'     => '$Id$',
			'author'      => 'Jean-Lou Dupont', 
			'url'         => 'http://www.bluecortex.com',
			'description' => 'Manages the files in a Mediawiki installation'
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
		$wgLogNames  ['commitfil']              = 'commitfilelogpage';
		$wgLogHeaders['commitfil']              = 'commitfilelogpagetext';
		$wgLogActions['commitfil/commitfil']    = 'commitfilelogentry';
		$wgLogActions['commitfil/commitok']     = 'commitfilelog-commitok-entry';
		$wgLogActions['commitfil/commitfail']   = 'commitfilelog-commitfail-entry';
		
		global $wgMessageCache, $wgFileManagerLogMessages;
		foreach( $wgFileManagerLogMessages as $key => $value )
			$wgMessageCache->addMessages( $wgFileManagerLogMessages[$key], $key );		
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
		$titre = $article->mTitle->getBaseText();
		$r = file_put_contents( $IP.'/'.$titre, $text );
		
		// write a log entry with the action result.
		// -----------------------------------------
		$action  = ($r === FALSE ? 'commitfail':'commitok' );
		$nsname  = Namespace::getCanonicalName( $ns );	
		$message = wfMsgForContent( 'commitfilelog-commit-text', $nsname, $titre );
		
		// we need to limit the text to 'commitscr' because of the database schema.
		$log = new LogPage( 'commitfil' );
		$log->addEntry( $action, $user->getUserPage(), $message );
		
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

		// If article is present in the database, used it.
		// Permissions are checked through normal flow.
		$a = new Article( $title );
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
		$filename = $title->getBaseText();
		$result   = @fopen( $IP.'/'.$filename,'r' );
		if ($result !== FALSE) { $fclose($result); $result = TRUE; }

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

		global $IP;
		$filename = $title->getBaseText();
		$text = file_get_contents( $IP.'/'.$filename );
	
		return true; // be nice.
	}
	// public function hUnknownAction( $action, $article )
	/*  This hook is used to implement the custom 'action=commitscript'
	 */
	
} // END CLASS DEFINITION
?>