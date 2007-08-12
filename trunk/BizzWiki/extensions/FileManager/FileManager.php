<?php
/*<!--<wikitext>-->
{{Extension
|name        = FileManager
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/FileManager/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{SUBPAGENAME}} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->

== Purpose ==
This Mediawiki extension enables a user with the 'commitfile' right to edit files in the Mediawiki installation directory.  

== Features ==
* Can be used independantly of BizzWiki environment 
* New rights: 'readfile', 'commitfile'
* Logging
* New Namespace 'NS_FILESYSTEM'
* Support for titles beginning with small caps; need the title to be prefixed with '/'
** e.g. to have access to 'includes/Setup.php' just reference the title 'Filesystem:/includes/Setup.php'
* No auto summary upon page creation

== DEPENDANCY ==
* [[Extension:StubManager]]

== History ==
* fixed for 'wgCapitalLinks' 
* fixed for suppressing PHP error messages on file_get_contents
* fixed logging messages
* disabled 'auto summary' upon page creation (clogs recentchanges, logs etc.)
* added 'reload' functionality
* Added some protection against !isset indexes in '$wgExtensionCredits'
* Added 'proprietary words' functionality
** @@file@@   replaces for the current filename
** @@mtime@@  replaces for the current filename last modification timestamp
** @@currentmtime@@ replaces for the current extracted filename last modification timestamp
* Removed extraneous '/' in the path name
* Added 'parser phase 2' magic words:
** ( ($#extractfile|@@file@@$) )   : extracts the filename returned through the proprietary word '@@file@@'
** ( ($#extractmtime|@@mtime@@$) ) : extracts 'mtime' returned through the proprietary word '@@mtime@@'
* EditFormPreload: added check for title both 'underscored' and not 

== TODO ==
* internationalization
* add 'edit from filesystem' functionality: capability to 'reload' a file from the filesystem
* enhance 'logging' through $type etc.

== Installation ==
* Install [[Extension:StubManager]] extension
* Download all the files from the SVN link
* Place in a directory e.g. 'extensions/FileManager'
* Place the following after 'StubManager' in <code>LocalSettings.php</code>
<source lang=php>
require('extensions/FileManager/FileManager_stub.php');
</source>

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits['other'][] = array( 
	'name'        => FileManager::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Manages the files in a Mediawiki installation. Namespace for filesystem is ',
	'url' 		=> StubManager::getFullUrl(__FILE__),
);

require( 'FileManager.i18n.php' );

class FileManager
{
	// constants.
	const thisName = 'FileManager';
	const thisType = 'other';
	  
	const actionCommit = 'commitfile';
	const actionRead   = 'readfile';

	const mNoCommit    = '__NOCOMMIT__';

	static $pWords = array(
							'/\@\@file(.*)\@\@/siU'		=> 'pw_file',
							'/\@\@mtime(.*)\@\@/siU'	=> 'pw_mtime',
							'/\@\@clearcache\@\@/siU'	=> 'pw_clearcache',
							);

	// error code constants
	const msg_nons = 1;
	const msg_folder_not_writable = 2;
	
	// variables
	var $currentFile;
	var $currentExtractFile;
	var $currentExtractMtime;
	var $requestTitle;

	function __construct()
	{
		# Add a new log type
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]                          = 'commitfil';
		$wgLogNames  ['commitfil']             = 'commitfil'.'logpage';
		$wgLogHeaders['commitfil']             = 'commitfil'.'logpagetext';
		$wgLogActions['commitfil/commitok']	   = 'commitfil'.'-commitok-entry';
		$wgLogActions['commitfil/commitfail']  = 'commitfil'.'-commitfail-entry';		
		$wgLogActions['commitfil/commitfail2'] = 'commitfil'.'-commitfail2-entry';				
		
		global $wgMessageCache;

		$msg = $GLOBALS[ 'msg'.__CLASS__ ];
		
		foreach( $msg as $key => $value )
			$wgMessageCache->addMessages( $msg[$key], $key );		

		// Keep this 'true' until I get around to doing
		// the 'commit' functionality.
		$this->docommit = true;
		
		// MediaWiki messes up with artitle title names i.e. capitalization
		#$this->requestTitle = $wgRequest->getText( 'title' );
	}
	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
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
		$shortTitle = self::getShortTitle( $titre );
		
		$this->currentFile = $IP.'/'.$titre;

		$nsname  = Namespace::getCanonicalName( $ns );	

		// next, check if the 'filename' is valid.
		// If not, file a log entry.
		/*
		if (!self::checkFilename( $titre ))
		{
			$action = 'commitfail2';
			$message = wfMsgForContent( 'commitfil-commit-text', $nsname, $titre, $shortTitle );			
			$log = new LogPage( 'commitfil' );
			$log->addEntry( $action, $user->getUserPage(), $message );
		
			return true;			
		}		
		*/
		$r = file_put_contents( $this->currentFile, $text );
		
		// write a log entry with the action result.
		// -----------------------------------------
		$action  = ($r === FALSE) ? 'commitfail':'commitok';
		$message = wfMsgForContent( 'commitfil-commit-text', $nsname, $titre, $shortTitle );
				
		// we need to limit the text to 'commitscr' because of the database schema.
		$log = new LogPage( 'commitfil' );
		$log->addEntry( $action, $user->getUserPage(), $message );
		
		// disable auto summary
		// (security issue ...)
		$flags = ($flags & (~EDIT_AUTOSUMMARY));
		
		// Replace Proprietary Words
		$this->doProprietaryWords( $text );
		
		return true; // continue hook-chain.
	}
	public function hArticleFromTitle( &$title, &$article )
	// This hook is used to:
	// - Verify if a file is available in the filesystem
	// - Verify if a file is available in the database mystem
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
		
		#$sfilename = self::sanitizeFilename( $filename );
		
		$result   = @fopen( $IP.$filename,'r' );
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

		// stop hook chain.
		return false; 
	}
	static function getFileContentsFromTitle( &$title )
	{
		global $IP;
		$filename = $title->getText();
		$text = @file_get_contents( $IP.'/'.$filename );
		
		// if we get an error, try another form
		if ($text === false)
		{
			$filename = $title->getDBkey();
			$text = @file_get_contents( $IP.'/'.$filename );
		}
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
	private static function getShortTitle( $title )
	{
		$v = explode('/', $title );
		$shortText = $v[ count($v)-1 ];
		
		return $shortText;	
	}
	/**
		Replaces '/../' for pattern from filename
	 */
	private static function sanitizeFilename( &$filename, &$pattern = '/./' )
	{
		$iresult = str_replace('/../', $pattern, $filename );
		$result  = str_replace('\..\\', $pattern, $iresult  );
		
		return $result;
	}
	private static function checkFilename( &$filename )
	{
		if (strpos( $filename, '/../' )!==false)
			return false;
			
		if (strpos( $filename, '\..\\' )!==false)
			return false;

		return true;	
	}
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	
	/**
		Proprietary Words functionality
	 */	
	private function doProprietaryWords( &$text )
	{
		foreach( self::$pWords as $pattern => $method )
		{
			$match = null;			
			
			$r = preg_match( $pattern, $text, $m );
			if (isset( $m[1] ))
				$match = $m[1];
				
			// check if we have at least one occurence of the proprietary word
			if ( (  $r !== false) && ($r>0) )
			{
				// get the value associated with the word
				$value = $this->$method( $match );
				// replace all occurences
				$text = preg_replace( $pattern, $value, $text );
			}
			
		}
	}

	/**
		Meant to be used in conjunction with the proprietary word '@@mtime@@'
	 */
	public function mg_extractmtime( &$parser, &$mtime, $show = false )
	{
		$this->currentExtractMtime = null;
		
		preg_match( '/\@\@mtime: (.*)\@\@/siU', $mtime, $m );
		
		if (isset( $m[1] ))
			$this->currentExtractMtime = $m[1];
		
		if ($show)
			return $this->currentExtractMtime;
			
		return null;
	}
	/**
		Meant to be used in conjunction with the proprietary word '@@file@@'	
	 */
	public function mg_extractfile( &$parser, &$file, $show = false )
	{
		$this->currentExtractFile = null;
		
		preg_match( '/\@\@file: (.*)\@\@/siU', $file, $m );
		
		if (isset( $m[1] ))
			$this->currentExtractFile = $m[1];
		
		if ($show)
			return $this->currentExtractFile;
			
		return null;
	}
	/**
		Returns 'newerText' if the file is newer than the 'mtime' timestamp suggests
		else returns 'olderText'.
	 */
	public function mg_comparemtime( &$parser, &$newerText, &$olderText )
	{
		$current_mtime = @filemtime( $this->currentExtractFile );
		if ($current_mtime > $this->currentExtractMtime)
			return $newerText;
			
		return $olderText;
	}
	/**
		Returns the current modification timestamp of the
		extracted current filename.
	 */
	public function mg_currentmtime( &$parser )
	{
		return @filemtime( $this->currentExtractFile );	
	}


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


	private function pw_file()
	{
		return '@@file: '.$this->currentFile.'@@';
	}
	private function pw_mtime()
	{
		return '@@mtime: '.@filemtime( $this->currentFile ).'@@';
	}
	private function pw_clearcache()
	{
		clearstatcache();
		return '@@clearcache@@';	
	}

} // END CLASS DEFINITION
