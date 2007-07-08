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
	
	// Extensibility
	static $keywords;
	
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

	// Extensibility feature
	// Ability to add 'keywords' processing functions.
	public static function addKeyword( $keyword, $callback )
	{
		if ( is_callable( $callback ) )
			self::$keywords[ $keyword ] = $callback;		
	}

	function hOutputPageBeforeHTML( &$op, &$text )
	{
		$m = $this->getList( $text );
		if ( empty( $m ) ) return true; // nothing to do

		// PHP sometimes messes up in preg_match_all returning an empty array
		// we need to guard against this or else client side caching always get thrashed!
		$found = false; 
		
		foreach( $m[1] as $index => $str)
		{
			$checkExt = false;
			
			// (($var|variable name$))
			// (($obj|global object name|method name$))
			$params = explode('|', $str);
			$action = array_shift( $params );
			switch ($action)
			{
				// only variables accessible through the parser
				// are supported at this point.
				case 'var':
					$value = $this->getValue( $params[0] );
					$rl[$index] = $value;
					$found = true;
					break;

				// globally accessible objects
				case 'obj':
					$obj = array_shift( $params );
					$fnc = array_shift( $params );
					$rl[$index] = $this->callObjMethod( $GLOBALS[$obj], $fnc, $params );
					$found = true;
					break;
				// globally accessible variable set
				case 'gset':
					$gvar  = array_shift( $params );
					$value = array_shift( $params );
				
					if (isset( $GLOBALS[$gvar] ))
						$GLOBALS[$gvar] = $value;
					$rl[$index] = ''; // nothing to return.
					$found = true;						
					break;
				// globally accessible variable get					
				case 'gget':
					$gvar  = array_shift( $params );
				
					if (isset( $GLOBALS[$gvar] ))
						$rl[$index] = $GLOBALS[$gvar];
					$found = true;						
					break;
				case 'foreachx':  // just to align the name with 'ForeachFunctions' extension
					$obj = array_shift( $params );
					$pro = array_shift( $params );  // array property
					$pat = array_shift( $params );  // pattern
					$rl[$index] = self::doForeachx( $obj, $pro, $pat );
					$found = true;						
					break;
					// for (i=$start$;i<$stop$;i++)
				case 'forx':
					$obj = array_shift( $params );
					$pro = array_shift( $params );  // array property
					$pat = array_shift( $params );  // pattern
					$start=array_shift( $params );  // start i.e. i=$start$
					$stop =array_shift( $params );  // stop i.e. i<$stop$
					$rl[$index] = self::doForx( $obj, $pro, $pat, $start, $stop );
					$found = true;											
					break;
				case 'set':
					break;				
				case 'get':
					break;
				default:
					$checkExt = true;
					break;	
			}

		// if we haven't found in the 'core keywords', try the extensions based keywords.
		if ($checkExt && !empty(self::$keywords) )
			if (array_key_exists( $action, self::$keywords ) )
			{
				$callback = self::$keywords[ $action ];
				$object = $callback[0];
				$method = $callback[1];				

				$func = get_class( $object ) . '::' . $method;
				$callback = array( $object, $method );
				
				if (!is_array( $params ))	$params = array( $params );  // paranoia
				if (empty( $params ))		$params = null;
					
				$found = true;
				
				// give some context to the extension (i.e. title object)
				global $wgTitle;
				$params = array_merge( array( &$wgTitle ), $params );
				
				$rl[$index] = call_user_func_array( $callback, $params );
			}

		}
		
		// we found some dynamic variables, disable client side caching.
		// parser caching is not affected.
		if ( $found )
			$op->enableClientCache( false );

		$this->replaceList( $text, $m, $rl );

		return true; // be nice with other extensions.
	}
	public static function doForeachx( &$object, &$property, &$pattern )
	{
		$a = self::getArray( $object, $property );
		
		if (empty( $a )) return;
		
		$result = '';
		$index = 0;
		foreach( $a as $key => $value )
		{
			$result .= self::replaceVars( $pattern,  $key, $value, $index );
			$index++;
		}
		return $result;
	}
	public static function doForx( &$obj, &$pro, &$pat, &$start, &$stop )
	{
		$a = self::getArray( $obj, $pro );
		
		if (empty( $a )) return;
		
		$result = '';
		for ( $index= $start; $index < $stop; $index++ )
		{
			$key = $index;
			$value = $a[ $key ];
			$result .= self::replaceVars( $pat,  $key, $value, $index );
		}
			
		return $result;
	}
	private static function getArray( &$object, &$property )
	{
		if (!isset( $GLOBALS[$object] )) return null;
		$o = $GLOBALS[$object];

		// array = object->property
		if (is_array( $o->$property )) 
			$a = &$o->$property;

		// array = object->property()
		if (is_callable( array($o, $property) ))
			$a = &$o->$property();

		return $a;		
	}
	public static function replaceVars( &$pattern, &$key, &$value, &$index )
	{
		// find $key$ , $value$, $index$ variables in the pattern
		$r  = str_replace( '$key$',   $key, $pattern );			
		$r2 = str_replace( '$value$', $value, $r );
		$r3 = str_replace( '$index$', $index, $r2 );		
		
		return $r3;
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

	function callObjMethod( &$obj, &$method, &$p )
	{
		echo __METHOD__.' count= '.count($p).'<br/>';
		
		$p = array_values( $p );
		switch ( count( $p ) ) 
		{
			case 0:
				return $obj->$method( );
			case 1:
				return $obj->$method( $p[0] );
			case 2:
				return $obj->$method( $p[0], $p[1] );
			case 3:
				return $obj->$method( $p[0], $p[1], $p[2] );
			case 4:
				return $obj->$method( $p[0], $p[1], $p[2], $p[3] );
			case 5:
				return $obj->$method( $p[0], $p[1], $p[2], $p[3], $p[4] );
			case 6:
				return $obj->$method( $p[0], $p[1], $p[2], $p[3], $p[4], $p[5] );
			default:
				throw new MWException( "Too many arguments to ".__METHOD__ );
		}
	}

} // end class
?>