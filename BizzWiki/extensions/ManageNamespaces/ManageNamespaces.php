<?php
/*
<!--<wikitext>-->
 <file>
  <name>ManageNamespaces.php</name>
  <version>$Id$</version>
  <package>Extension.ManageNamespaces</package>
 </file>
<!--</wikitext>-->
*/
// <source lang=php>

$wgAutoloadClasses['ManageNamespaces'] = dirname(__FILE__).'/ManageNamespaces.body.php';
$wgSpecialPages['ManageNamespaces'] = 'ManageNamespaces';

$wgExtensionCredits['specialpage'][] = array( 
	'name'    		=> 'ManageNamespaces',
	'version'		=> '$Id$',
	'author'		=> 'Jean-Lou Dupont',
	'url'			=> 'http://www.mediawiki.org/wiki/Extension:ManageNamespaces',	
	'description' 	=> "Provides a special page to add/remove namespaces.".
						// help the user a bit.
						" Ajax support is: ".($wgUseAjax?'enabled':'disabled').'.'
);

// we need at least the log related messages to be loaded.
require( 'ManageNamespaces.i18.log.php' );

// now the Ajax functions

//</source>