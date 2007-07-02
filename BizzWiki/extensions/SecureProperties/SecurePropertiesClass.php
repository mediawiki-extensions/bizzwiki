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
	const thisName = 'SecureHTMLclass';
	const thisType = 'other';
	
	//
	static $mgwords = array( '' );
	
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function SecurePropertiesClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => '$Id$',
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Enables secure  code on protected pages'
		);
	}
	public function setup() 
	{ parent::setup();	}



} // end class
?>