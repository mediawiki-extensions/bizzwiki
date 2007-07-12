<?php
/*<wikitext>
{| border=1
| <b>File</b> || CacheTools.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension allows for disabling 'client side caching' on a per-page basis through the
magic word <nowiki>{{NOCLIENTCACHING}}</nowiki>. The 'dynamic' version of this magic word can be accessed through 
(($var|NOCLIENTCACHING$)) when the extension 'ParserPhase2' is installed.

== Dependancy ==
* ExtensionClass extension (v>=306)

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/CacheTools/CacheTools.php');
</source>

== History ==

== Code ==
</wikitext>*/
// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: CacheTools extension will not work!';	
else
{
	require( "CacheToolsClass.php" );
	CacheToolsClass::singleton();
}
?>