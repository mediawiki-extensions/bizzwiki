<?php
/*<wikitext>
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

== Purpose==
Provides watch/unwatch rights enforcement.

== Features ==


== Dependancy ==
* [[Extension:StubManager]]

== Installation ==
To install independantly from BizzWiki:
* Download [[Extension:StubManager]]
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'WatchRight', 
							'extensions/WatchRight/WatchRight.php',
							null,
							array( 'WatchArticle','UnwatchArticle','SkinTemplateTabs' ),
							false,	// no need for logging support
							null,	// tags
							null,	// no parser functions
							null	// no magic words
						 );
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
</wikitext>*/

global $wgExtensionCredits;
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
?>