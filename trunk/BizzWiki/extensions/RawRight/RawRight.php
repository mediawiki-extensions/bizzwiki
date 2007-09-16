<?php
/*<!--<wikitext>-->
{{Extension
|name        = RawRight
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/RawRight/ SVN]
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
== Purpose ==
This extension adds a 'raw' right. Only the users with the 'raw' permission can 'raw view' an article's source wikitext.

== FEATURES ==
* Can be used independantly of BizzWiki environment
* Displays operational information in 'Special:Version' page
* Integrates with Hierarchical Namespace Permissions extension to provide 'raw' right.

== DEPENDANCIES ==
* [[Extension:StubManager|StubManager extension]]
* Hierarchical Namespace Permissions extension
* MW > 1.10 (or patched earlier version)

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/RawRight/RawRight_stub.php');
</source>

== HISTORY ==
* Removed dependency on ExtensionClass
* Added 'stub'-enabled capability (usage of StubManager)
* Added some protection against !isset indexes in '$wgExtensionCredits'

== TODO ==
* Internationalization: add messages to cache i18n file
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[RawRight::thisType][] = array( 
	'name'    		=> RawRight::thisName, 
	'version'		=> StubManager::getRevisionId( '$Id$' ),
	'author'		=> 'Jean-Lou Dupont', 
	'description'	=> "Status: ",
	'url'			=> 'http://mediawiki.org/wiki/Extension:RawRight',			
);
 
class RawRight
{
	const thisName = 'RawRight';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	
	// Our class defines magic words: tell it to our helper class.
	public function __construct() {}

	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	// setup of this hook occurs in 'ExtensionClass' base class.
	{
		global $wgExtensionCredits;

		// first check if the proper rights management class is in place.
		if (class_exists('hnpClass'))
			$hresult = '<b>Hierarchical Namespace Permissions extension operational</b>';
		else
			$hresult = '<b>Hierarchical Namespace Permissions extension <i>not</i> operational</b>';

		// check directly in the source if the hook is present 
		$rawpage = @file_get_contents('includes/RawPage.php');
		
		if (!empty($rawpage))
			$r = preg_match('/RawPageViewBeforeOutput/si',$rawpage);
		
		if ( $r==1 )
			$rresult = '<b>RawPageViewBeforeOutput hook operational</b>';
		else
			$rresult = '<b>RawPageViewBeforeOutput hook <i>not</i> operational</b>';
		
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if (isset($el['name']))
				if ($el['name']==self::thisName)
					$el['description'].=$hresult." and ".$rresult;
				
		return true; // continue hook-chain.
	}
	
	public function hRawPageViewBeforeOutput( &$rawpage, &$text )
	{
		global $wgUser;
		
		if (! $wgUser->isAllowed( "raw") )		
		{
			$text = '';
			wfHttpError( 403, 'Forbidden', 'Unsufficient access rights.' );
			return false;
		}
		
		return true; // continue hook-chain.
	}
} // end class definition.
//</source>