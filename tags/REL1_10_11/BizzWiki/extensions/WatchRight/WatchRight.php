<?php
/*<!--<wikitext>-->
{{Extension
|name        = WatchRight
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/WatchRight/ SVN]
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
Provides watch/unwatch rights enforcement.

== Features ==


== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/WatchRight/WatchRight_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[WatchRight::thisType][] = array( 
	'name'    		=> WatchRight::thisName, 
	'version'		=> StubManager::getRevisionId( '$Id$' ),
	'author'		=> 'Jean-Lou Dupont', 
	'description'	=> "Enforces 'watch/unwatch' rights",
	'url' 			=> StubManager::getFullUrl(__FILE__),			
);

class WatchRight
{
	const thisName = 'WatchRight';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	
	public function __construct() {}
	
	public function hWatchArticle( &$user, &$article )
	{
		if (!$user->isAllowed( 'watch' ))
			return $this->error( 'watch' );
		return true;
	}

	public function hUnwatchArticle( &$user, &$article )
	{
		if (!$user->isAllowed( 'unwatch' ))
			return $this->error( 'unwatch' );
		return true;			
	}
	private function error( $msg )
	{
		global $wgOut;
	
		$wgOut->addWikiText( wfMsg( 'badaccess' ) );
		
		return false;
	}
	
	public function hSkinTemplateTabs( &$st , &$content_actions )
	{
		global $wgUser;
		
		if (!$wgUser->isAllowed( 'watch') )
			unset( $content_actions['watch'] );

		if (!$wgUser->isAllowed( 'unwatch') )
			unset( $content_actions['unwatch'] );

		return true;
	}
} // end class definition.
//</source>