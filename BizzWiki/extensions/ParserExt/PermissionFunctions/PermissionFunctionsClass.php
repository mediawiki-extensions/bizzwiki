<?php
/*<wikitext>
{| border=1
| <b>File</b> || PermissionFunctionsClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/
class PermissionFunctionsClass extends ExtensionClass
{
	// constants.
	const thisName = 'PermissionFunctionsClass';
	const thisType = 'other';
	const id       = '$Id$';	
		
	public static function &singleton()
	{ return parent::singleton( );	}
	public function setup() 
	{ parent::setup();	}

	function __construct( )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => '',
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	public function mg_checkPermission( &$parser, $requiredRight = 'read' )
	// redirects to the standard 'Permission Error' page if the user lacks the $requiredRight
	{
		global $wgUser;
		global $wgTitle;
		global $wgOut;
		
		$ns = $wgTitle->getNamespace();
		
		if (!$wgUser->isAllowed( $requiredRight, $ns ) )
			$wgOut->permissionRequired( $requiredRight );
	}

} // end class.

?>