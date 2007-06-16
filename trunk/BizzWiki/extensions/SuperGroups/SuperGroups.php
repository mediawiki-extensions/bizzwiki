<?php
/*
 * SuperGroups.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 * $LastChangedRevision: 202 $
 *
 * Purpose:   
 * ========
 * 
 * Features:
 * =========
 * 1) A block of 256 namespaces is reserved for each SuperGroup
 *
 * 2) SuperGroup 'id' => namespaces set offset = sg_id * 
 *
 * Tested Compatibility: 
 *
 * DEPENDANCY: ExtensionClass (>=v1.8)
 *
 * IMPLEMENTATION:
 * ===============
 * -- The SuperGroup identifier space is an integer(11)
 * -- The normal namespaces set starts at 0  (well, technically
 *    there are also the -1 and -2 namespaces)
 * 
 * LocalSettings.php:
 * ==================
 * require("extensions/ExtensionClass.php");
 * require("extensions/SuperGroups/SuperGroups.php");
 *
 * CONFIGURATION EXAMPLE:
 * ======================
 
	 $sgExtraNamespaces = array (
	 100 => array( 'name' => "Admin",     'id' => 'NS_ADMIN'),
	 102 => array( 'name' => "Blog",      'id' => 'NS_BLOG' ), 
	 103 => array( 'name' => "Blog_talk", 'id' => 'NS_BLOG_TALK' ),
	 104 => array( 'name' => "Contact",   'id' => 'NS_CONTACT' ),
	 106 => array( 'name' => "Test",      'id' => 'NS_TEST' ),
	 );

 *
 * HISTORY:	v1.0
 *
 */
require(dirname( __FILE__ ) . '/SuperGroupsClass.php');

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: SuperGroups extension will not work!';	
else
	SuperGroupsClass::singleton();
	
$wgExtensionCredits[SuperGroupsClass::thisType][] = array( 
	'name'        => SuperGroupsClass::thisName,
	'version'     => 'v1.0 $LastChangedRevision: 202 $',
	'author'      => 'Jean-Lou Dupont', 
	'url'         => 'http://www.bluecortex.com',
	'description' => 'Extension status: '
);

?>