<?php
/*<!--<wikitext>-->
{{Extension
|name        = RepositoryManager
|status      = experimental
|type        = pfunc
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/RepositoryManager/ SVN]
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

== Usage ==

== Usage Notes ==

== Installation notes ==
* Parser Caching is recommended
* Create a new namespace 'NS_REPO'
** Proper permission management should be put in place

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]
* [[Extension:NamespaceManager|NamespaceManager extension]]
* [[Extension:PageServer|PageServer extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install all the dependent extensions (see above)
* Dowload <b>all</b> this extension's files and place in the desired directory
=== Example 'LocalSettings.php' ===
<source lang=php>
require('extensions/StubManager.php');
require('extensions/NamespaceManager/NamespaceManager_stub.php');
require('extensions/PageServer/PageServer_stub.php');
require('extensions/ExtensionManager/ExtensionManager_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

require('Repository.php');

class RepositoryManager extends NamespaceManager
{
	const thisType = 'other';
	const thisName = 'RepositoryManager';

	static $thisDir;
	static $msg = array();

	// Variables
	
	public function __construct( &$title ) 
	{
		// Enable raw html in this namespace.
		global $wgRawHtml;
		$wgRawHtml = true;
		
		// Initialize variables.
		self::$thisDir = dirname(__FILE__);
		
		parent::__construct( $title );
	}
	/**
		This method uses the 'PageServer' extension to load and parse
		a wikitext page stored in the filesystem.
	 */
	private function getTemplate( $templateName, $minify = false )
	{
		$filename = self::$thisDir.'/presentation/'.$templateName;
		
		return PageServer::load( $filename, $minify );
	}

	/**
	 */
	public function view()
	{
		global $wgOut;
		global $wgEnableParserCache;
		global $wgUser;
		
		// first, check if the article already exists!
		if ($this->getID() == 0)
			return $this->handleCreate();
		
		// Make sure the user has the appropriate right
		if ( !$this->mTitle->userCanRead() ) 
			return $this->doPermissionError(	$article->mTitle,
												'repositorymanager'.'-permissionerror-title',
												'repositorymanager'.'-permissionerror-read',
												'repositorymanager'.'-permissionerror-subtitle'
											);
		
		
		$parserCache =& ParserCache::singleton();
	
		$ns = $this->mTitle->getNamespace(); # shortcut

		# Should the parser cache be used?
		$pcache = $wgEnableParserCache;

		$wgOut->setArticleFlag( true );
		
		wfRunHooks( 'RepositoryManagerViewHeader', array( &$this ) );
		
		if ( !$wgOut->tryParserCache( $this, $wgUser ) )
		{		
			$text = $this->getContent();		

			if ( $pcache )
				# Display content and save to parser cache
				$this->outputWikiText( $text );
			else
			{
				# Display content and don't save to parser cache
				# With timing hack -- TS 2006-07-26
				$time = -wfTime();
				$this->outputWikiText( $text, false );
				$time += wfTime();
			}
		}

		/* title may have been set from the cache */
		$t = $wgOut->getPageTitle();
		if( empty( $t ) ) {
			$wgOut->setPageTitle( $this->mTitle->getPrefixedText() );
		}

		$this->viewUpdates();
	} // end view method.

	/**
		Presents the 'Create' form to the user
		and saves it in the database +/- parser cache.	
	 */
	public function handleCreate()
	{
		$header = $this->getTemplate( 'HeaderCreate.page', true );
		$text = $header.$this->getTemplate( 'Create.page' );
		wfRunHooks( 'RepositoryManagerCreatePage', array( &$this, &$text ) );
				
		$this->doEdit( $text, '', EDIT_NEW );
	}
	public function edit()
	{
		if ($this->getID() == 0)
			return $this->handleCreate();

		return $this->view();
	}
	
	/**
		Reports the status of this extension in the [[Special:Version]] page.
	 */	
	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	{
		global $wgExtensionCredits;

		$result = '';
		
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

	public function delete()
	{
		return parent::delete();	
	}

} // end 'ExtensionManager' class definition

//</source>
