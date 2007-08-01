<?php

/**
 * Installation script for the 'FetchPartnerRC' extension
 *
 * @author Jean-Lou Dupont
 * $Id$
 */

echo "\n\nFetchPartnerRC database table setup\n\n";

# RECURSE DOWN TO FIND '/maintenance' directory
$maint = $dir = dirname( __FILE__ );
$pieces = explode( DIRECTORY_SEPARATOR, $dir );
$found = false;
for ($i=0; count($pieces); $i++ )
{
	echo "\n Locating file: ".$maint."/maintenance/commandLine.inc";
	
	if( is_file( $maint . '/maintenance/commandLine.inc' ) ) 
	{
		echo " found! \n";		
		require_once( $maint . '/maintenance/commandLine.inc' );
		$found = true;
		break;
	}
	unset( $pieces[count($pieces)-1] );
	$maint = implode( DIRECTORY_SEPARATOR, $pieces );
}
if (!$found)
{	
	"Maintenance directory not found! Installation failed. \n";
	die(1);
}

# Set up some other paths
$sql = dirname( __FILE__ ) . '/FetchPartnerRC.sql';

# Whine if we don't have appropriate credentials to hand
if( !isset( $wgDBadminuser ) || !isset( $wgDBadminpassword ) ) {
	echo( "No superuser credentials could be found. Please provide the details\n" );
	echo( "of a user with appropriate permissions to update the database. See\n" );
	echo( "AdminSettings.sample for more details.\n\n" );
	die( 1 );
}

# Get a connection
$dbclass = $wgDBtype == 'MySql'
			? 'Database'
			: 'Database' . ucfirst( strtolower( $wgDBtype ) );
$dbc = new $dbclass;

echo "$wgDBserver, $wgDBadminuser, $wgDBadminpassword, $wgDBname \n";

$dba =& $dbc->newFromParams( $wgDBserver, $wgDBadminuser, $wgDBadminpassword, $wgDBname, 1 );

# Check we're connected
if( !$dba->isOpen() ) {
	echo( "A connection to the database could not be established.\n\n" );
	die( 1 );
}

# Do nothing if the table exists
if( !$dba->tableExists( 'recentchanges_partner' ) ) {
	if( $dba->sourceFile( $sql ) ) {
		echo( "The table has been set up correctly.\n" );
	}
} else {
	echo( "The table already exists. No action was taken.\n" );
}

# Close the connection
$dba->close();
echo( "\n" );

?>