<?php
/*<wikitext>
{| border=1
| <b>File</b> || XYZ.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==


== Features ==


== Dependancy ==
* [[Extension:ExtensionClass|ExtensionClass]]

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/FileSystemSyntaxColoring/FileSystemSyntaxColoring.php');
</source>

== History ==

== Code ==
</wikitext>*/

class EmailLog
{
	public function __construct()
	{
	}
	
	public function hEmailUserComplete( $to, $from, $subject, $text )
	{
#		echo __METHOD__.' text: '.$text.'<br/>';
		
		return true;
	}	
}
?>