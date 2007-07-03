<?php
/*<wikitext>
{| border=1
| <b>File</b> || AutoLanguage.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension provides viewing pages in the language specified by the user's preferences automatically.

== Features ==


== Dependancy ==
* ExtensionClass extension

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<geshi lang=php>
require('extensions/ExtensionClass.php');
require('extensions/AutoLanguage/AutoLanguage.php');
</geshi>

== History ==

== Code ==
</wikitext>*/

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: AutoLanguage extension will not work!';	
else
{
	require( "AutoLanguageClass.php" );
	AutoLanguageClass::singleton();
}
?>