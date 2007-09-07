<?php
/*<!--<wikitext>-->
{{Extension
|name        = SpecialPagesChangeLocation
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/SpecialPagesChangeLocation/ SVN]
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
This extension enables changing the page which lists the Special Pages, 
the default page being [[Special:Specialpages]].

== Features ==
* Provides a message in [[Special:Version]] if the new page location does not exist
* Enables locating the SpecialPages list in any namespace
* Provides localization of messages
* Changes the navigation uri link only if the new page location exists

== Usage ==
Edit the page elected to contain the list of Special Pages.
Ideally, this page should be protected.
If one wishes to have dynamic content included in th new 'SpecialPages'
(e.g. the list of special pages created by extensions)
then one must use parser functions available from [[Extension:PageFunctions]] as example.

== Installation ==
* Copy the extension's files from the SVN repository using the link provided
in the extension directory (e.g. /extensions/SpecialPagesChangeLocation)
* Edit <code>LocalSettings.php</code>:
<source lang=php>
 require('extensions/SpecialPagesChangeLocation/SpecialPagesChangeLocation.php');
 // e.g. MediaWiki:SpecialPages
 SpecialPagesChangeLocation::setPage( 'pagenamewheretofindthenewspecialpageslist' );
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/
$wgExtensionCredits['other'][] = array( 
	'name'    => 'SpecialPagesChangeLocation',
	'version' => '$Id$',
	'author'  => 'Jean-Lou Dupont',
	'url'		=> 'http://www.mediawiki.org/wiki/Extension:SpecialPagesChangeLocation',
	'description' => "Enables changing the location of the page which lists the Special Pages.", 
);

$wgExtensionFunctions[] = 
	create_function('','return SpecialPagesChangeLocation::setup();' );

require('SpecialPagesChangeLocation.i18n.php');

class SpecialPagesChangeLocation
{
	const thisType = 'other';
	const thisName = 'SpecialPagesChangeLocation';
	
	// defaults to the ... default (!)
	static $page = 'Special:Specialpages';
	static $doHook = false;
	static $msg = array();
	
	public static function setPage( $page = null )
	{
		if ($page === null)
			return;
		
		self::$page = $page;
		self::$doHook = true;		
	}
	public function setup()
	{
		global $wgMessageCache;
		foreach( self::$msg as $key => $value )
			$wgMessageCache->addMessages( self::$msg[$key], $key );		

		global $wgHooks;		
		$wgHooks['SpecialVersionExtensionTypes'][] = 
			'SpecialPagesChangeLocation::hSpecialVersionExtensionTypes';
		
		// do substitution only if a new page is set.
		if (!self::$doHook)
			return;

		// If the default is changed & the page exists THEN
		// hook up the appropriate vector so we can substitute
		if (!self::checkpage())
			return;
			
		$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 
			'SpecialPagesChangeLocation::hSkinTemplateOutputPageBeforeExec';
	}
	public static function hSkinTemplateOutputPageBeforeExec( &$skin, &$tpl )
	{
		// get the current nav url list
		// There is no 'get' method so we have to hack...
		$urls = $tpl->data['nav_urls'];
		
		// build the URI
		$title = Title::newFromText( self::$page );
		$href = $title->getLocalURL();
		
		// substitute
		$urls['specialpages'] = array( 'href' => $href );
		$tpl->set('nav_urls', $urls);
		
		// play nice
		return true;
	}
	/**
		Help the user by providing messages in the [[Special:Version]] page.
	 */
	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	{
		global $wgExtensionCredits;

		$result = null;
		if (!self::checkPage( $title ))
			$result = wfMsgForContent('specialpageschangelocation-page-not-exist', self::$page );
			
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if (isset($el['name']))		
				if ($el['name']==self::thisName)
					$el['description'].=$result;
				
		return true; // continue hook-chain.
	}

	private static function checkPage()
	{
		$title = Title::newFromText( self::$page );
		return $title->exists();		
	}
}

//</source>
