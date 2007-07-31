<?php
/*<wikitext>
 RawRight.php by Jean-Lou Dupont

== Purpose ==
This extension adds a 'raw' right. Only the users with the 'raw' permission can 'raw view' an article's source wikitext.

== FEATURES ==
* Can be used independantly of BizzWiki environment
* Displays operational information in 'Special:Version' page
* Integrates with Hierarchical Namespace Permissions extension to provide 'raw' right.

== DEPENDANCIES ==
* [[Extension:StubManager]]
* Hierarchical Namespace Permissions extension
* MW > 1.10 (or patched earlier version)

== Installation ==
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'RawRight', 
							'extensions/RawRight/RawRight.php',
							null,
							array( 'SpecialVersionExtensionTypes','RawPageViewBeforeOutput' ),
							false,	// no need for logging support
							null,	// tags
							null,	// no parser functions
							null	// no magic words
						 );
</source>

== HISTORY ==
* Removed dependency on ExtensionClass
* Added 'stub'-enabled capability (usage of StubManager)

== TODO ==
* Internationalization: add messages to cache i18n file
</wikitext>*/

	
global $wgExtensionCredits;
$wgExtensionCredits[RawRight::thisType][] = array( 
	'name'    		=> RawRight::thisName, 
	'version'		=> StubManager::getRevisionId( '$Id$' ),
	'author'		=> 'Jean-Lou Dupont', 
	'description'	=> "Status: ",
	'url'			=> StubManager::getFullUrl(__FILE__),			
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
?>