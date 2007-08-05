<?php
/*(($disable$))<wikitext>
{{Extension
|name        = ParserPhase2
|status      = stable
|type        = Parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ParserPhase2/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
== Purpose==
This extension enables performing a 'second pass' through a 'parser cached' page replacing for 
'dynamic' variables. In a word, once a page is normally processed (i.e. 'first pass') Mediawiki 'fixes'
all templates & variables in a 'parser cached' page. This extension enables substituting selected 
variables upon page view whilst still preserving the valuable job performed by the parser/parser cache.

Additionally, the extension enables the execution of 'parser functions' and 'magic words' *after* the
page's 'tidy' process is executed. This functionality is referred to as 'parser after tidy'. 
This capability allows for the inclusion of text that would otherwise upset MediaWiki's parser 
e.g. execution of a parser functions that replaces text in an <html> tagged section.

Finally, the extension enables the execution of 'parser functions' and 'magic words' *before* the
page's 'strip' process is executed i.e. before the MediaWiki begins parsing the page. 
This functionality is referred to as 'parser before strip'. 

== Theory of operation ==
In the standard MW processing flow, when a page is viewed it is retrieved (either from the cache or 'raw' from the database) and sent to the 'output page' object. What this extension does is intercept the flow process through the 'OutputPageBeforeHTML' hook and:
* Extracts the <code>(($ magic word| ... $))</code> tags (and other supported invocation formats)
* Looks for 'magic word' in the dictionary and retrieve the value if found
* Looks for 'magic word' in the 'parser function' dictionary and execute the function if found
This same process is performed for both 'parser phase 2' and 'parser after tidy' functionalities.
See [[Extension:ParserPhase2/Flow Summary]] for more details.

== Features ==
* Integrates with the standard Mediawiki Parser Cache
* Provides a simple 'magic word' based interface to standard Mediawiki variables & parser functions
* Handles two invocation forms for the 'parser phase 2' functionality:
** (($...$))
** (( ... ))
* Does not handle 'nested' magic words e.g. (($ magic word1 | (($magic word 2$)) $))
* Handles one invocation for the 'parser after tidy' functionality:
** ((% ... %))
* Handles one invocation for the 'parser before strip' functionality:
** ((@ ... @))

== Usage ==
=== ParserPhase2 functionality ===
<code>(($magic word|...parameters...$))  or  (( ))</code>
:Where 'variable' is a standard Mediawiki magic word e.g. CURRENTTIME, REVISIONID etc.
=== Parser After Tidy functionality ===
<code>((%magic word|...parameters...%))</code>
=== Parser Before Strip functionality ===
<code>((@magic word|...parameters...@))</code>

== Dependancy ==
* [[Extension:StubManager]]

== Installation ==
To install outside the BizzWiki platform:
* Download [[Extension:StubManager]]
** Place 'StubManager.php' file in '/extensions' directory
* Download the extension files from the SVN repository
** Place the files in '/extensions/ParserPhase2' directory
* Perform the following changes to 'LocalSettings.php':
<source lang=php>
require('/extensions/StubManager.php');

StubManager::createStub(	'ParserPhase2Class', 
							'extensions/ParserPhase2/ParserPhase2.php',
							null,
							array( 'OutputPageBeforeHTML','ParserAfterTidy','ParserBeforeStrip' ),
							false,	// no need for logging support
							null,	// tags
							null,	// no parser functions
							null	// no magic words
						 );
</source>

== History ==
* fixed client side caching logic due to a bug in PHP's preg_match_all function
* fixed issue with $wgParser not having a valid 'mTitle' property set
* added 'disable' command
* Removed dependency on 'ExtensionClass'
* Added 'stub' capability
* Added 'EndParserPhase2' hook
* Added pattern: ((magic word|... )) which more closely maps to standard MW parser function calling
** DO NOT MIX PATTERNS ON THE SAME PAGE i.e. no (($...$)) mixing up with ((...))
* Added functionality to execute parser functions/magic words just after the 'tidy' process
* Added functionality to execute parser functions/magic words just BEFORE the 'stip' process i.e. before the parser really begins.

== TODO ==
* possibly fix to allow mixing up (($..$)) and ((..)) patterns on the same page (TBD)

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki platform]].

== Code ==
</wikitext>*/
global $wgExtensionCredits;
$wgExtensionCredits[ParserPhase2Class::thisType][] = array( 
	'name'        => ParserPhase2Class::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Enables performing a `second pass` parsing over an already cached page for replacing dynamic variables',
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);

class ParserPhase2Class
{
	// constants.
	const thisName = 'ParserPhase2Class';
	const thisType = 'other';
	
	// (($ ... $))
	const pattern1a = '/\(\(\$(.*)\$\)\)/siU';
	// ((  ...  ))
	const pattern1b = '/\(\((.*)\)\)/siU';	// tracks more closely MW parser functions style
	//  ((% ... %))
	const pattern2  = '/\(\(\%(.*)\%\)\)/siU';
	//  ((@ ... @))
	const pattern3  = '/\(\(\@(.*)\@\)\)/siU';
	
	function __construct( ) {}

	/**
		The parser functions enclosed in ((@ ... @)) are executed
		before the MediaWiki starts parsing the wikitext.
	 */
	public function hParserBeforeStrip( &$parser, &$text, &$mStripState )
	{
		$m = $this->getList3( $text );
		if ( empty( $m ) ) return true; // nothing to do

		$this->executeList( $m, $text );

		return true; // be nice with other extensions.
	}

	/**
		'Parser After Tidy' functionality:
		
		This function picks up the patterns ((% ... %)) and executes
		the corresponding parser function/magic word *AFTER* the 'tidy' processed
		is finished. This way, it is possible to include calls to function that would
		generate otherwise unallowed wikitext for the parser.
	 */
	public function hParserAfterTidy( &$parser, &$text )
	{
		$m = $this->getList2( $text );
		if ( empty( $m ) ) return true; // nothing to do

		$this->executeList( $m, $text );

		return true; // be nice with other extensions.
	}

	/**
		ParserPhase2 core function: gets a list of replacement to be done,
		executes the referenced functions and replaces the text in of the page. 
	 */
	function hOutputPageBeforeHTML( &$op, &$text )
	{
		$m = $this->getList1( $text );
		if ( empty( $m ) ) return true; // nothing to do

		$found = $this->executeList( $m, $text );

		// we found some dynamic variables, disable client side caching.
		// parser caching is not affected.
		if ( $found )
		{
			global $wgOut;
			$wgOut->enableClientCache( false );
		}

		wfRunHooks('EndParserPhase2', array( &$op, &$text ) );

		return true; // be nice with other extensions.
	}
	/**
		This function handles all the hard work. It relies on MediaWiki's
		parser to reach the registered 'parser functions' and 'magic words'.
		
		It also implements the special keyword (($disable$)) which stops all
		'parserphase2' and 'parser after tidy' functionality. This is especially useful
		in case of documentation pages.
	 */
	private function executeList( &$liste, &$text )
	{
		// PHP sometimes messes up in preg_match_all returning an empty array
		// we need to guard against this or else client side caching always get thrashed!
		$found = false; 
		
		foreach( $liste[1] as $index => $str)
		{
			// (($magic word|... parameters...$))
			$params = explode('|', $str);
			$action = array_shift( $params );

			// if we are asked to disable, stop processing.
			if ('disable'==strtolower($action))
				break;

			global $wgParser, $wgTitle, $wgContLang;

			// check if the 'mTitle' property is set
			if (!is_object($wgParser->mTitle))
				$wgParser->mTitle = $wgTitle;

			$varname = $wgContLang->lc($action);
			$idl = MagicWord::getVariableIDs();
							
			// First, look for $action in 'parser variables'
			if (in_array( $varname, $idl ))
			{
				$rl[$index] = $this->getValue( $varname );
				$found = true;
				continue;
			}

			// If not found, check for $action in 'parser functions.
			$function = null;
	
			if ( isset( $wgParser->mFunctionSynonyms[1][$action] ) ) 
				$function = $wgParser->mFunctionSynonyms[1][$action];
			else 
			{
				# Case insensitive functions
				$function = strtolower( $action );
				if ( isset( $wgParser->mFunctionSynonyms[0][$action] ) ) 
					$function = $wgParser->mFunctionSynonyms[0][$action];
				else
					$function = false;
			}
		
			if ( $function ) 
			{
				$found = true;
				
				$funcArgs = array_map( 'trim', $params );
				$funcArgs = array_merge( array( &$wgParser) , $funcArgs );
				$result = call_user_func_array( $wgParser->mFunctionHooks[$function], $funcArgs );
	
				if ( is_array( $result ) ) 
				{
					if ( isset( $result[0] ) ) 
						$rl[$index] = $result[0];
					// Extract flags into the local scope
					// This allows callers to set flags such as nowiki, noparse, found, etc.
					// extract( $result );
				} else 
					$rl[ $index ] = $result;
			}

		} // end foreach

		$this->replaceList( $text, $liste, $rl );

		return $found;
	}

	private function getList1 ( &$text )
	{
		// find the (($...$)) matches
		$r1 = preg_match_all(self::pattern1a, $text, $m1 );
		
		// if we found some, return.
		if ( ($r1 !== false) && ( $r1!==0 ) )
			return $m1;
		
		// find the ((...)) matches	
		$r2 = preg_match_all(self::pattern1b, $text, $m2 );	
		
		return $m2;
	}
	/**
		Parser After Tidy related.
	 */
	private function getList2 ( &$text )
	{
		// find the ((%...%)) matches
		$r = preg_match_all(self::pattern2, $text, $m );
		
		// if we found some, return.
		if ( ($r !== false) && ( $r!==0 ) )
			return $m;
		
		return null;
	}
	/**
		Parser Before Strip related.
	 */
	private function getList3 ( &$text )
	{
		// find the ((%...%)) matches
		$r = preg_match_all(self::pattern3, $text, $m );
		
		// if we found some, return.
		if ( ($r !== false) && ( $r!==0 ) )
			return $m;
		
		return null;
	}
	/**
		Gets a value associated with a 'magic word'.
	 */
	private function getValue( $varid )
	{
		// ask our friendly MW parser for its help.
		global $wgParser;
		$value = $wgParser->getVariableValue( $varid );
		
		return $value;
	}
	/**
		Go through the current page and replaces
		the all the calls with the return values.
	 */
	private function replaceList( &$text, &$source, &$target )
	{
		if (!empty( $source[0] ))
			foreach( $source[0] as $index => $marker )
				$text = str_replace( $marker, $target[$index], $text );	
	}

} // end class

?>