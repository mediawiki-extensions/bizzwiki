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
* Added functionality to execute parser functions/magic words just BEFORE the 'stip' process i.e. before the parser really begins.
* Added 'enable' magic word
* Added support for 'sectional execution' i.e. replacement between 'enable' and 'disable' magic words

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
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);

class ParserPhase2
{
	// constants.
	const thisName = 'ParserPhase2';
	const thisType = 'other';
	
	const depthMax = 15;
	
	// (($ ... $))
	const pattern1a = '/\(\(\$(.*)\$\)\)/siU';
	// ((  ...  ))
	const pattern1b = '/\(\((.*)\)\)/siU';	// tracks more closely MW parser functions style
	//  ((% ... %))
	const pattern2  = '/\(\(\%(.*)\%\)\)/siU';
	//  ((@ ... @))
	const pattern3  = '/\(\(\@(.*)\@\)\)/siU';
	
	// new patterns
	static $newPatterns = array(
	'BeforeOutput' => array(
				'(($' => '\xfe',
				'$))' => '\xff',
				'((' => '\xfe',
				'))' => '\xff',
				),
	'AfterTidy' => array(
				'((%' => '\xfe',
				'%))' => '\xff',			
				),
	'BeforeStrip' => array(
				'((@' => '\xfe',
				'((@' => '\xff',			
				)
	);
	
	static $masterPattern = '/\xfe(((?>[^\xfe\xff]+)|(?R))*)\xff/si';
	
	// variables
	#var $disable;
	const disablePattern1a = '(($disable$))';
	const disablePattern1b = '((disable))';	
	const disablePattern2  = '((%disable%))';	
	const disablePattern3  = '((@disable@))';
	
	function __construct( ) 
	{
		$this->disable = false;
	}

	/**
		The parser functions enclosed in ((@ ... @)) are executed
		before the MediaWiki starts parsing the wiki-text.
	 */
	public function hParserBeforeStrip( &$parser, &$text, &$mStripState )
	{
		$m = $this->getList( $text, self::pattern3 );		
		if ( empty( $m ) ) return true; // nothing to do

		$this->executeList( $m, $text );

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
		$m = $this->getList( $text, self::pattern2 );		
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
		// PHP sometimes messes up in preg_match_all returning an empty array
		// we need to guard against this or else client side caching always get thrashed!
		$m1a = $this->getList( $text, self::pattern1a );
		$m1b = $this->getList( $text, self::pattern1b );		
		if ( empty( $m1a ) && empty( $m1b ) ) return true; // nothing to do

		if ( !empty( $m1a ))
			$found = $this->executeList( $m1a, $text );
		else
			$found = $this->executeList( $m1b, $text );		
			
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
		$found = false; 
		
		foreach( $liste[1] as $index => $str)
		{
			// (($magic word|... parameters...$))
			$params = explode('|', $str);
			$action = array_shift( $params );

			// if we are asked to disable
			if ('disable'==strtolower($action))
			{ $rl[$index]=''; $found = true; continue; }
			#{ $this->disable = true; break; }

			// if we are asked to enable
			if ('enable'==strtolower($action))
			{ $rl[$index]=''; $found = true; continue; }

			$r = $this->getParserFunctionValue( $params, $action );
			if ($r!==null)
			{
				$found = true;
				$rl[$index] = $r;	
			}

		} // end foreach

		$this->replaceList( $text, $liste, $rl );

		return $found;
	}
	/**
	 */
	private function getParserFunctionValue( &$params, &$var )
	{
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
	/**
	
	 */
	private function getList ( &$text, $pattern )
	{
		$r = preg_match_all( $pattern, $text, $m );
		
		// if we found some, return.
		if ( ($r !== false) && ( $r !== 0 ) )
			return $m;
			
		return null;
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
	
	 */
	private function getListV2( &$o, $stack, $currentPattern, $depth = 0 )
	{
		//TODO: better error handling.
		if ( $depth > self::depthMax )
			return null;
			
		$depth++;
		
		if (is_string($o))
		{
			$r = preg_match_all( self::$masterPattern, $o, $m );
			
			// did we find a 'terminal' token?
			// Accumulate those for faster replacing later on.
			if ( ($r === false) || ( $r === 0 ) )
			{
				$stack[$depth][] = array( $currentPattern => $o );
				return $o;	
			}
			// get rid of some junk
			unset( $m[2] );
		}
		else
			$m = $o;

		// recurse.
		if (!empty( $m[1] ))
			foreach( $m[1] as $index => &$match )
				$match = $this->getListV2( $match, $stack, $m[0][$index], $depth );
		
		return $m;
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
		$disable = false;
		
		if (!empty( $source[0] ))
			foreach( $source[0] as $index => $marker )
			{

				if ('(($enable$))'==$marker || '((@enable@))'==$marker || '((%enable%))'==$marker)
					$disable = false;
				elseif ($disable)
					continue;

				if ('(($disable$))'==$marker || '((@disable@))'==$marker || '((%disable%))'==$marker)
					$disable = true;
				$text = str_replace( $marker, $target[$index]/*.'<!--'.$marker.'-->'*/, $text );	
			}
	}
} // end class

//</source>