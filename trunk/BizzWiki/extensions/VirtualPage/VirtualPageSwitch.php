<?php
/*<!--<wikitext>-->
{{Extension
|name        = VirtualPageSwitch
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/VirtualPage/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
== Purpose==
This extension provides the hook 'VirtualPage'. Read more details in 'VirtualPage.php'.

== Features ==
* Low overhead: the VirtualPage functionality is only spawned when required
* Does not handle 'NS_MEDIA', 'NS_IMAGE' and 'NS_CATEGORY' namespaces

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Download the two files of this extension (VirtualPageSwitch.php & VirtualPage.php)
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/VirtualPage/VirtualPage.php');
</source>
* Make sure to have 'VirtualPage.php' and 'VirtualPageSwitch.php' in the same directory.
== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>-->*/
//<source lang=php>
$wgHooks['ArticleFromTitle'][] = 'bwVirtualPageSwitchInit';

StubManager::createStub2(	array(	'class' 		=> 'VirtualPage', 
									'classfilename'	=> $bwExtPath.'/VirtualPage/VirtualPage.php',
									'hooks'			=> array( 'VirtualPage' )
								)
						);

function bwVirtualPageSwitchInit( &$title, &$article )
{
	// let mediawiki handle those.
	$ns = $title->getNamespace();
	if (NS_MEDIA==$ns || NS_CATEGORY==$ns || NS_IMAGE==$ns)
		return true;
	
	$article = new Article( $title );
	
	// let mediawiki handle the articles that already exist
	if ( $article->getID() != 0 )
		return true;
	
	// now, we are interested in non-existing articles!
	wfRunHooks( 'VirtualPage', array( &$title, &$article) );
	
	return true;
}
//</source>