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
This extension enables changing the page which lists the Special Pages.
The default page is [[Special:Specialpages]].

== Usage ==
Edit the page elected to contain the list of Special Pages.
Ideally, this page should be protected.
If one wishes to have dynamic content included in th new 'SpecialPages'
(e.g. the list of special pages created by extensions)
then one must use parser functions available from [[Extension:PageFunctions]] as example.

== Installation ==
* Copy the extension's file from the SVN repository using the link provided
in the extension directory (e.g. /extensions/SpecialPagesChangeLocation)
* Edit <code>LocalSettings.php</code>:
<source lang=php>
 require('extensions/SpecialPagesChangeLocation/SpecialPagesChangeLocation.php');
 // e.g. MediaWiki:SpecialPages
 SpecialPagesChangeLocation::setPage( 'pagenamewheretofindthenewspecialpages' );
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
	'description' => "Enables changing the location of the page which lists the Special Pages.", 
);

$wgExtensionFunctions[] = 
	create_function('','return SpecialPagesChangeLocation::setupHook();' );

class SpecialPagesChangeLocation
{
	// defaults to the ... default (!)
	static $page = 'Special:Specialpages';
	static $doHook = false;
	
	public static function setPage( $page = null )
	{
		if ($page === null)
			return;
		
		self::$page = $page;
		
		// If the default is changed, hook up
		// the appropriate vector so we can substitute
		self::$doHook = true;
	}
	public function setupHook()
	{
		if (!self::$doHook)
			return;
		
		global $wgHooks;
		$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink'][] = 
			'SpecialPagesChangeLocation::hSkinTemplateBuildNavUrlsNav_urlsAfterPermalink';
	}
	public static function hSkinTemplateBuildNavUrlsNav_urlsAfterPermalink( &$skin, &$nav_urls, &$oldid, &$revid )
	{
		$title = Title::newFromText( self::$page );
		$href = $title->getLocalURL();
		$nav_urls['specialpages'] = array( 'href' => $href );
		return true;
	}
}

//</source>
