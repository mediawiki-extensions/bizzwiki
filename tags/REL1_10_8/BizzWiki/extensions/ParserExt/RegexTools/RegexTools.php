<?php
/*<wikitext>
{{extension:
|RegexTools.php
|$Id$
|Jean-Lou Dupont
}}
 
== Purpose==
Provides 'magic words' performing regular expression pattern ( aka 'regex' )matching.

== Features ==
* Magic Word '#regx' 
* Magic Word '#regx_vars'

== Usage ==
* <nowiki>{{#regx:regex pattern string|input}}</nowiki> 
** returns '1' if match found
* <nowiki>{{#regx_vars:pattern array name|input}}</nowiki> 
** returns the index in the pattern array if match found

== Dependancy ==
* [[Extension:ExtensionClass|ExtensionClass]]
* [[Extension:PageFunctions|Page Functions extension]]
** Required for 'regx_vars' magic word function

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/RegexTools/RegexTools.php');
</source>

== History ==

== Code ==
</wikitext>*/
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: RegexTools extension will not work!';	
else
{
	require( "RegexToolsClass.php" );
	RegexToolsClass::singleton();
}
?>