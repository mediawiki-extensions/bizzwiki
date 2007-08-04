<?php
/*<wikitext>
{{extension:
|RegexTools.php
|$Id$
|Jean-Lou Dupont
}}
 
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
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/RegexTools/RegexTools.php');
</source>

== History ==

== Code ==
</wikitext>*/


global $wgExtensionCredits;
$wgExtensionCredits[RegexToolsClass::thisType][] = array( 
	'name'        => RegexToolsClass::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => "Provides 'magic words' performing regular expression pattern ( aka 'regex' ) matching.",
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);

class RegexToolsClass
{
	// constants.
	const thisName = 'RegexToolsClass';
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

		$m = preg_match( $pms, $input );
		if (($m !== false) && ($m>0))
			return true;
			
		return false;
	}
} // end class declaration.
?>