<?php
/*<wikitext>
{| border=1
| <b>File</b> || FormProc.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension offers the ability to process posted pages/forms through the 'action=formsubmit' action. 
The processing code resides in the database. The code can be 'syntax highlighted' through a 
<nowiki><php></nowiki> tag.

== Features ==
* Handles 'action=formsubmit' action
* Executes PHP code stored in a standard Mediawiki page
* Supports code extraction when enclosed in 'PHP' tags

== Dependancy ==
* ExtensionClass extension

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<geshi lang=php>
require('extensions/ExtensionClass.php');
require('extensions/FormProc/FormProc.php');
</geshi>

== History ==

== Code ==
</wikitext>*/

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: FormProc extension will not work!';	
else
{
	require( "FormProcClass.php" );
	FormProcClass::singleton();
}
?>