<?php
/*<!--<wikitext>-->
{{Extension
|name        = RecentChangesManager
|status      = stable
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/RecentChangesManager/ SVN]
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
Prevents RecentChanges table entries from being deleted.

== Features ==


== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/RecentChangesManager/RecentChangesManager_stub.php');
</source>

== History ==
* Removed dependency on ExtensionClass
* Added 'stubbing' capability through StubManager

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[RecentChangesManager::thisType][] = array( 
	'name'    		=> RecentChangesManager::thisName, 
	'version'     	=> StubManager::getRevisionId( '$Id$' ),
	'author'  		=> 'Jean-Lou Dupont', 
	'description' 	=> "Prevents RecentChanges entries from being deleted",
	'url' 			=> StubManager::getFullUrl(__FILE__),			
);

class RecentChangesManager
{
	const thisName = 'RecentChangesManager';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	
	// Our class defines magic words: tell it to our helper class.
	public function __construct() {}
	
	public function hArticleEditUpdatesDeleteFromRecentchanges( &$article )
	{
		// don't delete entries from RecentChanges
		return false;
	}

} // end class definition.
//</source>