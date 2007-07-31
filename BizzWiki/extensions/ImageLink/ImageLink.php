<?php
/*<wikitext>
{{extension:
|ImageLink.php
|$Id$
|Jean-Lou Dupont
}}
 
== Purpose==
Provides a clickable image link using an image stored in the Image namespace and an article title (which may or may not existin the database).

== Features ==

== Usage ==
* <nowiki>{{#imagelink:New Clock.gif|Admin:Show Time|alternate text | width | height | border }}</nowiki>

== Dependancy ==
* [[Extension:StubManager]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'ImageLinkClass', 
							'extensions/ImageLink/ImageLink.php',
							null,					// i18n file			
							array('ParserAfterTidy'),	// hooks
							false, 					// no need for logging support
							null,					// tags
							array('imagelink'),	// parser Functions
							null
						 );

</source>

== Compatibility ==
Tested Compatibility: MW 1.8.2, 1.9.3, 1.10

== History ==
* Removed dependency on ExtensionClass
* Added 'stubbing' capability though StubManager

== Code ==
</wikitext>*/

$wgExtensionCredits[ImageLinkClass::thisType][] = array( 
	'name'        	=> ImageLinkClass::thisName, 
	'version'     	=> StubManager::getRevisionId( '$Id$' ),
	'author'      	=> 'Jean-Lou Dupont', 
	'description' 	=> 'Provides a clickable image link',
	'url' 			=> StubManager::getFullUrl(__FILE__),			
);

class ImageLinkClass
{
	// constants.
	const thisName = 'ImageLinkClass';
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
		if (!$image->exists()) return;
		
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
?>