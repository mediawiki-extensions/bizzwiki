<?php
/*<wikitext>
{| border=1
| <b>File</b> || PermissionFunctions.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Collection of permission management functionality.

== Features ==
* Magic Word 'checkPermission' : verifies if 'user' is allowed 'right' and if *not* then the page 'Permission Error' is served.
** This function is rather useful for building 'forms'
** Rather only helpful when used in a 'ParserPhase2' context (e.g. (($#checkpermission|edit$))  )

== Usage ==

== Dependancies ==
* [[Extension:ExtensionClass|ExtensionClass]] extension (v>=306)

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/ParserExt/PermissionFunctions/PermissionFunctions.php');
</source>

== History ==

== Code ==
</wikitext>*/

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: PermissionFunctions extension will not work!';	
else
{
	require( "PermissionFunctionsClass.php" );
	PermissionFunctionsClass::singleton();
}
?>