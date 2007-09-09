<?php
/*
<file>
	<name>Backup.php</name>
	<id>$Id$</id>
</file>
*/
// <source lang=php>

StubManager::createStub2(	array(	'class' 		=> 'backup', 
									'classfilename'	=> $bwExtPath.'/Backup/Backup.body.php',
									'hooks'			=> array(	'RecentChange_save',
																'ArticleSaveComplete',
																'ArticleDeleteComplete',
																'ArticleDelete',
																'SpecialMovepageAfterMove',
																'ArticleProtectComplete',
																#'hAddNewAccount',
																#'hUploadComplete',
															),
									// exclude the following namespaces
									'enss'			=> array( NS_FILESYSTEM, NS_DIRECTORY )
								)
						);

//</source>
