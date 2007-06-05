<?php
/*
 * FileManager.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 * $LastChangedRevision$
 * 
 * Purpose:   This Mediawiki extension enables a user with the 'commitfile' right
 * ========   to edit files in the Mediawiki installation directory.  
 *
 * Features:
 * =========
 * 1) New right:      'readfile', 'commitfile'
 * 2) Logging
 * 3) New Namespace 'NS_FILESYSTEM'
 *
 * DEPENDANCY:  
 * ===========
 * 1) Extension 'ExtensionClass' (>=v1.92) 
 *
 * USAGE NOTES:
 * ============
 *
 * Tested Compatibility:  MW 1.8.2, 1.10
 * =====================
 *
 * History:
 * ========
 * 
 *
 * TODO:
 * =====
 * - internationalization
 * - add 'edit from filesystem' functionality: capability to 'reload' a file from the filesystem
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