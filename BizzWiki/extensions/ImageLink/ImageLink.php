<?php
/*<!--<wikitext>-->
{{Extension
|name        = ImageLink
|status      = stable
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ImageLink/ SVN]
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
Provides a clickable image link using an image stored in the Image namespace and an article title (which may or may not existin the database).

== Features ==

== Usage ==
* <nowiki>{{#imagelink: image page name | page name |alternate text | width | height | border }}</nowiki>
See [http://www.w3schools.com/tags/tag_img.asp W3Schools on IMG tag] for more details.

Example:
* <nowiki>{{#imagelink: New Clock.gif | Admin:Show Time | Current Time | 32 | 32 | 2 }}</nowiki>

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/ImageLink/ImageLink_stub.php');
</source>

== Compatibility ==
Tested Compatibility: MW 1.8.2, 1.9.3, 1.10

== History ==
* Removed dependency on ExtensionClass
* Added 'stubbing' capability though StubManager
* Added non-existing link (i.e. red link) when image page does not exist

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[ImageLink::thisType][] = array( 
	'name'        	=> ImageLink::thisName, 
	'version'     	=> StubManager::getRevisionId( '$Id$' ),
	'author'      	=> 'Jean-Lou Dupont', 
	'description' 	=> 'Provides a clickable image link',
	'url' 			=> StubManager::getFullUrl(__FILE__),			
);

class ImageLink
{
	// constants.
	const thisName = 'ImageLink';
	const thisType = 'other';
	
	var $links;
	
	public function __construct() {}
	
	public function mg_imagelink( &$parser, $img, $page,  							// mandatory parameters  
								$alt=null, $width=null, $height=null, $border=null )// optional parameters
	/**
	 *  $img  = image reference i.e. a valid image name e.g. "New Clock.gif" 
	 *  $page = page reference i.e. a valid page name e.g. "Admin:Show Time"
	 *
	 * {{#imagelink:New Clock.gif|Admin:Show Time|alternate text}}
	 */
	{
		$image = Image::newFromName( $img );
		if (!$image->exists()) return '[[Image:'.$img.']]';
		
		if (empty($page)) return;
			
		$title = Title::newFromText( $page );
		if (!is_object($title)) return;
		
		$iURL = $image->getURL();
		
		// distinguish between local and interwiki URI
		if ($title->isLocal())
		{
			$tURL = $title->getLocalUrl();
			$aClass=''; 			
		}
		else
		{
			$tURL = $title->getFullURL();
			$aClass = 'class="extiw"';
		}		
		// Optional parameters
		if ($alt    !== null)	$alt    = "alt='${alt}'"; 		else $alt='';
		if ($width  !== null)	$width  = "width='${width}'"; 	else $width='';
		if ($height !== null)	$height = "height='${height}'";	else $height='';
		if ($border !== null)	$border = "border='${border}'";	else $border='';

		$t = "_imagelink_".date('Ymd').count($this->links)."_/imagelink_";
				
		// let's put an easy marker that we can 'safely' find once we need to render the HTML
		$this->links[] = "<a ".$aClass." href='${tURL}'><img src='${iURL}' $alt $width $height $border /></a>";

		return $t;
	}

	/**
	 	This function is called just before the HTML is rendered to the client browser.
	 */
	public function hParserAfterTidy( &$parser, &$text )
	{
		// Some substitution to do?
		if (empty($this->links)) return true;

		foreach($this->links as $index => $link)
		{
			$p = "/_imagelink_".date('Ymd').$index."_\/imagelink_/si";
			$text = preg_replace( $p, $link, $text );
		}
	
		return true;
	}
} // end class definition.
//</source>