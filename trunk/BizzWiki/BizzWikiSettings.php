<?php
/***************************************************************************
	
	BizzWikiSettings.php
	
	$Id$

****************************************************************************/

// Base class for multiple extensions
require('extensions/ExtensionClass.php');

// Parser & Page caching.
$wgEnableParserCache = true;
$wgParserCacheType   = CACHE_ANYTHING;
$wgCachePages        = true;

// Disable raw html
$wgRawHtml = false;

// define new namespaces constants
define('NS_BIZZWIKI',   100);
define('NS_FILESYSTEM', 102);

// Need to include standard 'Namespace.php'
require('../includes/Namespace.php');

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
	3)  
	4) Provision the new permission settings
*/

// 1
require('extensions/HierarchicalNamespacePermissions/HierarchicalNamespacePermissions.php');
require('extensions/RawRight/RawRight.php');
require('extensions/ViewsourceRight/ViewsourceRight.php');

// 2
unset( $wgGroupPermissions );

// 3
$bwAllRights = array (	'createaccount',
						'read', 'edit', 'minoredit', 'create', 'move', 'delete', 
						'upload', 'reupload', 'reupload-shared',
						'raw',
						'viewsource',
					);

// 4

	// Sysop gets all the rights.
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey("~","~","~")]     = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey("~","~","!bot")]  = true;

	// Anonymous
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey("~","~","createaccount")] = true;
$bwAnonymousNamespaces = array( NS_MAIN, NS_MAIN_TALK,
								NS_PROJECT, NS_PROJECT_TALK,
								NS_HELP, NS_HELP_TALK,
								); 

foreach( $bwAnonymousNamespaces as $index => $bwx )
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","read")] = true;

	// User
$bwUserNamespaces = array (	NS_MAIN, NS_MAIN_TALK,
							NS_PROJECT, NS_PROJECT_TALK,
							NS_HELP, NS_HELP_TALK,
							NS_CATEGORY, NS_CATEGORY_TALK,
							NS_TEMPLATE, NS_TEMPLATE_TALK,
							NS_IMAGE, NS_IMAGE_TALK,														
							);	
foreach( $bwUserNamespaces as $index => $bwx )
	{
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","read")] = true;
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","read")] = true;
	}
	


// *****************************************************************************************



?>