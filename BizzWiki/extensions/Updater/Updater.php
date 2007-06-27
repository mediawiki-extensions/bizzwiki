<?php
/*
 * Updater.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont
 * $Id$
 * 
 * Purpose:   This Mediawiki extension enables a user with the 'sysop' right
 * ========   to update a Mediawiki installation from a remote 'http' accessible
 *            code repository.
 *
 * Features:
 * =========
 * 0) Can be used independantly of BizzWiki environment 
 *
 * DEPENDANCY:  
 * ===========
 * 1) The system command 'wget' must be accessible
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
global $wgAutoloadClasses;
$wgAutoloadClasses['Updater'] = dirname(__FILE__) . "/UpdaterClass.php" ;
$wgSpecialPages['Updater'] = 'Updater';

$wgExtensionCredits['specialpage'][] = array( 
	'name'        => 'Updater', 
	'version'     => '$Id$',
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Updates a Mediawiki installation with http accessible files'
);

?>