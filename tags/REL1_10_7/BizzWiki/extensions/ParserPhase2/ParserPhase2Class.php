<?php
/*<wikitext>
{| border=1
| <b>File</b> || ParserPhase2Class.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/

class ParserPhase2Class extends ExtensionClass
{
	// constants.
	const thisName = 'ParserPhase2Class';
	const thisType = 'other';
	const id       = '$Id$';		
	
	const pattern = '/\(\(\$(.*)\$\)\)/siU';
	
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function ParserPhase2Class( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Enables performing a `second pass` parsing over an already cached page for replacing dynamic variables',
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	public function setup() 
	{ parent::setup();	}

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

		return true; // be nice with other extensions.
	}
	private function getList ( &$text )
	{
		// find the (($...$)) matches
		$r = preg_match_all(self::pattern, $text, $m );	
		
		return $m;
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