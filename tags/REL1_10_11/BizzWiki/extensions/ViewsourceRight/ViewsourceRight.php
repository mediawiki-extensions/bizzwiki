<?php
/*<!--<wikitext>-->
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
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->

== Purpose ==
This extension adds a 'viewsource' right. 
Only the users with the 'viewsource' permission can 'view' an article's source wikitext.

== FEATURES ==
* Can be used independantly of BizzWiki environment 
* No mediawiki installation source level changes

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]
* Hierarchical Namespace Permissions extension

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/XYZ/XYZ_stub.php');
</source>

== HISTORY ==
* Corrected missing 'return true' statement in hook.
* Removed 'view source' tab when permission is not granted to user.
* Moved Singleton invocation to end of file to accomodate some PHP versions
* Removed dependency on ExtensionClass
* Made 'stub'-enabled
* Added some protection against !isset indexes in '$wgExtensionCredits'

== Code==
<!--</wikitext>--><source lang=php>*/

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
			if (isset($el['name']))		
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
//</source>