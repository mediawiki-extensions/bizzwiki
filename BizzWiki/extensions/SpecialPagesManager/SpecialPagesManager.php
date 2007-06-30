<?php
/*<wikitext>
{| border=1
| <b>File</b> || SpecialPagesManager.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Gives the ability to a sysop to enhance a Mediawiki installation with custom 'special pages'
managed directly from the database (instead of PHP files).

== Features ==
* Default to 'Bizzwiki:Special Pages' page
* Can be changed through using
<geshi lang=php>
SpecialPagesManager->singleton()->setSpecialPage('page name');
</geshi>

== Dependancy ==
* ExtensionClass extension

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<geshi lang=php>
require('extensions/ExtensionClass.php');
require('extensions/SpecialPagesManager/SpecialPagesManager.php');
</geshi>

== History ==

== Code ==
</wikitext>*/

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'SpecialPagesManager extension: ExtensionClass missing.';	
else
{
	require('SpecialPagesManagerClass.php');
	SpecialPagesManagerClass::singleton();
}
?>