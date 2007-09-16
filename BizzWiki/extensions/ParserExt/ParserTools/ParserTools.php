<?php
/*<!--<wikitext>-->
{{Extension
|name        = ParserTools
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ParserExt/ParserTools/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose==
This extension allows for disabling 'parser caching' on a per-page basis through the
tag <nowiki><noparsercaching/></nowiki>.

== Dependancy ==
* [[Extension:StubManager]] extension

== Installation ==
To install independantly from BizzWiki:
* Download & install [[Extension:StubManager]] extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ParserTools/ParserTools.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[ParserTools::thisType][] = array( 
	'name'        => ParserTools::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Parser cache enabling/disabling through <noparsercaching/> tag',
	'url' 		=> 'http://mediawiki.org/wiki/Extension:ParserTools',			
);

class ParserTools
{
	// constants.
	const thisName = 'ParserTools';
	const thisType = 'other';
	  
	function __construct(  ) {	}

	public function tag_noparsercaching( &$text, &$params, &$parser )
	{ $parser->disableCache(); }

} // end class
//</source>