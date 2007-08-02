<?php
/*<wikitext>
{| border=1
| <b>File</b> || SecureProperties.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Enables getting/setting global object properties securily (operations are only allowed on protected pages).

== Usage ==
* Property 'get': <nowiki>{{#pg:global object name|property}}</nowiki>
* Property 'set': <nowiki>{{#ps:global object name|property|value}}</nowiki>
* Method call: <nowiki>{{#pf:global object name|method name}}</nowiki>
* Global variable 'get': <nowiki>{{#gg:global object name}}</nowiki>
* Global variable 'set': <nowiki>{{#gs:global object name|value}}</nowiki>

== Notes ==
Of course, those functions can be called in the context of 'ParserPhase2':
* Property 'get': <nowiki>(($#pg|global object name|property$))</nowiki>
* Property 'set': <nowiki>(($#ps|global object name|property|value$))</nowiki>
* Method call: <nowiki>(($#pf|global object name|method name$))</nowiki>
* Global variable 'get': <nowiki>(($#gg:global object name$))</nowiki>
* Global variable 'set': <nowiki>(($#gs:global object name|value$))</nowiki>

== Examples ==
Current user name: {{#pg:wgUser|mName}}

Current user id: {{#pg:wgUser|mId}}

== Features ==
* Security: the 'magic words' of the extension can only be used on protected pages
* Namespace exemption: configured namespaces are exempted from the 'protection' requirement

== Dependancy ==
* ExtensionClass extension

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/SecureProperties/SecureProperties.php');
</source>

== History ==
* added '#gg' and '#gs' magic words

== Code ==
</wikitext>*/
// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: SecureProperties extension will not work!';	
else
{
	require( "SecurePropertiesClass.php" );
	SecurePropertiesClass::singleton();
}
?>