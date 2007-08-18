<?php
/*<!--<wikitext>-->
{{Extension
|name        = ExtensionManager
|status      = experimental
|type        = pfunc
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
* Extensible: definition of 'repositories' can be extended
** <code>GoogleCode</code> is the default
* Secure: only executes in the NS_EXTENSION namespace
* Fast: only loads & executes when the user accesses the NS_EXTENSION namespace
** Standard feature of [[Extension:StubManager]]
* Manifest file
* Logging
** Installation success / fail etc.
* Localization
* Enable/Disable commands
** Requires 'manage_extension' right
* Update command
** Requires 'manage_extension' right
* Integrates with [[Extension:FileManager]]

== Usage ==
To add an extension simply use the '#extension' parser function in the NS_EXTENSION namespace.
<nowiki>{{#extension: repo=REPOSITORY TYPE | project=PROJECT NAME | dir=DIRECTORY }}</nowiki>
The name of the extension is the title name of the page where the '#extension' magic word is used.
The parameter <code>repo</code> specifies repository type.
The parameter <code>project</code> specifies the project.
The parameter <code>dir</code> specifies the directory of the repository where the extension is located.

== Usage Notes ==
=== Installation of a new extension ===
Each time a new extension is installed, there will be a short 'off-line' time for all the extensions
installed; this is due to the fact that [[Extension:ExtensionManager|Extension Manager]] needs to 
update a critical file and requires some 'downtime' to effect the changes.

=== Concurrency ===
Concurrent updates are not advised; only one user should do updates at the time.

== Installation notes ==
* Parser Caching is recommended
* Create a new namespace 'NS_EXTENSION'
** Proper permission management should be put in place

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload <b>all</b> this extension's files and place in the desired directory
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
	'description' => "Provides installation and maintenance for MediaWiki extensions. ", 
	'url' 		=> StubManager::getFullUrl(__FILE__),	
);

require('ExtensionManager.i18n.php');
require('ExtensionDirectory.php');
require('Extension.php');
require('ExtensionRepository.php');
require('ExtensionMagicWords.php');
require('ExtensionLog.php');

class ExtensionManager
{
	const thisType = 'other';
	const thisName = 'ExtensionManager';

	const keyREPO = 'repo';
	const keyDIR  = 'dir';
	const keyPRJ  = 'project';

	static $msg = array();

	// Variables
	var $currentRepo;
	var $currentRepoName;	
	var $currentProject;
	var $currentDir;
	var $currentExtension;
	
	public function __construct() 
	{
		# Add a new log type
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]						= 'extlog';
		$wgLogNames  ['extlog']				= 'extlog'.'logpage';
		$wgLogHeaders['extlog']				= 'extlog'.'logpagetext';
		$wgLogActions['extlog/installok']	= 'extlog'.'-installok-entry';
		$wgLogActions['extlog/installfail']	= 'extlog'.'-installfail-entry';		

		// Init the message cache.		
		global $wgMessageCache;
		$msg = $GLOBALS[ 'msg'.__CLASS__ ];
		
		foreach( $msg as $key => $value )
			$wgMessageCache->addMessages( $msg[$key], $key );		
		
		// Initialize variables.
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
					
		// Add list of managed extensions 	
				
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
		$prj  = $argv[self::keyPRJ];		
		
		$result = $this->validateParameters( $repo, $project, $dir );
		if (!empty( $result ))
			return $result;
		
		// the parameters check out.
		$this->currentRepoName = $repo;
		$this->currentDir      = $dir;
		$this->currentProject  = $prj;		
		
		if ($this->verifyExistence( $repo, $project, $dir ))
			return $this->doExtensionExists();
	
		return $this->doExtensionDoesNotExist();	
	}
	
	protected function doExtensionsExists()
	{
		
	}
	protected function doExtensionDoesNotExist()
	{
		// get extension files
		
		// create new filesystem directory
		
		// write files to filesystem
		
		// add extension to ExtensionList
		
			
	}
	/**
		Verify if the extension already exists on this system.
	 */
	protected function verifyExistence( $repo, $project, $dir )
	{
		global $wgTitle;
		$this->currentExtension = new Extension( $wgTitle->getDBkey() );
			
		
	}
	/**
		Create the repository object.
		This method only validates that an actual class supports
		the requested repository.
	 */
	protected function validateParameters( &$repo, &$project, &$dir )
	{
		// First, let's try to load the class defining
		// the requested repository
		$this->currentRepo = ExtensionRepository::newFromClass( $repo, $project, $dir );
		if (!is_object( $this->currentRepo ))
			return wfMsg('extensionmanager').wfMsgForContent('extensionmanager'.'-error-loadingrepo', $repo, $project );

		// everything OK.
		return null;
	}

	/**
		Replace the dynamic 'proprietary words' generated by this extension
		i.e. those that get parsed on every page view.
	 */
	function hOutputPageBeforeHTML( &$op, &$text )
	{
		ExtensionMagicWords::doReplaceDynamic( $text );
		
		return true;
	}
	/**
		Replace the static 'proprietary words' generated by this extension
		i.e. those that get parsed on every page edit.
	 */
	public function hParserBeforeStrip( &$parser, &$text, &$mStripState )
	{
		ExtensionMagicWords::doReplaceStatic( $text );
		
		return true;
	}	
} // end 'ExtensionManager' class definition

/**
	This class handles the file 'ExtensionList'.
	It supports 'atomic' operations for effecting updates
	on the file.
	
	Notes:
	- file existence
	- 

		// Put 'ExtensionList' off-line
		// update ExtensionList.php
		// Restore 'ExtensionList' 
	
 */
class ExtensionList
{
	static $liste = array();
	
	public static function getList()
	{ return self::$liste; }
	
	public static function add()
	{
		
	}
	
	public static function remove( &$name )
	{
		
	}
	
	/**
		Enables/disables the specified extension
	 */
	public static function setState( &$name )
	{}

	/**
		Gets the current status of the extension in the list
	 */
	public static function getState( &$name )
	{}
	
} // end class declaration

//</source>
