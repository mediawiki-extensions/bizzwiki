<?php
/*
 * FormProc.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont
 * $Id: PostProc.php 214 2007-06-29 02:38:54Z jeanlou.dupont $
 * 
 * Purpose:   Handles 'action=formsubmit' posts to a Mediawiki page. The underlying page
 * ========   must contain 'php' code. The extension follows 'redirect' page to fetch
 *            the required code if necessary.
 *    
 *            
 *
 * Features:
 * =========
 * 0) Can be used independantly of BizzWiki environment 
 *
 * DEPENDANCY:  
 * ===========
 * 1) ExtensionClass extension
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
	echo 'ExtensionClass missing: FormProc extension will not work!';	
elseif (!class_exists('runphpClass') )
	echo 'RunPHP Class missing: FormProc extension will not work!';
else
{
	require( "FormProcClass.php" );
	FormProcClass::singleton();
}
?>