<?php
/*<!--<wikitext>-->
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
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->(($disable$)) ((@disable@)) ((%disable%))
== Purpose==
This extension enables performing a 'second pass' through a 'parser cached' page replacing for 
'dynamic' variables. In a word, once a page is normally processed (i.e. 'first pass') Mediawiki 'fixes'
all templates & variables in a 'parser cached' page. This extension enables substituting selected 
variables upon page view whilst still preserving the valuable job performed by the parser/parser cache.

Additionally, the extension enables the execution of 'parser functions' and 'magic words' *after* the
page's 'tidy' process is executed. This functionality is referred to as 'parser after tidy'. 
This capability allows for the inclusion of text that would otherwise upset MediaWiki's parser 
e.g. execution of a parser functions that replaces text in an 'html' tagged section.

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
* Enable/disable keywords for 'sectional execution' support
** By default, replacement is 'enabled' until a 'disable' magic word is encountered
** Execution is stopped (i.e. no replacement occurs) until an 'enable' magic word is next encountered
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

=== Nesting ===
Recursive invocation is supported; example:
* <code>(($#f1 | (($#f2$)) | (($#f3$)) $))</code>

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/ParserPhase2/ParserPhase2_stub.php');
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
* Added functionality to execute parser functions/magic words just BEFORE the 'strip' process i.e. before the parser really begins.
* Added 'enable' magic word
* Added support for 'sectional execution' i.e. replacement between 'enable' and 'disable' magic words
* Added 'recursive' (aka 'nesting') processing functionality

== TODO ==
* possibly fix to allow mixing up (($..$)) and ((..)) patterns on the same page (TBD)

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[ParserPhase2::thisType][] = array( 
	'name'        => ParserPhase2::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => "Enables performing a 'second pass' parsing over an already cached page for replacing dynamic variables",
	'url' 		=> 'http://mediawiki.org/wiki/Extension:ParserPhase2',			
);

class ParserPhase2
{
	// constants.
	const thisName = 'ParserPhase2';
	const thisType = 'other';
	
	const depthMax = 15;
	
	// new patterns
	static $newPatterns = array(
	'BeforeOutput' => array(
				'(($' => "\xfe",
				'$))' => "\xff",
				'((' => "\xfe",
				'))' => "\xff",
				),
	'AfterTidy' => array(
				'((%' => "\xfe",
				'%))' => "\xff",			
				),
	'BeforeStrip' => array(
				'((@' => "\xfe",
				'((@' => "\xff",			
				)
	);
	
	static $masterPattern = "/\xfe(((?>[^\xfe\xff]+)|(?R))*)\xff/si";
	
	function __construct( ) {}

	/**
		The parser functions enclosed in ((@ ... @)) are executed
		before the MediaWiki starts parsing the wiki-text.
	 */
	public function hParserBeforeStrip( &$parser, &$text, &$mStripState )
	{
		$this->execute( &$text, 'BeforeStrip', &$found );
				
		return true; // be nice with other extensions.
	}

	/**
		'Parser After Tidy' functionality:
		
		This function picks up the patterns ((% ... %)) and executes
		the corresponding parser function/magic word *AFTER* the 'tidy' processed
		is finished. This way, it is possible to include calls to function that would
		generate otherwise unallowed wiki-text for the parser.
	 */
	public function hParserAfterTidy( &$parser, &$text )
	{
		$this->execute( &$text, 'AfterTidy', &$found );

		return true; // be nice with other extensions.
	}

	/**
		ParserPhase2 core function: gets a list of replacement to be done,
		executes the referenced functions and replaces the text in of the page. 
	 */
	function hOutputPageBeforeHTML( &$op, &$text )
	{
		$this->execute( &$text, 'BeforeOutput', &$found );
			
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
		Multiplex method.
	 */
	private function execute( &$text, $phase, &$found )
	{
		// assume worst case.
		$found = false;
		
		$this->prepareText( $text, $phase );

		$this->recursiveReplace( $text, $found );		
	}
	/**
		This method prepares the target text for pattern matching.
		It replaces the 'human readable' open/close delimiters
		with more easily processable ones.
	 */
	private function prepareText( &$text, $phase )
	{
		$patterns = self::$newPatterns[ $phase ];

		foreach( $patterns as $pattern => $replacement )
		{
			$pattern = '/'.preg_quote( $pattern ).'/';
			$text = preg_replace( $pattern, $replacement, $text );
		}
	}
	/**
		E.g. #fnc|param1... 
	 */
	private function getParserFunctionValueFromText( &$text, &$found )
	{
		$params = explode('|', $text );
		$action = array_shift( $params );

		$r = $this->getParserFunctionValue( $params, $action );
		if ($r !== null)
			$found = true;
	
		return $r;	
	}
	/**
		This function handles all the hard work. It relies on MediaWiki's
		parser to reach the registered 'parser functions' and 'magic words'.
		
		It also implements the special keyword (($disable$)) which stops all
		'parserphase2' and 'parser after tidy' functionality. This is especially useful
		in case of documentation pages.
	 */
	private function recursiveReplace( &$o, &$found, $depth = 0 )
	{
		//TODO: better error handling.
		if ( $depth > self::depthMax )
			return null;
			
		$r = preg_match_all( self::$masterPattern, $o, $m );
		
		// did we find a 'terminal' token?
		// signal it to the next level up.
		if ( ($r === false) || ( $r === 0 ) )
			return null;

		$depth++;
		
		// recurse.
		foreach( $m[1] as $index => &$match )
		{
			$replacement = $this->recursiveReplace( $match, $found, $depth );

			if ($replacement === null)
			{
				$r = $this->getParserFunctionValueFromText( $match, $found );
				$p = '/'.preg_quote( $m[0][$index] ).'/si';
				$o = preg_replace( $p, $r, $o, 1 );				
			}
		}

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
		Query our friendly MediaWiki parser
	 */
	private function getParserFunctionValue( &$params, &$var )
	{
		// enabled by default.
		static $enable = true;
	
		// sectional enable/disable commands.
		if ($var === 'enable')	{ $enable = true; return null; }
		if ($var === 'disable')	{ $enable = false; return null; }
		if (!$enable) return null;

		// real work starts here.
		$value = null;
		
		global $wgParser, $wgTitle, $wgContLang;

		// check if the 'mTitle' property is set
		if (!is_object($wgParser->mTitle))
			$wgParser->mTitle = $wgTitle;

		$varname = $wgContLang->lc( $var );
		$idl = MagicWord::getVariableIDs();
						
		// First, look for $action in 'parser variables'
		if (in_array( $varname, $idl ))
			return $this->getValue( $varname );

		// If not found, check for $action in 'parser functions.
		$function = null;

		if ( isset( $wgParser->mFunctionSynonyms[1][$var] ) ) 
			$function = $wgParser->mFunctionSynonyms[1][$var];
		else 
		{
			# Case insensitive functions
			$function = strtolower( $action );
			if ( isset( $wgParser->mFunctionSynonyms[0][$var] ) ) 
				$function = $wgParser->mFunctionSynonyms[0][$var];
			else
				$function = false;
		}
	
		if ( $function ) 
		{
			$funcArgs = array_map( 'trim', $params );
			$funcArgs = array_merge( array( &$wgParser) , $funcArgs );
			$result = call_user_func_array( $wgParser->mFunctionHooks[$function], $funcArgs );

			if ( is_array( $result ) ) 
			{
				if ( isset( $result[0] ) ) 
					$value = $result[0];
			} 
			else 
				$value = $result;
		}

		return $value;	
	}
		
} // end class

//</source>