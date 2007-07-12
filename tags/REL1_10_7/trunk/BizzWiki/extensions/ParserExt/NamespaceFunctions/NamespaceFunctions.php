<?php
/*<wikitext>
{| border=1
| <b>File</b> || NamespaceFunctions.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Collection of namespace management functionality.

== Features ==

== Usage ==

== Dependancies ==
* [[Extension:ExtensionClass|ExtensionClass]] extension (v>=306)

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/ParserExt/NamespaceFunctions/NamespaceFunctions.php');
</source>

== History ==

== Code ==
</wikitext>*/

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: NamespaceFunctions extension will not work!';	
else
{
	require( "NamespaceFunctionsClass.php" );
	$bwNamespaceFunctions = NamespaceFunctionsClass::singleton();
}
?>