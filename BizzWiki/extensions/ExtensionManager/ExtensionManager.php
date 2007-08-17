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


require('ExtensionManager.i18n.php');
require('Extension.php');
require('ExtensionDirectory.php');
require('ExtensionRepository.php');

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


//</source>
