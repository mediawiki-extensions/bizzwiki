<?php
/*
<!--<wikitext>-->
 <file>
  <name>ImagePageEx.php</name>
  <version>$Id$</version>
  <package>ImagePageEx</package>
 </file>
<!--</wikitext>-->
*/
//<source lang=php>

// Only tested on MW 1.10 for now.

if (version_compare( $wgVersion, "1.10", '<' ))
{
	echo 'Extension:ImagePageEx requires MW version >= 1.10';
}
elseif (version_compare( $wgVersion, "1.10.1", '>' ))
{
	echo 'Extension:ImagePageEx only tested on MW version >= 1.10 and < 1.11';	
}
else
{
	// only hook-up if the above conditions are met.
	require_once( $IP.'/includes/ImagePage.php' );
	require( 'ImagePageEx.body.php' );	
	$wgExtensionFunctions[] = create_function('', 'return ImagePageEx::setup();');
}
//</source>