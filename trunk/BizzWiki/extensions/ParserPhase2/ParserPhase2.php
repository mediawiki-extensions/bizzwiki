<?php
/*<wikitext>
{| border=1
| <b>File</b> || ParserPhase2.php
|-
| <b>Revision</b> || $Id: ParserPhase2.php 371 2007-07-12 15:05:24Z jeanlou.dupont $
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension enables performing a 'second pass' through a 'parser cached' page replacing for 
'dynamic' variables. In a word, once a page is normally processed (i.e. 'first pass') Mediawiki 'fixes'
all templates & variables in a 'parser cached' page. This extension enables substituting selected 
variables upon page view whilst still preserving the valuable job performed by the parser/parser cache.

== Features ==
* Integrates with the standard Mediawiki Parser Cache
* Provides a simple 'magic word' based interface to standard Mediawiki variables
* Does not handle 'nested' magic words e.g. (($ magic word1 | (($magic word 2$)) $))

== Usage ==
(($magic word$))
:Where 'variable' is a standard Mediawiki magic word e.g. CURRENTTIME, REVISIONID etc.

== Dependancy ==
* ExtensionClass extension

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/ParserPhase2/ParserPhase2.php');
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

== TODO ==
* possibly fix to allow mixing up (($..$)) and ((..)) patterns on the same page (TBD)

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
	
	const pattern1 = '/\(\(\$(.*)\$\)\)/siU';
	const pattern2 = '/\(\((.*)\)\)/siU';	// tracks more closely MW parser functions style
	
	function __construct( ) {}

	function hOutputPageBeforeHTML( &$op, &$text )
	{
		$m = $this->getList( $text );
		if ( empty( $m ) ) return true; // nothing to do

		// PHP sometimes messes up in preg_match_all returning an empty array
		// we need to guard against this or else client side caching always get thrashed!
		$found = false; 
		
		foreach( $m[1] as $index => $str)
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

		// we found some dynamic variables, disable client side caching.
		// parser caching is not affected.
		if ( $found )
			$op->enableClientCache( false );

		$this->replaceList( $text, $m, $rl );

		wfRunHooks('EndParserPhase2', array( &$op, &$text ) );

		return true; // be nice with other extensions.
	}
	private function getList ( &$text )
	{
		// find the (($...$)) matches
		$r1 = preg_match_all(self::pattern1, $text, $m1 );
		
		// if we found some, return.
		if ( ($r1 !== false) && ( $r1!==0 ) )
			return $m1;
		
		// find the ((#...#)) matches	
		$r2 = preg_match_all(self::pattern2, $text, $m2 );	
		
		return $m2;
	}
	private function getValue( $varid )
	{
		// ask our friendly MW parser for its help.
		global $wgParser;
		$value = $wgParser->getVariableValue( $varid );
		
		return $value;
	}
	private function replaceList( &$text, &$source, &$target )
	{
		foreach( $source[0] as $index => $marker )
			$text = str_replace( $marker, $target[$index], $text );	
	}

} // end class

?>