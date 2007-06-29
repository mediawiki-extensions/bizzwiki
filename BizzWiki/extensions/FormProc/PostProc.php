<?php
/*
 * PostProc.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont
 * $Id$
 * 
 * Purpose:   Handles 'action=submit' posts to a Mediawiki page. The underlying page
 * ========   must contain 'php' code. The extension follows 'redirect' page to fetch
 *            the required code.
 *    
 *            
 *
 * Features:
 * =========
 * 0) Can be used independantly of BizzWiki environment 
 *
 * DEPENDANCY:  
 * ===========
 * 
 *
 *
 * USAGE NOTES:
 * ============
 *
 * Tested Compatibility:  MW 1.10
 * =====================
 *
 * History:
 * ========
 *
 * TODO:
 * =====
 * - 
 *
 */
// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: PostProc extension will not work!';	
else
{
	require( 'PostProc.i18n.php' );
	require( "PostProcClass.php" );
	PostProcClass::singleton();
}
?>