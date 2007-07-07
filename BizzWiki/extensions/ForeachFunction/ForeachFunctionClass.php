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

	public function mg_foreachx( &$parser, &$object, &$prop, &$pattern )
	// {{#foreachx:global object name|property name|pattern}}
	// {{#foreachx:global object name|method name  |pattern}}	
	// Works on 'array' exclusively.
	{
		return ParserPhase2Class::doForeachx( $object, $prop, $pattern );
	}

	public function mg_forx( &$parser, &$object, &$prop, &$pattern, &$start, &$stop )
	// {{#forx:global object name|property name|pattern|start index|stop index}}
	// {{#forx:global object name|method name  |pattern|start index|stop index}}	
	// Works on 'array' exclusively.
	{
		return ParserPhase2Class::doForx( $object, $prop, $pattern, $start, $stop );
	}
	
} // end class.

?>