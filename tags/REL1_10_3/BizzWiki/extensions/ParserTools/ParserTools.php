<?php
/*<wikitext>
{| border=1
| <b>File</b> || ParserTools.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension allows for disabling 'parser caching' on a per-page basis through the
tag <nowiki><noparsercaching/></nowiki>.

== Dependancy ==
* ExtensionClass extension

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<geshi lang=php>
require('extensions/ExtensionClass.php');
require('extensions/ParserTools/ParserTools.php');
</geshi>

== History ==

== Code ==
</wikitext>*/
// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: ParserTools extension will not work!';	
else
{
	require( "ParserToolsClass.php" );
	ParserToolsClass::singleton();
}
?>