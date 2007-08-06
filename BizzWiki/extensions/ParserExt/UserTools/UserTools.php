<?php
/*<wikitext>
{{Extension
|name        = UserTools
|status      = beta
|type        = parser
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

	public function __construct() {}
	
	/**
	 */
	public function mg_userlanguage( &$parser )
	{ 
		global $wgUser;
		return $wgUser->getOption( 'language' );
	}
	/**
	
	 */
	public function mg_usergetoption( &$parser, $whichOption )
	{
		global $wgUser;
		return $wgUser->getOption( $whichOption, '' );
	}
	
} // end class