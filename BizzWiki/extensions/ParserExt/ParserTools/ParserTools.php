<?php
/*<wikitext>
{| border=1
| <b>File</b> || ParserTools.php
|-
| <b>Revision</b> || $Id: ParserTools.php 328 2007-07-09 14:21:31Z jeanlou.dupont $
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension allows for disabling 'parser caching' on a per-page basis through the
tag <nowiki><noparsercaching/></nowiki>.

== Dependancy ==
* ExtensionClass extension

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<geshi lang=php>
require('extensions/StubManager.php');
require('extensions/ParserTools/ParserTools.php');
</geshi>

== History ==

== Code ==
</wikitext>*/

global $wgExtensionCredits;
$wgExtensionCredits[ParserToolsClass::thisType][] = array( 
	'name'        => ParserToolsClass::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Parser cache enabling/disabling through <noparsercaching/> tag',
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);

class ParserToolsClass
{
	// constants.
	const thisName = 'ParserTools';
	const thisType = 'other';
	  
	function __construct(  ) {	}

	public function tag_noparsercaching( &$text, &$params, &$parser )
	{ $parser->disableCache(); }

} // end class
?>