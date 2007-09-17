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

#$wgAutoloadClasses['ManageNamespaces'] = dirname(__FILE__).'/ManageNamespaces.body.php';

// we need SpecialPageHelperClass
#if (class_exists('SpecialPageHelperClass'))
#	$wgSpecialPages['ManageNamespaces'] = 'ManageNamespaces';
#else
#	echo 'Extension:ManageNamespaces <b>requires</b> Extension:SpecialHelperClass';
if (class_exists('StubManager'))
{
	$wgExtensionCredits['parser'][] = array( 
		'name'    		=> 'ManageNamespaces',
		'version'		=> StubManager::getRevisionId( '$Id$' ),
		'author'		=> 'Jean-Lou Dupont',
		'url'			=> 'http://www.mediawiki.org/wiki/Extension:ManageNamespaces',	
		'description' 	=> "Provides a special page to add/remove namespaces. "
	);
	
	StubManager::createStub2(	array(	'class' 		=> 'ManageNamespaces', 
										'classfilename'	=> dirname(__FILE__).'/ManageNamespaces.body.php',
										'i18n'			=> dirname(__FILE__).'/ManageNamespaces.i18n.php',
										'hooks'			=> array( 'ParserAfterTidy' ),
										'mgs'			=> array( 'mns' )
									)
							);
	
}
else
	echo "Extension:ManageNamespaces <b>requires</b> Extension:StubManager\n";
	
// Now include the managed namespaces in question
@include( 'ManageNamespaces.namespaces.php' );

// Is the Namespace class defined yet?
if (!class_exists('Namespace') && !empty( $bwManagedNamespaces ))
	require($IP.'/includes/Namespace.php');

// Go through all the managed namespaces
if (!empty( $bwManagedNamespaces ))
	foreach( $bwManagedNamespaces as $index => $name )
	{
		// add the managed namespaces to the primary tables
		$wgCanonicalNamespaceNames[$index] = $name;
		$wgExtraNamespaces[$index] = $name;
				
		// Add subpage support for each of the managed namespaces		
		$wgNamespacesWithSubpages[ $name ] = true;
	}

//</source>