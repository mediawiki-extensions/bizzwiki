<?php
/*(($disable$))<wikitext>
{{Extension
|name        = PermissionFunctions
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ParserExt/PermissionFunctions/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
== Purpose==
Provides a collection of permission management functionality.

== Features ==
* Magic Word 'checkPermission' : verifies if 'user' is allowed 'right' and if *not* then the page 'Permission Error' is served.
** This function is rather useful for building 'forms'
** Rather only helpful when used in a 'ParserPhase2' context (e.g. (($#checkpermission|edit$))  )
* Static function 'getpermissionline'
** Meant to be itereated with using 'ForeachFunction' magic words

== Usage ==
E.g. check to see if the current user has the 'edit' right
* <nowiki>(($#checkpermission|edit$))</nowiki>

== Dependancies ==
* [[Extension:StubManager|StubManager]] extension

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'PermissionFunctions', 
							$IP.'extensions/ParserExt/PermissionFunctions/PermissionFunctions.php',
							null,							
							null,
							false, // no need for logging support
							null,	// tags
							array( 'checkpermission' ),  //of parser function magic words,
							null
						 );
</source>

== History ==

== Code ==
</wikitext>*/
$wgExtensionCredits[PermissionFunctions::thisType][] = array( 
	'name'        => PermissionFunctions::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Provides a collection of permission management functionality.',
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);

class PermissionFunctions
{
	// constants.
	const thisName = 'PermissionFunctions';
	const thisType = 'other';
		
	function __construct( ) {}

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// Functions which are meant to be accessed through 'ParserPhase2' functionality

	public function mg_checkpermission( &$parser, $requiredRight = 'read' )
	// (($ #checkpermission|required right $))
	// redirects to the standard 'Permission Error' page if the user lacks the $requiredRight
	{
		global $wgUser;
		global $wgTitle;
		global $wgOut;
		
		$ns = $wgTitle->getNamespace();
		
		if (!$wgUser->isAllowed( $requiredRight, $ns ) )
			$wgOut->permissionRequired( $requiredRight );
	}

	public static function getpermissionline( $group, $namespace )
	// This function is meant to be used in conjuction with 'Hierarchical Namespace Permission' extension.
	// E.g. (($#foreachx|bwPermissionFunctions|getpermissionline| ... $))
	{
		if (!class_exists('hnpClass'))
			return "<b>PermissionFunctions:</b> ".wfMsg('error')." <i>Hierarchical Namespace Permission Extension</i>";		
		return hnpClass::getPermissionGroupNamespace( $group, $namespace );
	}

	public static function usercan( &$user, &$ns, &$pt, &$action )
	{
		if (!class_exists('hnpClass')) 
			return "<b>PermissionFunctions:</b> ".wfMsg('error')." <i>Hierarchical Namespace Permission Extension</i>";
		
		if ( !is_object( $user ) )
		{
			global $wgUser;
			$user = &$wgUser;
		}
		return hnpClass::userCanInternal( $user, $ns, $pt, $action );
	}
} // end class.

?>