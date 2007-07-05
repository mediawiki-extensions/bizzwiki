<?php
/*<wikitext>
{| border=1
| <b>File</b> || ParserPhase2.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension enables performing a 'second pass' through a 'parser cached' page replacing for 
'dynamic' variables. In a word, once a page is normally processed (i.e. 'first pass') Mediawiki 'fixes'
all templates & variables in a 'parser cached' page. This extension enables substituting selected 
variables upon page view whilst still preserving the valuable job performed by the parser/parser cache.

== Features ==
* Integrates with the standard Mediawiki Parser Cache
* Provides a simple 'magic word' based interface to standard Mediawiki variables
* Provides a simple 'magic word' based interface to global objects->methods

== Usage ==
(($var|variable$))
:Where 'variable' is a standard Mediawiki magic word e.g. CURRENTTIME, REVISIONID etc.

(($obj|object|method|param 1...$))
:Where 'object' is a global object
:Where 'method' is a publically available method of the above mentionned object
:Where 'param X' is a list of parameters

== Dependancy ==
* ExtensionClass extension

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/ParserPhase2/ParserPhase2.php');
</source>

== History ==
* added 'obj' capability
* fixed client side caching logic due to a bug in PHP's preg_match_all function

== Code ==
</wikitext>*/
// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: ParserPhase2 extension will not work!';	
else
{
	require( "ParserPhase2Class.php" );
	ParserPhase2Class::singleton();
}
?>