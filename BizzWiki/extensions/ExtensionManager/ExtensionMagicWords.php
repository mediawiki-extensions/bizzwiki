<?php
/*<!--<wikitext>-->
{{Extension
|name        = ExtensionManager
|status      = beta
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ExtensionManager/ SVN]
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
Provides a means of easily installing 'extensions' to MediaWiki.

== Features ==
* Definition of 'repositories'

== Theory of Operation ==

== Usage ==
Use the parser function '#extension' in the NS_EXTENSION namespace.
<nowiki>{{#extension: repo=REPOSITORY TYPE | dir=DIRECTORY [| name=NAME ] }}</nowiki>

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/ExtensionManager/ExtensionManager_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

class ExtensionMagicWords
{
	static $wordsDynamic = array(
	'' => '',
	'' => '',	
	#	'' => '',
	);

	static $wordsStatic = array(
	'/\$id/siU' => '',
	#'' => '',	
	#	'' => '',
	);

	public static function doReplaceStatic( &$text )	
	{	self::doReplace( $text, self::$wordsStatic );	}	
	
	public static function doReplaceDynamic( &$text )	
	{	self::doReplace( $text, self::$wordsDynamic );	}	
	
	protected static function doReplace( &$text, &$liste )
	{
		foreach( $liste as $pattern => $method )
		{
			$r = preg_match_all( $pattern, $text, $matches );
			if ( ($r===false) || ($r===0) )
				continue;
				
			self::replaceMatches( $text, $matches, $pattern, $method );
		}
	}
	public static function replaceMatches( &$text, &$matches, &$pattern, &$method )
	{
		if (!empty( $matches ))
			foreach( $matches[1] as $match )
				
	}
	
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	
	protected static function word_( &$text )
	{
		
	}
	
} // end class

//</source>