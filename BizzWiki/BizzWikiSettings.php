<?php

/***************************************************************************
	
	BizzWikiSettings.php
	
	$Id$

	TODO:
	=====
	- disable proxy caching functionality for 'file system' & BizzWiki namespaces
	

****************************************************************************/

// Base class for multiple extensions
require('extensions/ExtensionClass.php');
require('extensions/RunPHP_class.php');

// Parser & Page caching.
$wgEnableParserCache = true;
$wgParserCacheType   = CACHE_ANYTHING;
$wgCachePages        = true;

// Disable raw html
// (There is the extension 'addHtml' to better cover this)
$wgRawHtml = false;

// define new namespaces constants
define('NS_BIZZWIKI',   100);
define('NS_FILESYSTEM', 102);
define('NS_INTERWIKI',  104);

// Need to include standard 'Namespace.php'
require($IP.'/includes/Namespace.php');

// Add the new namespaces to the global variables
$wgExtraNamespaces[NS_BIZZWIKI]   = 'Bizzwiki';
$wgExtraNamespaces[NS_FILESYSTEM] = 'Filesystem';
$wgExtraNamespaces[NS_INTERWIKI]  = 'Interwiki';

// Put all the namespaces in the global variable
$wgCanonicalNamespaceNames = $wgCanonicalNamespaceNames + $wgExtraNamespaces;

// Subpages
$bwNamespacesWithSubpages = array ( NS_MAIN,
									NS_PROJECT,
									NS_PROJECT_TALK,
									NS_CATEGORY,
									NS_CATEGORY_TALK,
									NS_MEDIAWIKI,
									NS_MEDIAWIKI_TALK,									
									NS_BIZZWIKI,
									NS_FILESYSTEM,
									NS_INTERWIKI,	// not used at the moment.
									);
foreach ( $bwNamespacesWithSubpages as $index => $bwx )
	$wgNamespacesWithSubpages[ $bwx ] = true;
	
/* *******************************************************
	Apply new permission management functionality
	1) Hierarchical Namespace Permissions
	2) Wipe out the standard Mediawiki permission settings
	3) All rights
	4) Provision the new permission settings
*/
require('extensions/HierarchicalNamespacePermissions/HierarchicalNamespacePermissions.php');
require('extensions/RawRight/RawRight.php');
require('extensions/ViewsourceRight/ViewsourceRight.php');

// define group hierarchy
// default is: sysop -> user -> *
#hnpClass::singleton()->setGroupHierarchy( array('sysop', 'user', '*') ));

unset( $wgGroupPermissions );

$bwNamespaceIndependantRights =  array( 'createaccount',
										'ipblock-exempt',
										'hideuser',
										'userrights',
										'siteadmin',
										'import',
										'deletedhistory',
										'deleterevision',
										'block',
										'bot',             	// TODO
										'proxyunbannable', 	// TODO
										'trackback',		// TODO
										'unwatchedpages',	// TODO
										'readlog',  		// BizzWiki specific
										'siteupdate',		// BizzWiki specific
										'undelete',			// BizzWiki specific
									);


$bwNamespaceDependantRights =  array(	'read', 'edit', 'minoredit', 'create', 'delete', 'move',								
										'nominornewtalk', 
										"createpage", "createtalk",
										
										"rollback",			// TODO
										'protect',
										'patrol', "autopatrol",
										'purge',			// TODO
										'upload', 'reupload', 'reupload-shared', // for now, those rights
										'upload_by_url',						// are only relevant to NS_IMAGE
										
										'raw',        // BizzWiki specific
										'viewsource', // BizzWiki specific
										'browse',     // BizzWiki specific
										'search',     // BizzWiki specific

									);
									
// Critical permission system initialization
hnpClass::singleton()->addNamespaceIndependantRights( $bwNamespaceIndependantRights );
hnpClass::singleton()->addNamespaceDependantRights(   $bwNamespaceDependantRights );

	// Sysop gets all the rights.
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey("~","~","~")]     = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey("~","~","!bot")]  = true;  // required !!
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/newusers",	"browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/commitfil",	"browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/upload",		"browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/delete",   	"browse")] = true;

	// Anonymous users don't get much...
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey("~","~","createaccount")] = true;

		// remove access to some log entries.
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/newusers", "!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/commitfil","!browse")] = true; // FileManager extension
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/upload",   "!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/delete",   "!browse")] = true;
#$wgGroupPermissions['*' ][hnpClass::buildPermissionKey("~","~","readlog")] = true;  // debugging

$bwAnonymousNamespaces = array( NS_MAIN, NS_TALK,
								NS_PROJECT, NS_PROJECT_TALK,
								NS_CATEGORY, NS_CATEGORY_TALK,
								NS_HELP, NS_HELP_TALK,
								NS_SPECIAL
								); 

foreach( $bwAnonymousNamespaces as $index => $bwx )
{	
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","read")]   = true;
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","browse")] = true;
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","search")] = true;
	#$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","browse")] = true;   // debugging	
}

	// User
	
		// Log Entries access
$wgGroupPermissions['user' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/newusers","!browse")] = true;

	// User group can access more namespaces
$bwUserNamespaces = array (	NS_TEMPLATE, NS_TEMPLATE_TALK,
							NS_IMAGE, NS_IMAGE_TALK,														
							);	
foreach( $bwUserNamespaces as $index => $bwx )
	{
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","read")] = true;
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","browse")] = true;
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","search")] = true;
	}

// For testing QueryPage.php functionality
// as in 'SpecialPopularpages.php'.
// DEBUG
$wgContentNamespaces[] = NS_BIZZWIKI;
$wgContentNamespaces[] = NS_FILESYSTEM;
$wgContentNamespaces[] = NS_INTERWIKI;

// *****************************************************************************************

// Protect critical namespaces from Templating level permission bypassing.
$wgNonincludableNamespaces[] = NS_FILESYSTEM;
$wgNonincludableNamespaces[] = NS_BIZZWIKI;
$wgNonincludableNamespaces[] = NS_INTERWIKI;

// *****************************************************************************************

/**
 * Show a bar of language selection links in the user login and user
 * registration forms; edit the "loginlanguagelinks" message to
 * customise these
 */
$wgLoginLanguageSelector = false;

// readfile & commitfile rights
require('extensions/FileManager/FileManager.php');

// syntax highlighting for the NS_FILESYSTEM namespace.
require('extensions/FileSystemSyntaxColoring/FileSystemSyntaxColoring.php');

// New User Logging
require('extensions/NewUserLog/Newuserlog.php');

// Show Redirect Page Text extension
require('extensions/ShowRedirectPageText/ShowRedirectPageText.php');

// Interwiki table management
require('extensions/InterWikiLinkManager/InterWikiLinkManager.php');

// Sidebar extended
require('extensions/SidebarEx/SidebarEx.php');

// Generic Syntax Highlighter
require('extensions/GeSHi/geshi.php');

// Enhanced Special Pages
require('extensions/SpecialPagesManager/SpecialPagesManager.php');

// Form/Page related tools
require('extensions/addScriptCss/AddScriptCss.php');
require('extensions/SecureHTML/SecureHTML.php');
require('extensions/SecureProperties/SecureProperties.php');
require('extensions/ParserTools/ParserTools.php');
require('extensions/FormProc/FormProc.php');

#require('extensions/PageTools/PageTools.php'); 

require('extensions/AutoLanguage/AutoLanguage.php');
AutoLanguageClass::$exemptNamespaces[] = NS_BIZZWIKI;
AutoLanguageClass::$exemptNamespaces[] = NS_INTERWIKI;
AutoLanguageClass::$exemptNamespaces[] = NS_FILESYSTEM;

require('extensions/ParserPhase2/ParserPhase2.php');

## To enable image uploads, make sure the 'images' directory
## is writable, then set this to true:
$wgEnableUploads     = true;

## This must be set if the 'Updater' extension is to be functional
$wgFileExtensions[]  = "zip";

// Updater extension
#require('extensions/Updater/Updater.php');

?>