<?php
/*<wikitext>
{| border=1
| <b>File</b> || ForeachFunctionClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/
class ForeachFunctionClass extends ExtensionClass
{
	// constants.
	const thisName = 'ForeachFunctionClass';
	const thisType = 'other';
	const id       = '$Id$';	
		
	public static function &singleton()
	{ return parent::singleton( );	}
	public function setup() 
	{ parent::setup();	}

	function ForeachFunctionClass( )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Looping functions for global objects using parser functions',
			'url' => self::getFullUrl(__FILE__),			
		);
	}

	public function mg_foreachx( &$parser, &$object, &$property, &$pattern )
	// {{#foreachx:global object name|property name|pattern}}
	// {{#foreachx:global object name|method name  |pattern}}	
	// Works on 'array' exclusively.
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

	public function mg_forx( &$parser, &$object, &$prop, &$pattern, &$start, &$stop )
	// {{#forx:global object name|property name|pattern|start index|stop index}}
	// {{#forx:global object name|method name  |pattern|start index|stop index}}	
	// Works on 'array' exclusively.
	{
		$a = self::getArray( $object, $prop );
		
		if (empty( $a )) return;
		
		$result = '';
		for ( $index= $start; $index < $stop; $index++ )
		{
			$key = $index;
			$value = $a[ $key ];
			$result .= self::replaceVars( $pattern,  $key, $value, $index );
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
} // end class.

?>