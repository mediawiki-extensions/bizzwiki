<?php
/*
<!--<wikitext>-->
 <file>
  <name>TagToTemplate.php</name>
  <version>$Id$</version>
  <package>Extension.TagToTemplate</package>
 </file>
<!--</wikitext>-->
*/
// <source lang=php>

if (class_exists('StubManager'))
{
	StubManager::createStub2(	array(	'class' 		=> 'TagToTemplate', 
										'classfilename'	=> dirname(__FILE__).'/TagToTemplate.body.php',
										'hooks'			=> array( 'ParserBeforeStrip' ),
										'mgs'			=> array( 'tag_to_template' )
									)
							);

	$wgExtensionCredits['hook'][] = array( 
		'name'    		=> 'TagToTemplate',
		'version'		=> StubManager::getRevisionId('$Id$'),
		'author'		=> 'Jean-Lou Dupont',
		'url'			=> 'http://www.mediawiki.org/wiki/Extension:TagToTemplate',	
		'description' 	=> "Provides tag markup substitution for a configured template.", 
	);
}
else
	echo 'Extension:TagToTemplate <b>requires</b> [[Extension:StubManager]]'."\n";
//</source>