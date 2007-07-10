<?php
/*<wikitext>
{| border=1
| <b>File</b> || ForeachFunction.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension provides 'looping' functionality (e.g. 'foreach') for iterating through arrays.

== Features ==

== Usage ==
* <code>{{#foreachx:global object name|property|pattern}}</code>
** The global object's property will be retrieved; the property should be an 'array'
* <code>{{#foreachx:global object name|method|pattern}}</code>
** The global object's method will be called: an array is expected as return value
* <code>{{#foreachx:global array variable|key|pattern}}</code>
** The global array variable will be referenced using 'key' as key
* <code>{{#foreachx:global array variable||pattern}}</code>
** The global array variable will be referenced (as a whole)

== Dependancies ==
* ExtensionClass extension (v>=306)
* ParserPhase2 extension

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/ForeachFunction/ForeachFunction.php');
</source>

== History ==

== Code ==
</wikitext>*/

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: ForeachFunction extension will not work!';	
else
{
	require( "ForeachFunctionClass.php" );
	ForeachFunctionClass::singleton();
}
?>