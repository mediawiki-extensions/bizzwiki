<?php
/*<wikitext>
{{extension:
|DocProc.php
|$Id$
|Jean-Lou Dupont
}}
 
== Purpose==
Serves to document markup/magic words whilst still executing them as per required.

== Features ==
* Secure: only predefined HTML documentation tags can be specified
** Currently, only the 'pre' and 'code' tags are supported

== Usage ==
Let's say one wants to document & still execute the following wikitext:
:<docproc code>{{CURRENTTIME}}</docproc>

== Dependancy ==
* [[Extension:ExtensionClass|ExtensionClass]]

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/DocProc/DocProc.php');
</source>

== History ==

== Code ==
</wikitext>*/
// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: DocProc extension will not work!';	
else
{
	require( "DocProcClass.php" );
	DocProcClass::singleton();
}
?>