<?php
/***********************
	BizzWikiSettings.php
	$Id$

*/

// define new namespaces constants
define('NS_BIZZWIKI',   100);
define('NS_FILESYSTEM', 102);

// Need to include standard 'Namespace.php'
require('../includes/Namespace.php');

// Add the new namespaces to the global variables
$wgExtraNamespaces[NS_BIZZWIKI]   = 'Bizzwiki';
$wgExtraNamespaces[NS_FILESYSTEM] = 'Filesystem';

$wgCanonicalNamespaceNames = $wgCanonicalNamespaceNames + $wgExtraNamespaces;

/*
	Apply new permission management functionality
	1) Hierarchical Namespace Permissions
	2) Wipe out the standard Mediawiki permission settings
	3)  
	4) Provision the new permission settings
*/

// 1
require('extensions/HierarchicalNamespacePermissions/HierarchicalNamespacePermissions.php');

// 2
unset( $wgGroupPermissions );

// 3


// 4
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey("~","~","~")]    = true;

// *****************************************************************************************



?>