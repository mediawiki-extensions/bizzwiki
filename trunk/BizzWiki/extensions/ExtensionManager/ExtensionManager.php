<?php
/*<!--<wikitext>-->
{{Extension
|name        = ExtensionManager
|status      = beta
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ExtensionManager/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose==
Provides a means of easily installing 'extensions' to MediaWiki.

== Features ==
* Definition of 'repositories'

== Theory of Operation ==

== Usage ==
Use the parser function '#extension' in the NS_EXTENSION namespace.
<nowiki>{{#extension: repo=REPOSITORY TYPE | dir=DIRECTORY [| name=NAME ] }}</nowiki>

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/ExtensionManager/ExtensionManager_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[ExtensionManager::thisType][] = array( 
	'name'    	=> ExtensionManager::thisName,
	'version' 	=> StubManager::getRevisionId('$Id$'),
	'author'  	=> 'Jean-Lou Dupont',
	'description' => "Provides installation and maintenance functions for MediaWiki extensions. ", 
	'url' 		=> StubManager::getFullUrl(__FILE__),	
);


requires('ExtensionManager.i18n.php');

class ExtensionDirectory
{
	static $directory = '/extensions';
	
	static function exists()
	{
		global $IP;
		
		$dir = $IP.self::$directory;
		
		clearstatcache();
		return is_dir( $dir );
	}
} // end 'ExtensionDirectory' class declaration

class Extension
{
	
	public function __construct( &$name )
	{
		
	}
	public function exists()
	{
		
	}
	public function writeFile( &$filename, &$code )
	{
		
	}
	
} // end 'Extension' class definition

class ExtensionManager
{
	const thisType = 'other';
	const thisName = 'ExtensionManager';

	const keyREPO = 'repo';
	const keyDIR  = 'dir';
	const keyNAME = 'name';

	static $msg;

	// Variables
	var $currentRepo;
	
	public function __construct() 
	{
		global $wgMessageCache;
		foreach( self::$msg as $key => $value )
			$wgMessageCache->addMessages( self::$msg[$key], $key );
			
		$this->init();
	}
	/**
		Initialize variables
	 */
	protected function init()
	{
		$this->currentRepo = null;	
	}
	/**
		Reports the status of this extension in the [[Special:Version]] page.
	 */	
	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	{
		global $wgExtensionCredits;

		$result = '';
		if (!defined('NS_EXTENSION'))
			$result .= wfMsg('extensionmanager-missing-namespace');
		
		if (!ExtensionDirectory::exists())
			$result .= wfMsg('extensionmanager'.'-missing-extensiondirectory');
					
		// add other checks here.
		
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if (isset($el['name']))		
				if ($el['name']==self::thisName)
					$el['description'] .= $result;
				
		return true; // continue hook-chain.
	}
	/**
		This method implements the 'parser function magic word' #extension.
	 */
	public function mg_extension( &$parser )
	{
		// process the argument list
		$args = func_get_args();
		$argv = StubManager::processArgList( $args, true );
		
		// get the parameters
		$repo = $argv[self::keyREPO];
		$dir  = $argv[self::keyDIR];
		
		$result = $this->validateParameters( $repo, $dir );
		if (!empty( $result ))
			return $result;
			
		$this->verifyExistence( $repo, $dir );
		
	}
	/**
		Verify if the extension already exists on this system.
	 */
	protected function verifyExistence( $repo, $dir )
	{
		
	}
	protected function validateParameters( &$repo, &$dir )
	{
		// First, let's try to load the class defining
		// the requested repository
		$this->currentRepo = ExtensionRepository::newFromClass( $repo, $dir );
		if (!is_object( $this->currentRepo ))
			return wfMsg('extensionmanager').wfMsgForContent('extensionmanager'.'-error-loadingrepo', $repo );

		// Repository looks OK;
		// Now check ...
			
	}


	
} // end 'ExtensionManager' class definition

abstract class ExtensionRepository
{
	// relative to the installation i.e. $IP
	const repoClassesDir = '/Repositories';
	
	var $project;
	var $directory;
	var $baseURI;
	
	public function __construct( $baseURI, &$project, &$directory )
	{
		$this->project = $project;
		$this->directory = $directory;
		$this->baseURI = $baseURI;
		
		$this->formatRepoURI();
	}
	
	/**
		Class Factory
	 */
	public static function newFromClass( &$name, &$repo, &$dir )
	{
		// is the class already loaded??
		if ( class_exists( $name ) )
			return true;

		$filename = __FILE__.self::repoClassesDir.'/'.$name.'.php';
		
		// silently try to load the class describing the repository
		@require( $filename );
		
		// check if we have succeeded (!)
		if ( class_exists( $name ) )
			return new $name( $dir );
			
		return null;
	}

	protected function formatRepoURI()
	{
		$project = htmlspecialchars( $this->project );

		$uri = $this->baseURI;
		$this->uri = str_replace( '$1', $project, $uri );
	}
	
	abstract public function exists();

	// Recursive function which preserves whole (relative) path information
	abstract public function getFileList();

	// Requires the full relative path
	abstract public function getFileCode();

} // end 'ExtensionRepository' class definition

//</source>
