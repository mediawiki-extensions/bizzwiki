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