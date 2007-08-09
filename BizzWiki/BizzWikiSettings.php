<?php
/***************************************************************************
	
	BizzWikiSettings.php
	
	$Id$

	TODO:
	=====
	- disable proxy caching functionality for 'file system' & BizzWiki namespaces
	

****************************************************************************/
// Define the base of BizzWiki
define('BIZZWIKI', '1_10_11alpha');
$bwVersion = BIZZWIKI;
$bwPath    = $IP.'/BizzWiki';
$bwExtPath = $IP.'/BizzWiki/extensions';

// Base class for multiple extensions
require('extensions/ExtensionClass.php');
require('extensions/RunPHP_class.php');
require('extensions/StubManager.php');

require( $bwExtPath.'/ParserPhase2/ParserPhase2_stub.php' );

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
	define('NS_API',		106);	
	define('NS_DIRECTORY',	108);		// extension DirectoryManager
	define('NS_EXTENSION',  110);		// for easy integration with MediaWiki.org
	
	// Add the new namespaces to the global variables
	$wgExtraNamespaces[NS_BIZZWIKI]   = 'Bizzwiki';
	$wgExtraNamespaces[NS_FILESYSTEM] = 'Filesystem';
	$wgExtraNamespaces[NS_INTERWIKI]  = 'Interwiki';
	$wgExtraNamespaces[NS_API]		  = 'Api';
	$wgExtraNamespaces[NS_DIRECTORY]  = 'Directory';	
	$wgExtraNamespaces[NS_EXTENSION]  = 'Extension';		

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
									NS_DIRECTORY,
									NS_EXTENSION,
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

require( $bwExtPath.'/RawRight/RawRight_stub.php');
require( $bwExtPath.'/ViewsourceRight/ViewsourceRight_stub.php');
require( $bwExtPath.'/WatchRight/WatchRight_stub.php');

require('extensions/MakeSysop/SpecialMakesysop.php');
require('extensions/DeSysop/SpecialDesysop.php');

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
										'oversight',
										'bot',             	// TODO
										'proxyunbannable', 	// TODO
										'trackback',		// TODO
										'unwatchedpages',	// TODO
										'readlog',  		// BizzWiki specific
										'siteupdate',		// BizzWiki specific
										'undelete',			// BizzWiki specific
										'userdetails',		// BizzWiki specific			
																				
										'skipcaptcha',		// ReCAPTCHA specific
										'makesysop',		// MakeSysop extension specific
										'desysop',			// DeSysop extension specific	
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
										'watch',		// BizzWiki specific
										'unwatch',		// BizzWiki specific
										'reload',		// BizzWiki specific
									);
									
// Critical permission system initialization -- DO NOT TOUCH
## {{
hnpClass::singleton()->addNamespaceIndependantRights( $bwNamespaceIndependantRights );
hnpClass::singleton()->addNamespaceDependantRights(   $bwNamespaceDependantRights );
## }}

	// Steward Group
$wgGroupPermissions['steward' ][hnpClass::buildPermissionKey("~","~","makesysop")]		= true;	
$wgGroupPermissions['steward' ][hnpClass::buildPermissionKey("~","~","userrights")]		= true;	

	// Bureaucrat Group
$wgGroupPermissions['bureaucrat' ][hnpClass::buildPermissionKey("~","~","makesysop")]	= true;	
$wgGroupPermissions['bureaucrat' ][hnpClass::buildPermissionKey("~","~","desysop")]		= true;	

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
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/usetchglog",	"browse")] = true;
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/watchlog",	"browse")] = true;
// replication related.
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/ftchrclog",	"browse")] = true;	// partner RC
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/schlog",		"browse")] = true;	// task scheduler
$wgGroupPermissions['sysop' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/usrloglog",	"browse")] = true;	// UserLoginLogoutLog

	// Anonymous
	// #########
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey("~","~","createaccount")] = true;

if (!defined('BIZZWIKIDEMO'))
{
	// remove access to some log entries.
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/newusers",	"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/commitfil",	"!browse")] = true; // FileManager extension
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/upload",		"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/delete",		"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/rights",		"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/move",		"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/block",		"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/emaillog",  	"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/usetchglog",	"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/watchlog",	"!browse")] = true;
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/usrloglog",	"!browse")] = true;	// UserLoginLogoutLog

	// replication related.
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/ftchrclog",	"!browse")] = true;	// partner RC
$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,"Log/schlog",		"!browse")] = true;	// task scheduler

#$wgGroupPermissions['*' ][hnpClass::buildPermissionKey("~","~","readlog")] = true;  // debugging
}

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
	#$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","raw")] = true;   
	#$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","viewsource")] = true;
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

$bwUserNamespaces = array_merge( $bwAnonymousNamespaces, $bwUserNamespaces);

	// Additional rights available to 'User'
	// #####################################
foreach( $bwUserNamespaces as $index => $bwx )
	{
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","read")] = true;
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","browse")] = true;
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","search")] = true;
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","watch")] = true;		
		$wgGroupPermissions['user' ][hnpClass::buildPermissionKey($bwx,"~","unwatch")] = true;				
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

	// Page Level Restrictions
	// %%%%%%%%%%%%%%%%%%%%%%%
require('extensions/PageRestrictions/PageRestrictions.php');

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
require( $bwExtPath.'/FileSystemSyntaxColoring/FileSystemSyntaxColoring_stub.php' );


// New User Logging
require('extensions/NewUserLog/Newuserlog.php');

// Show Redirect Page Text extension
require( $bwExtPath.'/ShowRedirectPageText/ShowRedirectPageText_stub.php' );

// Interwiki table management
require( $bwExtPath.'/InterWikiLinkManager/InterWikiLinkManager_stub.php' );

// Sidebar extended
require('extensions/SidebarEx/SidebarEx.php');

// Generic Syntax Highlighter
require( $bwExtPath.'/GeSHi/geshi_stub.php' );

// Enhanced Special Pages
require('extensions/SpecialPagesManager/SpecialPagesManager.php');
 // Use the following to customize the source path of the files.
 // TODO

// Form/Page related tools
require( $bwExtPath.'/SecureHTML/SecureHTML_stub.php' );
# SecureHTMLclass::enableExemptNamespaces = false; # turn off
# SecureHTMLclass::exemptNamespaces[] = NS_XYZ;    # to add namespaces to exemption list

require( $bwExtPath.'/SecureProperties/SecureProperties_stub.php' );

StubManager::createStub(	'FormProcClass', 
							$bwExtPath.'/FormProc/FormProc.php',
							null,
							array( 'UnknownAction' ),
							false,	// no need for logging support
							null,	// tags
							null,	// no parser functions
							null,	// no magic words
							null	// no namespace triggering							
						 );


StubManager::createStub(	'AutoLanguage', 
							$bwExtPath.'/AutoLanguage/AutoLanguage.php',
							null,							
							array('ArticleFromTitle'),
							false
						 );
// can be added through 'createStub2' method.
#AutoLanguageClass::$exemptNamespaces[] = NS_BIZZWIKI;
#AutoLanguageClass::$exemptNamespaces[] = NS_INTERWIKI;
#AutoLanguageClass::$exemptNamespaces[] = NS_FILESYSTEM;

	// Parser Extensions
	// %%%%%%%%%%%%%%%%%
// http://meta.wikimedia.org/wiki/ParserFunctions
require( 'extensions/ParserExt/ParserFunctions/ParserFunctions.php' );

// http://www.mediawiki.org/wiki/Extension:StringFunctions
require( 'extensions/ParserExt/StringFunctions/StringFunctions.php' );

StubManager::createStub(	'PermissionFunctions', 
							$bwExtPath.'/ParserExt/PermissionFunctions/PermissionFunctions.php',
							null,							
							array('EndParserPhase2'),
							false, // no need for logging support
							null,	// tags
							array( 'checkpermission' ),  //of parser function magic words,
							null
						 );

require('extensions/ParserExt/NamespaceFunctions/NamespaceFunctions.php');

	// Stubs
	//  Used for rare events handling.


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

StubManager::createStub(	'WatchLog', 
							$bwExtPath.'/WatchLog/WatchLog.php',
							$bwExtPath.'/WatchLog/WatchLog.i18n.php',							
							array('WatchArticleComplete', 'UnwatchArticleComplete' ),
							true
						 );

StubManager::createStub(	'RegexNamespaceContext', 
							$bwExtPath.'/RegexNamespaceContext/RegexNamespaceContext.php',
							null,							
							array( 'EditFormPreloadText', 'ParserAfterTidy', 'BeforePageDisplay' ),
							false
						 );


StubManager::createStub(	'RawPageTools', 
							$bwExtPath.'/RawPageTools/RawPageTools.php',
							null,							
							array( 'RawPageViewBeforeOutput' ),
							false
						 );


require( $bwExtPath.'/addScriptCss/AddScriptCss_stub.php' );

// Parser Functions
StubManager::createStub(	'ForeachFunctionClass', 
							$bwExtPath.'/ParserExt/ForeachFunction/ForeachFunction.php',
							null,							
							null,
							false, // no need for logging support
							null,	// tags
							array( 'foreachx','foreachy','forx' ),  //of parser function magic words,
							null
						 );


StubManager::createStub(	'MiscParserFunctions', 
							$bwExtPath.'/ParserExt/MiscParserFunctions/MiscParserFunctions.php',
							null,							
							null,
							false, // no need for logging support
							null,	// tags
							array( 'trim','nowikitext','gettagsection' ),  //of parser function magic words,
							null
						 );

StubManager::createStub(	'PageFunctionsClass', 
							$bwExtPath.'/ParserExt/PageFunctions/PageFunctions.php',
							null,
							array( 'PageVarGet', 'PageVarSet' ),
							false, // no need for logging support
							null,	// tags
							array( 'pagetitle','pagesubtitle','pageexists',
									'varset', 'varget',
									'varaset', 'varaget',
									'varcapset',
									'cshow',
									'noext'
									 ),  				//of parser function magic words,
							array( 'noclientcaching' )	// magic words
						 );

StubManager::createStub(	'ParserToolsClass', 
							$bwExtPath.'/ParserExt/ParserTools/ParserTools.php',
							null,							
							null,
							false, 						// no need for logging support
							array('noparsercaching'),	// tags
							null,
							null
						 );

StubManager::createStub(	'RegexToolsClass', 
							$bwExtPath.'/ParserExt/RegexTools/RegexTools.php',
							null,							
							null,
							false, 						// no need for logging support
							null,						// tags
							array('regx_vars', 'regx'), // parser Functions
							null
						 );

StubManager::createStub(	'DocProcClass', 
							$bwExtPath.'/DocProc/DocProc.php',
							null,							
							null,
							false, 					// no need for logging support
							array('docproc'),		// tags
							null, 					// parser Functions
							null
						 );

StubManager::createStub(	'ScriptingToolsClass', 
							$bwExtPath.'/ScriptingTools/ScriptingTools.php',
							null,					// i18n file			
							array('ArticleSave', 'ParserAfterTidy' ),	// hooks
							false, 					// no need for logging support
							null,					// tags
							array('epropset','epropset2'),	// parser Functions
							null
						 );

StubManager::createStub(	'RecentChangesManager', 
							$bwExtPath.'/RecentChangesManager/RecentChangesManager.php',
							null,					// i18n file			
							array('ArticleEditUpdatesDeleteFromRecentchanges'),	// hooks
							false, 					// no need for logging support
							null,					// tags
							null,					// parser Functions
							null
						 );

StubManager::createStub(	'ImageLinkClass', 
							$bwExtPath.'/ImageLink/ImageLink.php',
							null,					// i18n file			
							array('ParserAfterTidy'),	// hooks
							false, 					// no need for logging support
							null,					// tags
							array('imagelink'),	// parser Functions
							null
						 );

StubManager::createStub(	'PageAfterAndBefore', 
							$bwExtPath.'/PageAfterAndBefore/PageAfterAndBefore.php',
							null,					// i18n file			
							null,					// hooks
							false, 					// no need for logging support
							null,					// tags
							array('pagebefore', 'pageafter', 'firstpage', 'lastpage' ),	// parser Functions
							null
						 );

StubManager::createStub(	'NewUserEmailNotification', 
							$bwExtPath.'/NewUserEmailNotification/NewUserEmailNotification.php',
							$bwExtPath.'/NewUserEmailNotification/NewUserEmailNotification.i18n.php',							
							array('AddNewAccount'),
							false
						 );

StubManager::createStub(	'UserLoginLogoutLog', 
							$bwExtPath.'/UserLoginLogoutLog/UserLoginLogoutLog.php',
							$bwExtPath.'/UserLoginLogoutLog/UserLoginLogoutLog.i18n.php',							
							array(	'UserLoginForm', 'UserLoginComplete', 
									'UserLogout', 'UserLogoutComplete',
									'SpecialVersionExtensionTypes' ),
							true
						 );

StubManager::createStub2(	array(	'class' 		=> 'SkinTools', 
									'classfilename'	=> $bwExtPath.'/ParserExt/SkinTools/SkinTools.php',
									'hooks'			=> array( 'SkinTemplateTabs' ),
									'mgs'			=> array( 'clearactions', 'removeactions', 'addaction' ),
								)
						);

StubManager::createStub2(	array(	'class' 		=> 'UserTools', 
									'classfilename'	=> $bwExtPath.'/ParserExt/UserTools/UserTools.php',
									'mgs'			=> array( 'cusergetoption', 'usergetoption' ),
								)
						);

require('extensions/VirtualPage/VirtualPageSwitch.php');
require('extensions/AutoRedirect/AutoRedirect_stub.php');
require('extensions/DirectoryManager/DirectoryManager_stub.php');
/* TODO
require('extensions/DPL/DynamicPageList2.php');

#Adjust the following parameters according to user permissions:
$wgDPL2AllowedNamespaces = array(

								);
$wgDPL2Options['namespace'] 
$wgDPL2Options['notnamespace'] 
*/

// ReCaptcha plug-in
//  Some customization required below.
//  Depending on your setup, it might be more appropriate to set these keys
//  in your LocalSettings.php
#require('extensions/ReCaptcha/ReCaptcha.php');
#$recaptcha_public_key = '';
#$recaptcha_private_key = '';

// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//                           TASK SCHEDULING FUNCTIONALITY
// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
/*
StubManager::createStub(	'TaskScheduler', 
							$bwExtPath.'/TaskScheduler/TaskScheduler.php',
							$bwExtPath.'/TaskScheduler/TaskScheduler.i18n.php',							
							array( 'SpecialVersionExtensionTypes','ClockTickEvent' ), // created by 'ClockTick' extension
							true // logging included
						 );
*/
// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//                           REPLICATION FUNCTIONALITY
//                             *** EXPERIMENTAL ***
// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
# Customize 'clocktick.php' as required
#require('extensions/SimpleReplicator/SimpleReplicator.php');
// Customize the following parameters
#PartnerMachine::$url		= 'http://bizzwiki.org';
#PartnerMachine::$port		= 80; 	//HTTP/TCP port
#PartnerMachine::$timeout	= 15;	//Timeout (in seconds) for request to partner machine

// Enable this extension if you require locally generated 'ClockTickEvent'
// A local 'cron' job must be setup to request a page from the local web server
// with an 'action=ping' command.
#require('extensions/ClockTick/ClockTick.php');



## To enable image uploads, make sure the 'images' directory
## is writable, then set this to true:
$wgEnableUploads     = true;

## This must be set if the 'Updater' extension is to be functional
#$wgFileExtensions[]  = "zip";
$wgFileExtensions[]  = "png";

// Updater extension (ongoing)
#require('extensions/Updater/Updater.php');


// The API functionality requires this function defined in MW1.11
if (!defined('wfScript'))
{
	function wfScript( $script = 'index' ) 
	{
		global $wgScriptPath, $wgScriptExtension;
		return "{$wgScriptPath}/{$script}{$wgScriptExtension}";
	}
}	



  // for web site demo only.
  // %%%%%%%%%%%%%%%%%%%%%%%
if (defined('BIZZWIKIDEMO'))
{
	require('extensions/ReCaptcha/ReCaptcha.php');
	
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"LocalSettings.php","!read")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"AdminSettings.php","!read")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"LocalSettings.php","!raw")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"AdminSettings.php","!raw")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"LocalSettings.php","!viewsource")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"AdminSettings.php","!viewsource")] = true;  

	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"~",	"read")]   = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"~",	"browse")] = true; 
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"~",	"viewsource")] = true; 	
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_FILESYSTEM,"~",	"raw")] = true; 	

	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_DIRECTORY,"~",	"read")]   = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_DIRECTORY,"~",	"browse")] = true; 
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_DIRECTORY,"~",	"viewsource")] = true; 	
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_DIRECTORY,"~",	"raw")] = true; 	

	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_BIZZWIKI,	"~",	"read")]   = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_BIZZWIKI,	"~",	"browse")] = true; 
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_BIZZWIKI,	"~",	"viewsource")] = true; 	
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_BIZZWIKI,	"~",	"raw")] = true; 	

	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_USER,		"~/details",	"!read")] = true; 
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_USER,		"~",			"read")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_USER,		"~",			"viewsource")] = true;  	
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_USER_TALK,"~",			"read")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_USER,		"~",			"browse")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_USER_TALK,"~",			"browse")] = true;  
	$wgGroupPermissions['*' ][hnpClass::buildPermissionKey(NS_SPECIAL,	"Log/~",		"browse")] = true;

 

	$bwAnonymousNamespaces = array( NS_MAIN, NS_TALK,
									NS_PROJECT, NS_PROJECT_TALK,
									NS_TEMPLATE,NS_TEMPLATE_TALK,
									NS_CATEGORY, NS_CATEGORY_TALK,
									NS_HELP, NS_HELP_TALK,
									NS_SPECIAL,
									NS_INTERWIKI // BizzWiki specific
									); 

	foreach( $bwAnonymousNamespaces as $index => $bwx )
	{	
		$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","read")] = true;   
		$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","browse")] = true;
		$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","search")] = true;   		
		$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","raw")] = true;   
		$wgGroupPermissions['*' ][hnpClass::buildPermissionKey($bwx,"~","viewsource")] = true;
	}
	
}

function bwVarDump( $var ) 
{
	return str_replace("\n","<br />\n", var_export( $var, true ) . "\n");
}
