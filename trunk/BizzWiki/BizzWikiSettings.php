<?php

/***************************************************************************
	
	BizzWikiSettings.php
	
	$Id$

	TODO:
	=====
	- disable proxy caching functionality for 'file system' & BizzWiki namespaces
	

****************************************************************************/
// Define the base of BizzWiki
define('BIZZWIKI', '1_10_7');
$bwPath    = $IP.'/BizzWiki';
$bwExtPath = $IP.'/BizzWiki/extensions';

// Base class for multiple extensions
require('extensions/ExtensionClass.php');
require('extensions/RunPHP_class.php');
require('extensions/ParserPhase2/ParserPhase2.php');

// Parser & Page caching.
$wgEnableParserCache = true;
$wgParserCacheType   = CACHE_ANYTHING;
$wgCachePages        = true;

// Disable raw html
// (There is the extension 'SecureHTML' to better cover this)
$wgRawHtml = false;  // on protected pages, one can use 'SecureHTML' extension 
					// to achieve the same goal

// Need to include standard 'Namespace.php'
require($IP.'/includes/Namespace.php');

## DEFINE NEW NAMESPACES HERE 
## {{

	// define new namespaces constants
	define('NS_BIZZWIKI',   100);
	define('NS_FILESYSTEM', 102);
	define('NS_INTERWIKI',  104);
	
	// Add the new namespaces to the global variables
	$wgExtraNamespaces[NS_BIZZWIKI]   = 'Bizzwiki';
	$wgExtraNamespaces[NS_FILESYSTEM] = 'Filesystem';
	$wgExtraNamespaces[NS_INTERWIKI]  = 'Interwiki';

## }}

$wgCanonicalNamespaceNames[NS_MAIN] = 'Main';

// Put all the namespaces in the global variable
$wgCanonicalNamespaceNames = $wgCanonicalNamespaceNames + $wgExtraNamespaces;

// Subpages
$bwNamespacesWithSubpages = array ( NS_MAIN,
									NS_TALK,
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
										'importupload',										
										'deletedhistory',
										'deleterevision',	// TODO
										'hiderevision',		// TODO
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
									
// Critical permission system initialization -- DO NOT TOUCH
## {{
hnpClass::singleton()->addNamespaceIndependantRights( $bwNamespaceIndependantRights );
hnpClass::singleton()->addNamespaceDependantRights(   $bwNamespaceDependantRights );
## }}

	// Sysop gets all the rights.
	// ##########################
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey("~","~","~")]     = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey("~","~","!bot")]  = true;  // required !!
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/newusers",	"browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/commitfil",	"browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/upload",		"browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/delete",   	"browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/rights",     "browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/move",     	"browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/block",    	"browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/emaillog",  	"browse")] = true;

	// Anonymous
	// #########
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey("~","~","createaccount")] = true;

	// remove access to some log entries.
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/newusers",	"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/commitfil",	"!browse")] = true; // FileManager extension
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/upload",		"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/delete",		"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/rights",		"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/move",		"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/block",		"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/emaillog",  	"!browse")] = true;
#$wgGroupPermissions['*' ][hnpClass::buildPermissionKey("~","~","readlog")] = true;  // debugging

	// Namespace accessible by 'Anonynous'
	// ###################################
$bwAnonymousNamespaces = array( NS_MAIN, NS_TALK,
								NS_PROJECT, NS_PROJECT_TALK,
								NS_CATEGORY, NS_CATEGORY_TALK,
								NS_HELP, NS_HELP_TALK,
								NS_SPECIAL,
								NS_INTERWIKI // BizzWiki specific
								); 

	// Rights available to 'Anonymous'
	// ###############################
foreach( $bwAnonymousNamespaces as $index => $bwx )
{	
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","read")]   = true;
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","browse")] = true;
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","search")] = true;
	#$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","browse")] = true;   // debugging	
}

	// 'Users' inherit all rights from '*' (anonymous)
	// ############################################### 
	
		// Log Entries access
$wgGroupPermissions['user' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/newusers","!browse")] = true;

	// Namespace accessible by 'User'
	// ##############################
$bwUserNamespaces = array (	NS_TEMPLATE, NS_TEMPLATE_TALK,
							NS_IMAGE, NS_IMAGE_TALK,
							NS_USER, NS_USER_TALK														
							);	

	// Additional rights available to 'User'
	// #####################################
foreach( $bwUserNamespaces as $index => $bwx )
	{
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","read")] = true;
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","browse")] = true;
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","search")] = true;
	}

	// Equivalent functionality to 'KeepYourHandsToYourself' extension
	// ###############################################################
$wgExtensionFunctions[] = 'bwKeepYourHandsToYourself';

function bwKeepYourHandsToYourself()
{
	global $wgUser, $wgGroupPermissions;
	$userName = $wgUser->getName();
	$wgGroupPermissions['user'][hnpClass::buildPermissionKey(NS_USER,$userName,"edit")]   = true;
	$wgGroupPermissions['user'][hnpClass::buildPermissionKey(NS_USER,$userName,"create")] = true;	
	$wgGroupPermissions['user'][hnpClass::buildPermissionKey(NS_USER,$userName,"delete")] = true;	
}

// For testing QueryPage.php functionality
// as in 'SpecialPopularpages.php'.
// DEBUG
## {{
$wgContentNamespaces[] = NS_BIZZWIKI;
$wgContentNamespaces[] = NS_FILESYSTEM;
$wgContentNamespaces[] = NS_INTERWIKI;
## }}

// *****************************************************************************************

// Protect critical namespaces from Templating level permission bypassing.
// It is strongly suggested not to mess with this.
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
 // Use the following to customize the source path of the files.
#SpecialPagesManagerClass::singleton()->setPagesPath('page title');

// Form/Page related tools
require('extensions/addScriptCss/AddScriptCss.php');
require('extensions/SecureHTML/SecureHTML.php');
	# SecureHTMLclass::enableExemptNamespaces = false; # turn off
	# SecureHTMLclass::exemptNamespaces[] = NS_XYZ;    # to add namespaces to exemption list

require('extensions/SecureProperties/SecureProperties.php');
require('extensions/ParserExt/ParserTools/ParserTools.php');
require('extensions/FormProc/FormProc.php');

require('extensions/AutoLanguage/AutoLanguage.php');
AutoLanguageClass::$exemptNamespaces[] = NS_BIZZWIKI;
AutoLanguageClass::$exemptNamespaces[] = NS_INTERWIKI;
AutoLanguageClass::$exemptNamespaces[] = NS_FILESYSTEM;

require('extensions/CacheTools/CacheTools.php');

	// Parser Extensions
	// %%%%%%%%%%%%%%%%%
// http://meta.wikimedia.org/wiki/ParserFunctions
require( 'extensions/ParserExt/ParserFunctions/ParserFunctions.php' );

// http://www.mediawiki.org/wiki/Extension:StringFunctions
require( 'extensions/ParserExt/StringFunctions/StringFunctions.php' );

require( 'extensions/ParserExt/ForeachFunction/ForeachFunction.php' );
require('extensions/ParserExt/PageFunctions/PageFunctions.php'); 
require('extensions/ParserExt/PermissionFunctions/PermissionFunctions.php');
require('extensions/ParserExt/NamespaceFunctions/NamespaceFunctions.php');

	// Stubs
	//  Used for rare events handling.
require('extensions/StubManager.php');
StubManager::createStub(	'EmailLog', 
							$bwExtPath.'/EmailLog/EmailLog.php',
							$bwExtPath.'/EmailLog/EmailLog.i18n.php',							
							array('EmailUserComplete'),
							true
						 );
StubManager::createStub(	'UserSettingsChangedLog', 
							$bwExtPath.'/UserSettingsChangedLog/UserSettingsChangedLog.php',
							$bwExtPath.'/UserSettingsChangedLog/UserSettingsChangedLog.i18n.php',							
							array('UserSettingsChanged'),
							true
						 );

// ReCaptcha plug-in
//  Some customization required below.
#require('extensions/ReCaptcha/ReCaptcha.php');
#$recaptcha_public_key = '';
#$recaptcha_private_key = '';

// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
require('extensions/SimpleReplicator/SimpleReplicator.php');

## To enable image uploads, make sure the 'images' directory
## is writable, then set this to true:
$wgEnableUploads     = true;

## This must be set if the 'Updater' extension is to be functional
$wgFileExtensions[]  = "zip";

// Updater extension
#require('extensions/Updater/Updater.php');





  // for web site demo only.
  // %%%%%%%%%%%%%%%%%%%%%%%
if (defined('BIZZWIKIDEMO'))
{
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"LocalSettings.php","!read")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"AdminSettings.php","!read")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"LocalSettings.php","!raw")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"AdminSettings.php","!raw")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"LocalSettings.php","!viewsource")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"AdminSettings.php","!viewsource")] = true;  

	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"~","read")]   = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"~","browse")] = true; 
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"~","viewsource")] = true; 	
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"~","raw")] = true; 	

	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_BIZZWIKI,"~","read")]   = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_BIZZWIKI,"~","browse")] = true; 
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_BIZZWIKI,"~","viewsource")] = true; 	
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_BIZZWIKI,"~","raw")] = true; 	

	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_USER,"~","read")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_USER_TALK,"~","read")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_USER,"~","browse")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_USER_TALK,"~","browse")] = true;  
}

?>