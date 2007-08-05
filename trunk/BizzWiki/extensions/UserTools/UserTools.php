<?php
/*<wikitext>
{{Extension
|name        = UserTools
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/UserTools/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
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

</source>

== History ==

== Todo ==
* Internationalize

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
</wikitext>*/

$wgExtensionCredits[UserTools::thisType][] = array( 
	'name'        => UserTools::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => ' ',
	'url' 		=> StubManager::getFullUrl(__FILE__),						
);
class UserTools
{
	const thisName = 'UserTools';
	const thisType = 'other';

	const dataPage = 'data';
	const dataTag  = 'userdata';

	public function __construct() {}

	/**
		Modify the skin to show the specify action.
	 */
	public function hSkinTemplateTabs( &$st , &$content_actions )
	{
		// make sure we are in the right namespace.
		$ns = $st->mTitle->getNamespace();
		if ($ns != NS_USER) return true; // continue hook chain.
		
		// second, make sure the user has the 'reload' right.
		global $wgUser;
		if ( !$wgUser->isAllowed('details') )
			return true;
		
		$content_actions['details'] = array(
			'text' => 'details',
			'href' => $st->mTitle->getLocalUrl( 'action=reload' )
		);

		return true;
	}
	/**
	 */
	public function mg_( &$parser, &$input )
	{ 

	}

	public function tag_userdata( &$text, &$params, &$parser )
	{

	}
	
} // end class