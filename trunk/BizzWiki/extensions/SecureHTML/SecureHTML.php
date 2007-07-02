<?php
/*<wikitext>
{| border=1
| <b>File</b> || SecureHTML.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==


== Features ==


== Dependancy ==


== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<geshi lang=php>
require('extensions/ExtensionClass.php');
require('extensions/SecureHTML/SecureHTML.php');
</geshi>

== History ==

== Code ==
</wikitext>*/

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: SecureHTML extension will not work!';	
else
{
	require( "SecureHTMLclass.php" );
	SecureHTMLclass::singleton();
}
?>