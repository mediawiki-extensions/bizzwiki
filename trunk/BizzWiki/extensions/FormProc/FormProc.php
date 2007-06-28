<?php
/*
 * FormProc.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont
 * $Id$
 * 
 * Purpose:   
 * ========   
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

$wgAutoloadClasses['Updater'] = dirname(__FILE__) . "/UpdaterClass.php" ;
$wgSpecialPages['Updater'] = 'Updater';

$wgExtensionCredits['specialpage'][] = array( 
	'name'        => 'Updater', 
	'version'     => '$Id$',
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Updates a Mediawiki installation through "wget"'
);

?>