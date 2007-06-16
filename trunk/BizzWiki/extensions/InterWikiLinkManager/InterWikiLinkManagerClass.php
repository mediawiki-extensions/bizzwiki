<?php
/*
 * InterWikiLinkManagerClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 * 
 */

class InterWikiLinkManagerClass extends ExtensionClass
{
	// constants.
	const thisName = 'InterWikiLinkManager';
	const thisType = 'other';

	static $mgwords = array( 'iwl' );
	  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function InterWikiLinkManagerClass( self::$mgwords, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits['other'][] = array( 
			'name'        => self::thisName, 
			'version'     => '$Id$',
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Manages the files in a Mediawiki installation. Namespace for filesystem is '
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
			if ($el['name']==self::thisName)
				$el['description'].=$hresult;
				
		return true; // continue hook-chain.
	}
	public function mg_iwl( &$parser, $prefix, $uri, $local, $trans )
	{
		
	}	
	
	public function hArticleSave( &$article, &$user, &$text, &$summary, $minor, $dontcare1, $dontcare2, &$flags )
	{
		// check if we are in the right namespace
		$ns = $article->mTitle->getNamespace();
		if ($ns != NS_INTERWIKI) return true;

		// does the user have the right to commit scripts?
		// i.e. commit the changes to the file system.
		if (! $article->mTitle->userCan(self::actionCommit) ) return true;  



		
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
	{
		// Paranoia
		if (empty($title)) return true; // let somebody else deal with this.
		
		// Are we in the right namespace at all??
		$ns = $title->getNamespace();
		if ($ns != NS_INTERWIKI) return true; // continue hook chain.



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
	// If we are in the NS_INTERWIKI namespace and no article is found ('initial creation')
	// then let's get the database entries.
	//
	// NOTE that the 'edit' permission is assumed to be checked prior to entering this hook.
	//
	{
		// Are we in the right namespace at all??
		$ns = $title->getNamespace();
		if ($ns != NS_INTERWIKI) return true; // continue hook chain.

		// Paranoia: Is the user allowed committing??
		// We shouldn't even get here if the 'edit' permission gets
		// verified adequately.
		if (! $title->userCan(self::actionCommit) ) return true;		


	
		return true; // be nice.
	}
	
} // END CLASS DEFINITION
?>