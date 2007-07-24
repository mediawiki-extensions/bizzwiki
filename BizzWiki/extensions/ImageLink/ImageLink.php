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
* [[Extension:ExtensionClass|ExtensionClass]]

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/ImageLink/ImageLink.php');
</source>

== Compatibility ==
Tested Compatibility: MW 1.8.2, 1.9.3, 1.10

== History ==

== Code ==
</wikitext>*/

$wgExtensionCredits['other'][] = array( 
	'name'    => 'ImageLink',
	'version' => '$Id$',
	'author'  => 'Jean-Lou Dupont', 
);

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: ImageLink extension will not work!';	
else
{
	require(dirname( __FILE__ ) . '/ImageLinkClass.php');
	ImageLinkClass::singleton();
}
?>