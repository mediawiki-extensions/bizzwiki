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
* Based language is assumed to be 'en'
* Base 'page' (i.e. no /$lang sub-page) is assumed to be in 'en' language

== Usage ==
* Visit 'page' and redirection to 'page/$lang' will be effected IF $lang != 'en'
* 'page/en' can be visited as per normal
* Visit 'page/' to show 'page' without any redirection based on this extension
  (i.e. same as visiting 'page' )

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

== Notes ==
This extension is heavily based on the 'Polyglot' extension found on Mediawiki.org.

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