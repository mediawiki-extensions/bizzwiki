<?php
/**
 * $Id$
 *
 * TODO
 *
 */

/**
 *
 */
# Internationalisation file
require_once( dirname(__FILE__) . '/SpecialMakesysop.i18n.php' );

// Set groups to the appropriate sysop/bureaucrat structure:
// * Steward can do 'full' work (makesysop && userrights)
// * Bureaucrat can only do limited work (makesysop)
$wgGroupPermissions['steward'   ]['makesysop' ] = true;
$wgGroupPermissions['steward'   ]['userrights'] = true;
$wgGroupPermissions['bureaucrat']['makesysop' ] = true;
$wgGroupPermissions['bureaucrat']['userrights'] = false;

$wgAvailableRights[] = 'makesysop';

/**
 * Quick hack for clusters with multiple master servers; if an alternate
 * is listed for the requested database, a connection to it will be opened
 * instead of to the current wiki's regular master server.
 *
 * Requires that the other server be accessible by network, with the same
 * username/password as the primary.
 *
 * eg $wgAlternateMaster['enwiki'] = 'ariel';
 */
$wgAlternateMaster = array();

# Register special page
if ( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}
extAddSpecialPage( dirname(__FILE__) . '/SpecialMakesysop_body.php', 'Makesysop', 'MakeSysopPage' );

function extAddSpecialPage( $file, $name, $params ) {
		global $wgSpecialPages, $wgAutoloadClasses;
		if ( !is_array( $params ) ) {
			$className = $params;
		} else {
			$className = $params[0];
		}
		$wgSpecialPages[$name] = $params;
		$wgAutoloadClasses[$className] = $file;
	}
?>
