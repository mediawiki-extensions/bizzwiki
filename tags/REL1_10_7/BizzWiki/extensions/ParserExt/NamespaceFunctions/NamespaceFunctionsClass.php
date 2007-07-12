<?php
/*<wikitext>
{| border=1
| <b>File</b> || NamespaceFunctionsClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/
class NamespaceFunctionsClass extends ExtensionClass
{
	// constants.
	const thisName = 'NamespaceFunctionsClass';
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

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// Functions which are meant to be accessed through 'ParserPhase2' functionality


	#public function mg_( &$parser, )
	// (($ #magic word | $))
	// 
	#{ }

	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	// Namespace related functions
	// Useful for building <select> <option> sections
	// e.g. (($#foreachx|$bwNamespaceFunctions|getRealNamespacesNames| ...

	public static function getNamespacesNames( $user = null, $right = 'read' )
	/*
		This method returns a list of valid namespaces according to the specified 'right' for 'user'
	*/
	{
		$l = null;
		
		if ( $user === null or $user == 0)
		{
			global $wgUser;
			$user = $wgUser;
		}
		global $wgCanonicalNamespaceNames;
			
		foreach( $wgCanonicalNamespaceNames as $id => $name )
			if ( $user->isAllowed( $right, $id ))
				$l[ $id ] = $name;
		
		// Namespace class does not return NS_MAIN by default....
		if ( $user->isAllowed( $right, NS_MAIN ))
			$l[ NS_MAIN ] = Namespace::getCanonicalName( NS_MAIN );
		
		ksort( $l );
		
		return $l;
	}//end

	public static function getNamespacesIDs( $user, $right )
	{
		$id = null;
		
		$l = self::getNamespacesNames( $user, $right );
		if (!empty($l))
			foreach( $l as $id => $name )
				$l2[] = $id;
				
		return $id;
	}

	public static function getRealNamespacesNames( $user, $right )
	// returns canonical names of 'real' namespaces i.e. ones with corresponding pages in the database
	// Basically excludes NS_SPECIAL and NS_MEDIA namespaces
	{
		$l = self::getNamespacesNames( $user, $right );
		if (!empty($l))
			foreach( $l as $id => $name )
				if ( $id < 0 )
					unset( $l[$id] );
		return $l;
	}


} // end class.

?>