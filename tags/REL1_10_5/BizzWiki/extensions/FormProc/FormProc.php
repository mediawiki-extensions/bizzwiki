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
* Follows 'redirects'
* Executes PHP code stored in a standard Mediawiki page
* Supports code extraction when enclosed in 'PHP' tags
* Supports the definition of a class in the processor page ( $page.'Class' )
** If a method 'submit' is present in the said class, it will be called upon formsubmit action (see example)

== Example ==
=== Form Processing Page 'MyFormProc' ===
<php>
  class MyFormProcClass
  {
  	 function submit() {}
  }
</php>

== Dependancy ==
* ExtensionClass extension

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/FormProc/FormProc.php');
</source>

== History ==
- added functionality to define a class for handling form processing

== Code ==
</wikitext>*/

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: FormProc extension will not work!';	
else
{
	$wgAutoloadClasses['FormProcBaseClass'] = dirname(__FILE__) . "/FormProcBaseClass.php" ;
	require( "FormProcClass.php" );
	FormProcClass::singleton();
}
?>