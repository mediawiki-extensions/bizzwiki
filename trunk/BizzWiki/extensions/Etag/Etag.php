<?php
/*
<!--<wikitext>-->
 <file>
  <name>Etag.php</name>
  <version>$Id$</version>
  <package>Extension.Etag</package>
 </file>
<!--</wikitext>-->
*/
// <source lang=php>

if (class_exists('StubManager'))
{
	StubManager::createStub2(	array(	'class' 		=> 'Etag', 
										'classfilename'	=> dirname(__FILE__).'/Etag.body.php',
										'tags' 			=> array( 'etag' )
									)
							);
	$wgExtensionCredits['parser'][] = array( 
		'name'    		=> 'Etag',
		'version'		=> StubManager::getRevisionId('$Id$'),
		'author'		=> 'Jean-Lou Dupont',
		'url'			=> 'http://www.mediawiki.org/wiki/Extension:Backup',	
		'description' 	=> "Supports the <etag> tag.", 
	);
}
else
	echo 'Extension:Etag <b>requires</b> [[Extension:StubManager]]';
//</source>