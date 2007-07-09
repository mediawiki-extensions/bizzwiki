<?php
/*<wikitext>
{| border=1
| <b>File</b> || FormProcBaseClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Provides a useful base class for form processing classes.

== Features ==
* Only loaded when actually used ('stub object' functionality)

== History ==

== Code ==
</wikitext>*/

class FormHelper
{
	private function &singleton()
	{
		static $instance = null;
		if (!$instance) $instance = new FormHelper;
		return $instance;	
	}

	function __call( $name, $args ) 
	{ return $this->_call( $name, $args );	}

	function _newObject() 
	{ return self::singleton();	}

	function __construct( ) 
	{	}
	
	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	// Namespace related functions
	// Useful for building <select> <option> sections
	// e.g. (($#foreachx|bwFormHelper|getRealNamespacesNames| ...
	
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
	{
		$l = self::getNamespacesNames( $user, $right );
		if (!empty($l))
			foreach( $l as $id => $name )
				if ( $id < 0 )
					unset( $l[$id] );
		return $l;
	}

	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

} // end class
?>