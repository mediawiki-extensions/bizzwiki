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
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/XYZ/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose==


== Features ==


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
	'description' => " ", 
);

class DirectoryManager
{
	const thisType = 'other';
	const thisName = 'DirectoryManager';
	
	public __construct() {}
	
	public function hArticleFromTitle( &$title, &$article )
	{
		// we are only interested in one particular namespace
		$ns = $title->getNamespace();
		if (NS_DIRECTORY==$ns)
			return true;
		
		$article = new Article( $title );
		
		// let mediawiki handle the articles that already exist
		if ( $article->getID() != 0 )
			return true;
		
	}
} // end class
//</source>
