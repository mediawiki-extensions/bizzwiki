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
* Documents all wikitext types by enclosing the said wikitext in either 'code' or 'pre' tags
* Executes the passed wikitext as per usual processing flow
* Secure: only predefined HTML documentation tags can be specified
** Currently, only the 'pre' and 'code' tags are supported

== Usage ==
Let's say one wants to document & still execute the following wikitext:
:<docproc code>{{CURRENTTIME}}</docproc>
:Here the wikitext magic word ''CURRENTTIME'' would be executed and the result would be presented next to the 'documented' 
wikitext enclosed inside a 'code' section.

== Target Application ==
The envisaged target application for this extension is to document wikitext that produces no direct user visible results.

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