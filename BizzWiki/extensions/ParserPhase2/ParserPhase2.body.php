<?php
/**
 * @author Jean-Lou Dupont
 * @package ParserPhase2
 */
//<source lang=php>*/
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
		$this->execute( $text, 'BeforeStrip', $found );
				
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
		$this->execute( $text, 'AfterTidy', $found );

		return true; // be nice with other extensions.
	}

	/**
		ParserPhase2 core function: gets a list of replacement to be done,
		executes the referenced functions and replaces the text in of the page. 
	 */
	function hOutputPageBeforeHTML( &$op, &$text )
	{
		$this->execute( $text, 'BeforeOutput', $found );
			
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