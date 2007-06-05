<?php
/***************************************************************************
	
	BizzWikiSettings.php
	
	$Id$

	TODO:
	=====
	- disable search & proxy caching functionality for 'file system' & BizzWiki namespaces
	

****************************************************************************/

// Base class for multiple extensions
require('extensions/ExtensionClass.php');

// Parser & Page caching.
$wgEnableParserCache = true;
$wgParserCacheType   = CACHE_ANYTHING;
#$wgCachePages        = true;

// Disable raw html
// (There is the extension 'addHtml' to better cover this)
$wgRawHtml = false;

// define new namespaces constants
define('NS_BIZZWIKI',   100);
define('NS_FILESYSTEM', 102);

// Need to include standard 'Namespace.php'
require($IP.'/includes/Namespace.php');

// Add the new namespaces to the global variables
$wgExtraNamespaces[NS_BIZZWIKI]   = 'Bizzwiki';
$wgExtraNamespaces[NS_FILESYSTEM] = 'Filesystem';

$wgCanonicalNamespaceNames = $wgCanonicalNamespaceNames + $wgExtraNamespaces;

// Subpages
$bwNamespacesWithSubpages = array ( NS_MAIN,
									NS_PROJECT,
									NS_BIZZWIKI,
									NS_FILESYSTEM
									);
foreach ( $bwNamespacesWithSubpages as $index => $bwx )
	$wgNamespacesWithSubpages[ $bwx ] = true;
	
/*
	Apply new permission management functionality
	1) Hierarchical Namespace Permissions
	2) Wipe out the standard Mediawiki permission settings
	3) All rights
	4) Provision the new permission settings
*/

// 1
require('extensions/HierarchicalNamespacePermissions/HierarchicalNamespacePermissions.php');
require('extensions/RawRight/RawRight.php');
require('extensions/ViewsourceRight/ViewsourceRight.php');

// 2
unset( $wgGroupPermissions );

// 3
$bwNamespaceIndependantRights =  array( 'createaccount',
										'readlog',  // BizzWiki specific
									);

$bwNamespaceDependantRights =  array(	'read', 'edit', 'minoredit', 'create', 'delete', 'move',

										'upload', 'reupload', 'reupload-shared', // for now, those rights
																				// are only relevant to NS_IMAGE
										'raw',        // BizzWiki specific
										'viewsource', // BizzWiki specific
										'browse',     // BizzWiki specific
										'search',     // BizzWiki specific
									);
									
// Critical permission system initialization
hnpClass::addNamespaceIndependantRights( $bwNamespaceIndependantRights );
hnpClass::addNamespaceDependantRights(   $bwNamespaceDependantRights );

// 4

	// Sysop gets all the rights.
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey("~","~","~")]     = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey("~","~","!bot")]  = true;

	// Anonymous users don't get much...
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey("~","~","createaccount")] = true;
$bwAnonymousNamespaces = array( NS_MAIN, NS_TALK,
								NS_PROJECT, NS_PROJECT_TALK,
								NS_CATEGORY, NS_CATEGORY_TALK,
								NS_HELP, NS_HELP_TALK,
								NS_SPECIAL
								); 

foreach( $bwAnonymousNamespaces as $index => $bwx )
{	
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","read")] = true;
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","browse")] = true;
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","search")] = true;
}

	// User
$bwUserNamespaces = array (	NS_MAIN, NS_MAIN_TALK,
							NS_PROJECT, NS_PROJECT_TALK,
							NS_CATEGORY, NS_CATEGORY_TALK,
							NS_HELP, NS_HELP_TALK,
							NS_TEMPLATE, NS_TEMPLATE_TALK,
							NS_IMAGE, NS_IMAGE_TALK,														
							);	
foreach( $bwUserNamespaces as $index => $bwx )
	{
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","read")] = true;
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","browse")] = true;
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","search")] = true;
	}
	


// *****************************************************************************************

// Protect critical namespaces from Templating level permission bypassing.
$wgNonincludableNamespaces[] = NS_FILESYSTEM;
$wgNonincludableNamespaces[] = NS_BIZZWIKI;

// *****************************************************************************************

// readfile & commitfile rights
require('extensions/FileManager/FileManager.php');

// syntax highlighting for the NS_FILESYSTEM namespace.
require('extensions/SyntaxColoring/SyntaxColoring.php');

?>