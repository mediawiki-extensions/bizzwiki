<?php
/*<wikitext>
{| border=1
| <b>File</b> || SecurePropertiesClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>

== Code ==
</wikitext>*/

class SecurePropertiesClass extends ExtensionClass
{
	// constants.
	const thisName = 'SecurePropertiesClass';
	const thisType = 'other';
	
	//
	static $mgwords = array( 'pg', 'ps' );
	const actionGet = 0;
	const actionSet = 1;
	
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function SecurePropertiesClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( self::$mgwords );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => '$Id$',
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Enables global object property get/set on protected pages'
		);
	}
	public function setup() 
	{ parent::setup();	}

	public function mg_pg( )
	// {{#pg:object|property}}
	{
		$args = func_get_args();
		return $this->process( $args, self::actionGet );
	}

	public function mg_ps( )
	// {{#pg:object|property|value}}
	{
		$args = func_get_args();
		return $this->process( $args, self::actionSet );
	}

	private function process( &$args, $action = self::actionGet )
	{
		$parser = $args[0];
		
		if ( !$this->isAllowed( $parser->mTitle ) ) 
			return "<b>SecureProperties:</b> ".wfMsg('badaccess');

		$object   = $args[1];
		$property = $args[2];
		$value    = $args[3];

		if ( !is_object( $obj = $GLOBALS[$object] ) ) 
			return "<b>SecureProperties:</b> ".wfMsg('error')." <i>$object</i>";

		switch( $action )
		{
			case self::actionGet:
				return $obj->$property;
			case self::actionSet:
				$obj->$property = $value;					
				return null;
		}
	}

	private function isAllowed( &$title )
	{ return $title->isProtected( 'edit' );	 }

} // end class
?>