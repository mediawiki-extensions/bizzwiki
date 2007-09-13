<?php
/*
<file>
	<name>AmazonS3.php</name>
	<id>$Id$</id>
	<package>Extension.AmazonS3</package>
</file>
*/
// <source lang=php>

// verify that the 'curl' module is available... 
// hopefully the right version also.
if (defined('CURLOPT_URL'))
{
	$wgAutoloadClasses['AmazonS3'] = dirname(__FILE__).'/AmazonS3.body.php';
	
	$wgExtensionCredits['other'][] = array( 
		'name'    		=> 'AmazonS3',
		'version'		=> '$Id$',
		'author'		=> 'Jean-Lou Dupont',
		'url'			=> 'http://www.mediawiki.org/wiki/Extension:AmazonS3',	
		'description' 	=> "Provides the base class 'AmazonS3'.", 
	);
}
else
	echo 'Extension:AmazonS3 <b>requires</b> PHP CURL module.';
// </source>