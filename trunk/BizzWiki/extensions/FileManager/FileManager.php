<?php
/*
 * FileManager.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 * 
 * Purpose:   This Mediawiki extension enables a user with the 'commitfile' right
 * ========   to edit files in the Mediawiki installation directory.  
 *
 * Features:
 * =========
 * 0) Can be used independantly of BizzWiki environment 
 * 1) New right:      'readfile', 'commitfile'
 * 2) Logging
 * 3) New Namespace 'NS_FILESYSTEM'
 * 4) Support for titles beginning with small caps; need the title to be prefixed with '/'
 *    e.g. to have access to 'includes/Setup.php' just reference the title 'Filesystem:/includes/Setup.php'
 *
 * DEPENDANCY:  
 * ===========
 * 1) Extension 'ExtensionClass' (>=v1.92) 
 *
 * USAGE NOTES:
 * ============
 *
 * Tested Compatibility:  MW 1.10
 * =====================
 *

== History ==
* fixed for 'wgCapitalLinks' 
* fixed for suppressing PHP error messages on file_get_contents
* fixed logging messages

== TODO ==
* internationalization
* add 'edit from filesystem' functionality: capability to 'reload' a file from the filesystem
* enhance 'logging' through $type etc.
*  

 */

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: FileManager extension will not work!';	
else
{
	require( 'FileManager.i18n.php' );
	require( "FileManagerClass.php" );
	FileManagerClass::singleton();
}
?>