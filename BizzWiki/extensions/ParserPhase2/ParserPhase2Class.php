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
			'description' => 'Enables performing a `second pass` parsing over an already cached page for replacing dynamic variables'
		);
	}
	public function setup() 
	{ parent::setup();	}

	function hOutputPageBeforeHTML( &$op, &$text )
	{
		$m = $this->getList( $text );
		if ( empty( $m ) ) return true; // nothing to do

		// we found some dynamic variables, disable client side caching.
		$op->enableClientCache( false );

		foreach( $m[1] as $index => $str)
		{
			// (($var|variable name}}
			$params = explode('|', $str);
			switch ($params[0])
			{
				case 'var':
					$value = $this->getValue( $params[1] );
					$rl[$index] = $value;
					break;
					
					// only variables accessible through the parser
					// are supported at this point.
				default:
					break;	
			}
		}

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