<?php
/*
<file>
	<name>BackupS3.php</name>
	<id>$Id$</id>
	<package>Extension.BackupS3</package>
</file>
*/
// <source lang=php>


if (class_exists('StubManager'))
{
	StubManager::createStub2(	array(	'class' 		=> 'BackupS3',
										'i18n'			=> dirname(__FILE__).'/BackupS3.i18n.php',
										'classfilename'	=> dirname(__FILE__).'/BackupS3.body.php',
										'hooks'			=> array( 'Backup', 'SpecialVersionExtensionTypes' ),
									)
							);
							
	// load the configuration class.
	require('BackupS3.config.php');
	
	// Register the 'special page'
	$wgAutoloadClasses['BackupS3SP'] = dirname(__FILE__).'/BackupS3.specialpage.php';
	$wgSpecialPages['BackupS3'] = 'BackupS3SP';
	
	try
	{
		if (!StubManager::isExtensionRegistered('Backup'))
			echo 'Extension:BackupS3 <b>requires</b> Extension:Backup';
		
	} catch(Exception $e)
	{
		echo "Extension:Backup requires Extension:StubManager of version >= 757";
	}
}
else
	echo "Extension:Backup requires Extension:StubManager";
//</source>
