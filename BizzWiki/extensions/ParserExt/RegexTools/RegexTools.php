<?php
/*<!--<wikitext>-->
{{Extension
|name        = RegexTools
|status      = stable
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ParserExt/RegexTools/ SVN]
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
Provides 'magic words' performing regular expression pattern ( aka 'regex' )matching.

== Features ==
* Magic Word '#regx' 
* Magic Word '#regx_vars'

== Usage ==
* <nowiki>{{#regx:regex pattern string|input}}</nowiki> 
** returns '1' if match found
* <nowiki>{{#regx_vars:pattern array name|input}}</nowiki> 
** returns the index in the pattern array if match found

== Dependancy ==
* [[Extension:StubManager|StubManager]]
* [[Extension:PageFunctions|Page Functions extension]]
** Required for 'regx_vars' magic word function

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/RegexTools/RegexTools_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/
$wgExtensionCredits[RegexTools::thisType][] = array( 
	'name'        => RegexTools::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => "Provides 'magic words' performing regular expression pattern ( aka 'regex' ) matching.",
	'url' 		=> 'http://mediawiki.org/wiki/Extension:RegexTools',			
);

class RegexTools
{
	// constants.
	const thisName = 'RegexTools';
	const thisType = 'other';
	  
	function __construct( ) {}

	/**
		Returns index in pattern array of *first* pattern match.
		
		@param: patternArrayName:	variable name (found in PageFunctions extension) 
		@param: input:				input string to regex match
	 */
	public function mg_regx_vars( &$parser, &$patternArrayName, &$input )
	{
		// the worst that can happen is that no valid return values are received.
		wfRunHooks('PageVarGet', array( &$patternArrayName, &$parray ) );
		$mIndex = self::regexMatchArray( $parray, $input );	
		
		return $mIndex;
	}
	public function mg_regx( &$parser, &$patternString, &$input )
	{
		return self::regexMatch( $patternString, $input );
	}
	
/*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
	
	public static function regexMatchArray( &$patternArray, &$input )
	{
		if (!empty( $patternArray ))
			foreach( $patternArray as $index => &$p )
				if ( self::regexMatch( $p, $input ) )
					return $index;
		return null;
	}
	public static function regexMatch( &$p, &$input )
	{
		$pms= '/'.$p.'/siU';

		#echo ' $pms:'.$pms.' $input:'.$input."\n";

		$m = preg_match( $pms, $input );
		if (($m !== false) && ($m>0))
			return true;
			
		return false;
	}
} // end class declaration.
//</source>