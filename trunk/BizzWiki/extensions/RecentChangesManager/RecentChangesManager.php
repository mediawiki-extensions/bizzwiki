<?php
/*<wikitext>
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

== Purpose==
Prevents RecentChanges table entries from being deleted.

== Features ==


== Dependancy ==
* [[Extension:StubManager]]

== Installation ==
To install independantly from BizzWiki:
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'RecentChangesManager', 
							'extensions/RecentChangesManager/RecentChangesManager.php',
							null,					// i18n file			
							array('ArticleEditUpdatesDeleteFromRecentchanges'),	// hooks
							false, 					// no need for logging support
							null,					// tags
							null,					// parser Functions
							null
						 );
</source>

== History ==
* Removed dependency on ExtensionClass
* Added 'stubbing' capability through StubManager

== Code ==
</wikitext>*/

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
?>