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
This extension enables the usage of 'html' tags (functionality which is controlled through the
'$wgRawHtml' global variable) within protected pages.

== Features ==
* Cascading: if the base page is allowed to use 'html' tags, then all included pages will be processed
as if they could.
* Namespace exemption: configured namespaces are exempted from 'protection' requirement
* <code><addtohead>some html code here></addtohead></code>

== Dependancy ==
* ExtensionClass extension

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/SecureHTML/SecureHTML.php');
</source>

== History ==
* added namespace exemption functionality i.e. namespaces where article do not need to be
protected in order to use 'html' tags
** use <code>SecureHTMLclass::enableExemptNamespaces = false; </code> to turn off
** use <code>SecureHTMLclass::exemptNamespaces[] = NS_XYZ; </code> to add namespaces
* enhanced with functionality to 'add' content to the document's 'head' section

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