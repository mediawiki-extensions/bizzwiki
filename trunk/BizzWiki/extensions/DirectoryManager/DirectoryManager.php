<?php
/*<!--<wikitext>-->
{{Extension
|name        = DirectoryManager
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/DirectoryManager/ SVN]
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
== Purpose==
Provides a namespace 'Directory' for browsing the filesystem of a MediaWiki installation.

== Features ==
* Directory tree structure roots on MediaWiki installation
* Security: enforcement of the 'read' right
* Integrates with [[Extension:FileManager]]
* Highly customizable through 'hooks'

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/DirectoryManager/DirectoryManager_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[DirectoryManager::thisType][] = array( 
	'name'    => DirectoryManager::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => "Provides a namespace 'Directory' for browsing the filesystem of a MediaWiki installation.", 
	'url' 		=> StubManager::getFullUrl(__FILE__),	
);

class DirectoryManager
{
	const thisType = 'other';
	const thisName = 'DirectoryManager';
	
	static $msg;
	static $dirBase;
	
	// Constants
	const filePatternTag = "/<filepattern(?:.*)\>(.*)(?:\<.?filepattern)>/siU";
	const dirPatternTag  = "/<dirpattern(?:.*)\>(.*)(?:\<.?dirpattern)>/siU";	
	const linePatternTag = "/<linepattern(?:.*)\>(.*)(?:\<.?linepattern)>/siU";		
	
	// Template related
	var $filePattern;
	var $dirPattern;
	var $linePattern;
	
	// Variables
	var $dir;
	var $files;
	var $template;
	var $page;
	
	public function __construct() 
	{
		global $IP;
		self::$dirBase = $IP;

		$this->filePattern = null;
		$this->dirPattern = null;
		$this->linePattern = null;
		
		global $wgMessageCache;
		foreach( self::$msg as $key => $value )
			$wgMessageCache->addMessages( self::$msg[$key], $key );		
	}
	
	public function hArticleFromTitle( &$title, &$article )
	{
		global $wgOut;
		global $wgUser;
		global $IP;
				
		// we are only interested in one particular namespace
		$ns = $title->getNamespace();
		if (NS_DIRECTORY!=$ns)
			return true;
		
		$titre = $title->getText();
		
		if (!$wgUser->isAllowed( 'read', $ns, $titre ))
		{
			$skin = $wgUser->getSkin();
			$wgOut->setPageTitle( wfMsg( 'directorymanager'.'title' ) );
			$wgOut->setSubtitle( wfMsg( 'directorymanager'.'view', $skin->makeKnownLinkObj( $title ) ) );
			$wgOut->addWikiText( wfMsg( 'badaccess' ) );
			
			return false; // stop normal processing flow.
		}

		$this->dir = $IP.'/'.$title->getText();
		
		$article = new Article( $title );
		
		// let mediawiki handle the articles that already exist
		if ( $article->getID() != 0 )
			return true;

		$this->dirTs = self::getDirectoryTimestamp( $this->dir );

		// Give other extensions a chance to:
		// - Cache
		// - Abort
		if (wfRunHooks( 'DirectoryManagerBegin', array( &$title, &$article, self::$dirBase, $this->dir, $this->dirTs ) ))
			$this->doDirectoryPageDisplay( $title, $article );
		
		return true;
	}
	/**
		Block edition.
	 */
	public function hCustomEditor( $article, $user )
	{
		// we are only interested in one particular namespace
		$ns = $article->mTitle->getNamespace();
		if (NS_DIRECTORY!=$ns)
			return true;

		// there is nothing to edit in this namespace!
			
		return false;	
	}
	private function doDirectoryPageDisplay( &$title, &$article )
	{
		$this->template = null;
		
		// let extensions the change to modify the template
		wfRunHooks( 'DirectoryManagerBeginPageDisplay', array( &$this, &$files, &$this->dir, &$this->template ) );		
		if ($this->template === null)
			$this->template = $this->getTemplate();
		
		$this->files = $this->getDirectoryInformation( $this->dir, self::$dirBase );

		// let extensions the chance to modify the files list.
		// Modify the 'template' parameter to add/remove wikitext
		wfRunHooks( 'DirectoryManagerBeforeCreatePage', array( &$files, $this->template ) );

		$this->page = $this->createDirectoryPage( $this->dir, self::$dirBase, $this->template, $this->files );
		
		// let extensions the chance to modify the page before it is parsed.
		wfRunHooks( 'DirectoryManagerBeforeParsePage', array( &$page ) );		
		
		$po = $this->parsePage( $this->page, $title, $article );
		
		// let extensions the chance to do last minute changes
		// before the page is actually displayed.
		wfRunHooks( 'DirectoryManagerBeforeDisplayPage', array( &$po ) );		
				
		$this->displayPage( $po );
	}
	/**
		The default template 
	 */
	private function getTemplate()
	{
		$template = wfMsgForContent( 'directorymanager'.'-template' );	
		
		$this->filePattern = self::extractPattern( self::filePatternTag, $template );
		$this->dirPattern  = self::extractPattern( self::dirPatternTag, $template );		
		$this->linePattern = self::extractPattern( self::linePatternTag, $template );
		
		return $template;			
	}
	private static function extractPattern( $pattern, &$text, $remove = true )
	{
		$r = preg_match( $pattern, $text, $m );

		if ($remove)
			$text = preg_replace( $pattern, '', $text );
			
		if ($r===1)
			return $m[1];
		
		return null;
	}
	private static function replaceParams( &$text )
	{
		$args = func_get_args();
		array_shift( $args );
		
		for ($i=1; $i<10; $i++)
		{
			// loop whilst we have parameters to replace
			if (!isset( $args[$i-1] ) )
				break;
			$text = str_replace( '$'.$i, $args[$i-1], $text );
		}
	}
	
	/**
		Outputs WikiText
	 */
	private function createDirectoryPage( &$dir, &$base, &$template, &$files )	
	{
		// start by adding the template content
		// to the beginning of the page.
		// The 'patterns' should have been removed by now.
		$page = $template;
		
		foreach( $files as $file )
		{
			if ( $file['name'] =='.' )
				continue;
				
			if ( $file['name'] == '..' )
				$file['name'] = self::getDotDotFile( $dir, $base );
				
			// we might have reached the root...
			if (empty($file['name']))
				continue;
				
			switch( $file['type'] )
			{
				case 'dir':
					$sline = $this->dirPattern;				
					break;
					
				case 'file':
					$sline = $this->filePattern;					
					break;
			}
			self::replaceParams( $sline, $file['name'] );
			$line = $this->linePattern;
			self::replaceParams( $line, $sline );
			
			$page .= $line;
		}

		return $page;
	}
	/**
	 */
	private function parsePage( &$text, &$title, &$article )	 
	{
		global $wgParser;
		global $wgUser;
		
		# Parse the text
		$options = new ParserOptions;
		$options->setTidy(true);
		$poutput = $wgParser->parse( $text, $title, $options );

		return $poutput;
	}

	private function displayPage( &$parserOutput )
	{
		global $wgOut;
		
		$wgOut->addParserOutput( $parserOutput );
	}
	/**
		e.g.
		array (
				0 =>
				array (
				'name' => '.',
				'type' => 'dir',
				'mtime' => 1186483435,
				),
				1 =>
				array (
				'name' => '..',
				'type' => 'dir',
				'mtime' => false,			# NOTE HERE
				),
				2 =>
				array (
				'name' => '.htaccess',
				'type' => 'file',
				'mtime' => 1181832196,
				),
				3 =>
				array (
				'name' => 'AdminSettings.php',
				'type' => 'file',
				'mtime' => 1178738087,
				),
			...
	 */
	 
	public static function getDirectoryInformation( &$dir, &$base )
	{
		$files = @scandir( $dir );
		$upDir = self::getDotDotFile( $dir, $base );
		$thisDir = self::getRelativePath( $dir, $base );
		
		#echo ' upDir:'.$upDir."<br/>\n";
		#echo ' thisDir:'.$thisDir."<br/>\n";		
		
		foreach( $files as &$file )
		{
			$info = @filetype( $dir.'/'.$file );

			if ( '.' == $file )	$info = 'dir';
			if ( '..' == $file )$info = 'dir';

			$filename = $file;
			$mtime = @filemtime( $dir.'/'.$file );
		
			if ( $file != '.' && $file != '..' && $thisDir != '/' )
				$filename = $thisDir.'/'.$filename;

			$file = array( 'name' => $filename, 'type' => $info , 'mtime' => $mtime );
		}
	
		return $files;
	}
	/**
		Returns the filename (directory name really) correspondig to '..'
	 */
	public static function getDotDotFile( &$dir, &$base )
	{
		$d = str_replace( "\\", '/', $dir );

		$pathInfo = pathinfo( $d );
		
		$p = $pathInfo['dirname'];		

		// now remove the base.
		$s = self::getRelativePath( $p, $base );

		// make sure we haven't reached the root.
		if (empty($s))
			return '/';
			
		return $s;
	}

	public static function getRelativePath( &$dir, &$base )
	{
		$d = str_replace( "\\", '/', $dir );

		return substr( $d, strlen($base)+1 );
	}

	public static function getDirectoryTimestamp( &$dir )
	{
		return @filemtime( $dir );	
	}
} // end class

require( 'DirectoryManager.i18n.php' );

//</source>