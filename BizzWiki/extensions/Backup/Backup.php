<?php
/*
<file>
	<name>Backup.php</name>
	<id>$Id$</id>
	<package>Extension.Backup</package>
</file>
*/
// <source lang=php>


if (class_exists('StubManager'))
{
$backupExt = array();

// Exclude the following namespaces by default.
if (defined('NS_FILESYSTEM'))	$backupExt[] = NS_FILESYSTEM;
if (defined('NS_DIRECTORY'))	$backupExt[] = NS_DIRECTORY;
if (defined('NS_EXT'))			$backupExt[] = NS_EXT;

StubManager::createStub2(	array(	'class' 		=> 'Backup', 
									'classfilename'	=> $bwExtPath.'/Backup/Backup.body.php',
									'hooks'			=> array(	'RecentChange_save',
																'ArticleSaveComplete',
																'ArticleDeleteComplete',
																'ArticleDelete',
																'SpecialMovepageAfterMove',
																'ArticleProtectComplete',
																'ImageDoDeleteBegin',  // supported through [[Extension:ImagePageEx]]
																#'hAddNewAccount',
																#'hUploadComplete',
															),
									// exclude the following namespaces
									'enss'			=> $backupExt
								)
						);
}
else
{
	echo "Extension:Backup requires Extension:StubManager";
}
//</source>
