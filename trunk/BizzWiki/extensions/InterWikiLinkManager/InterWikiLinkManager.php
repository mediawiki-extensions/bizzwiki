<?php
/*
 * InterWikiLinkManager.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 * 
 * Purpose:   This Mediawiki extension enables a user with the appropriate rights
 * ========   to manage the InterWiki Links of the database.
 *
 * Features:
 * =========
 * 0) Can be used independantly of BizzWiki environment 
 * 1) Rights policing
 * 2) Logging
 * 3) New Namespace 'NS_INTERWIKI'
 *
 * DEPENDANCY:  
 * ===========
 * 1) Extension 'ExtensionClass' 
 *
 * USAGE NOTES:
 * ============
 * 1) Use "Interwiki:Main Page" to manage the interwiki links
 * 2) Use the magic word {{#iwl: prefix | URI | local flag | transclusion flag }}
 * 3) Appropriate rights management should be in place (e.g. Hierarchical Namespace Permissions extension)
 *
 * INSTALLATION:
 * =============
 * 1) Create NS_INTERWIKI namespace in LocalSettings.php
 * 2) Require ExtensionClass.php
 * 3) Require InterWikiLinkManager.php
 * 4) Set Permissions
 *
 * Tested Compatibility:  MW 1.10
 * =====================
 *
 * History:
 * ========
 *
 * TODO:
 * =====
 * - Add validation
 *
 */

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
{
	echo 'ExtensionClass missing: InterWikiLinkManager extension will not work!';	
}
elseif (defined('NS_INTERWIKI'))
{
	#require( 'InterWikiLinkManager.i18n.php' );
	require( "InterWikiLinkManagerClass.php" );
	InterWikiLinkManagerClass::singleton();
}
else
{
	echo "InterWikiLinkManager: NS_INTERWIKI namespace not defined in LocalSettings.php";
}
?>