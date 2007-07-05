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
	const id       = '$Id$';	
		
	//
	static $mgwords = array( 'pg', 'ps','pf' );
	const actionGet = 0;
	const actionSet = 1;
	const actionFnc = 2;	
	
	// Namespace exemption functionality
	static $enableExemptNamespaces = true;
	static $exemptNamespaces;
	
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function SecurePropertiesClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( self::$mgwords );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Enables global object property get/set on protected pages',
			'url' => self::getFullUrl(__FILE__),			
		);

		// default exempt namespaces from the BizzWiki platform.
		// won't affect installs of the extension outside the BizzWiki platform.
		if (defined('NS_BIZZWIKI'))   self::$exemptNamespaces[] = NS_BIZZWIKI;
		if (defined('NS_FILESYSTEM')) self::$exemptNamespaces[] = NS_FILESYSTEM;
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
	// {{#pg:object|property name|value}}
	{
		$args = func_get_args();
		return $this->process( $args, self::actionSet );
	}
	public function mg_pf( )
	// {{#pg:object|function name}}
	{
		$args = func_get_args();
		return $this->process( $args, self::actionFnc );
	}

	private function process( &$args, $action = self::actionGet )
	{
		$parser = $args[0];
		
		if ( !$this->isAllowed( $parser->mTitle ) ) 
			return "<b>SecureProperties:</b> ".wfMsg('badaccess');

		$object   =             $args[1];
		$property = $fnc      = $args[2];
		$value    = $param1   = $args[3];
		$param2               = $args[4];
		$param3               = $args[5];
				
		if ( !is_object( $obj = $GLOBALS[$object] ) ) 
			return "<b>SecureProperties:</b> ".wfMsg('error')." <i>$object</i>";

		switch( $action )
		{
			case self::actionGet:
				return $obj->$property;
			case self::actionSet:
				$obj->$property = $value;					
				return null;
			case self::actionFnc:
				return $obj->$fnc();					
		}
	}

	private function isAllowed( &$title )
	{ 
		if (self::$enableExemptNamespaces)
		{
			$ns = $title->getNamespace();
			if ( !empty(self::$exemptNamespaces) )
				if ( in_array( $ns, self::$exemptNamespaces) )
					return true;	
		}
		
		// check protection status
		if ( $title->isProtected( 'edit' ) ) return true;
		
		return false;
	}

} // end class
?>