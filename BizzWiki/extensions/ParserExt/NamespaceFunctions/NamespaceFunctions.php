<?php
/*<wikitext>
{| border=1
| <b>File</b> || NamespaceFunctions.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Collection of namespace management functionality.

== Features ==

== Usage ==

== Dependancies ==
* [[Extension:StubManager|StubManager]] extension (v>=306)

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/ParserExt/NamespaceFunctions/NamespaceFunctions.php');
</source>

== History ==

== Code ==
</wikitext>*/
global $wgExtensionCredits;
$wgExtensionCredits[NamespaceFunctionsClass::thisType][] = array( 
	'name'        => NamespaceFunctionsClass::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => '',
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);

class NamespaceFunctionsClass
{
	// constants.
	const thisName = 'NamespaceFunctionsClass';
	const thisType = 'other';
		
	function __construct( ) {	}

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