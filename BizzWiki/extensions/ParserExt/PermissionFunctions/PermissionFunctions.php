<?php
/*(($disable$))<!--<wikitext>-->
{{Extension
|name        = PermissionFunctions
|status      = beta
|type        = parser
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
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
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
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/PermissionFunctions/PermissionFunctions.php');
</source>

== History ==
* Added setting of contextual variable upon permission error
* Added clearing of page's text upon permission error

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== History ==

== Code ==
<!--</wikitext>--><source lang=php>*/
$wgExtensionCredits[PermissionFunctions::thisType][] = array( 
	'name'        => PermissionFunctions::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Provides a collection of permission management functionality.',
	'url' 		=> 'http://mediawiki.org/wiki/Extension:PermissionFunctions',			
);

class PermissionFunctions
{
	// constants.
	const thisName = 'PermissionFunctions';
	const thisType = 'other';
	
	var $permissionErrorFound;
		
	function __construct( ) 
	{
		$this->permissionErrorFound = false;
	}

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
		{
			$this->permissionErrorFound = true;			
			// set a 'context' variable to help other extensions.
			wfRunHooks('PageVarSet', array( 'PermissionError', &$this->permissionErrorFound ) );			
			$wgOut->clearHTML();
			$wgOut->permissionRequired( $requiredRight );
		}
	}
	/**
	 */
	public function hEndParserPhase2( &$op, &$text )
	{
		if ($this->permissionErrorFound)
			$text = null;
		return true;
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
//</source>