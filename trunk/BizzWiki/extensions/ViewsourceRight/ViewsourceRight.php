<?php
/*<wikitext>
{{Extension
|name        = ViewsourceRight
|status      = stable
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ViewsourceRight/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
== Purpose ==
This extension adds a 'viewsource' right. 
Only the users with the 'viewsource' permission can 'view' an article's source wikitext.

== FEATURES ==
* Can be used independantly of BizzWiki environment 
* No mediawiki installation source level changes

== DEPENDANCIES ==
* [[Extension:StubManager]]
* Hierarchical Namespace Permissions extension

== Installation ==
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'ViewsourceRight', 
							'extensions/ViewsourceRight/ViewsourceRight.php',
							null,
							array( 'UpdateExtensionCredits','AlternateEdit', 'SkinTemplateTabs' ),
							false,	// no need for logging support
							null,	// tags
							null,	// no parser functions
							null	// no magic words
						 );
</source>

== HISTORY ==
* Corrected missing 'return true' statement in hook.
* Removed 'view source' tab when permission is not granted to user.
* Moved Singleton invocation to end of file to accomodate some PHP versions
* Removed dependency on ExtensionClass
* Made 'stub'-enabled

</wikitext>*/

	
global $wgExtensionCredits;
$wgExtensionCredits[ViewsourceRight::thisType][] = array( 
	'name'    		=> ViewsourceRight::thisName, 
	'version'		=> StubManager::getRevisionId( '$Id$' ),
	'author'		=> 'Jean-Lou Dupont', 
	'description'	=> "Enforces 'viewsource' right. Status: ",
	'url'			=> StubManager::getFullUrl(__FILE__),			
);

class ViewsourceRight
{
	const thisName = 'ViewsourceRight';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	
	// Our class defines magic words: tell it to our helper class.
	public function __construct() {} 

	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	// setup of this hook occurs in 'ExtensionClass' base class.
	{
		global $wgExtensionCredits;

		if (class_exists('hnpClass'))
			$result = '<b>operational</b>';
		else
			$result = '<b>not operational: missing Hierarchical Namespace Permissions extension </b>';
		
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if ($el['name']==self::thisName)
				$el['description'].=$result;
				
		return true; // continue hook-chain.
	}
	
	public function hAlternateEdit( &$ep )
	{
		global $wgUser;
		
		$title =  $ep->mTitle;
		$new   = !$title->exists();		
		$save  =  $ep->save;
		
		if (!$new && !$save)
		{
			if ( ! $title->userCanEdit() ) 
			{
				$ns    = $title->getNamespace();
				$titre = $title->mDbkeyform;
				
				if (!$wgUser->isAllowed('viewsource'))
				{
					global $wgOut;
				
					$skin = $wgUser->getSkin();
					$wgOut->setPageTitle( wfMsg( 'viewsource' ) );
					$wgOut->setSubtitle( wfMsg( 'viewsourcefor', $skin->makeKnownLinkObj( $title ) ) );
					$wgOut->addWikiText( wfMsg( 'badaccess' ) );
					
					return false; // stop normal processing flow.
				}
			}
		}
		// if the user can't 'edit',
		// the normal processing flow will catch this.
		return true;		
	}

	public function hSkinTemplateTabs( &$st , &$content_actions )
	{
		$ns    = $st->mTitle->getNamespace();
		$titre = $st->mTitle->mDbkeyform;
		
		global $wgUser;
		global $action;

		// if the user can 'edit' the title, don't bother with 'viewsource' then.
		if ($st->mTitle->userCan('edit') ) return true;

		if ($wgUser->isAllowed( 'viewsource') )
		{
			$content_actions['viewsource'] = array(
				'class' => ($action == 'edit') ? 'selected' : false,
				'text' => wfMsg('viewsource'),
				'href' => $st->mTitle->getLocalUrl( $st->editUrlOptions() )
			);
		}
		else 
			unset( $content_actions['viewsource'] );

		return true;
	}
} // end class definition.
?>