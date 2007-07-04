<?php
/*
 * ViewsourceRight.php
 *
 * @author Jean-Lou Dupont -- www.bluecortex.com
 * @package MediaWiki
 * @subpackage Extensions
 * 
 * <b>Purpose:</b>  This extension adds a 'viewsource' right.
 * Only the users with the 'viewsource' permission can 'view' an article's source wikitext.
 *
 * FEATURES:
 * =========
 * 0) Can be used independantly of BizzWiki environment 
 * 1) No mediawiki installation source level changes
 *
 * DEPENDANCIES:
 * =============
 * 1) ExtensionClass (>v1.3)
 * 2) Hierarchical Namespace Permissions extension
 *
 * Installation:
 * include("extensions/ViewsourceRight.php");
 *
 * HISTORY:
 * ========
 * - Corrected missing 'return true' statement in hook.
 * - Removed 'view source' tab when permission is not granted to user.
 *
 * TODO:
 * =====
 * 
 */

ViewsourceRight::singleton();

class ViewsourceRight extends ExtensionClass
{
	const thisName = 'ViewsourceRight';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	const id       = '$Id$';	
	
	public static function &singleton( )
	{ return parent::singleton( ); }
	
	// Our class defines magic words: tell it to our helper class.
	public function ViewsourceRight() 
	{ 
		parent::__construct( ); 
	
		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'    => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'  => 'Jean-Lou Dupont', 
			'url'     => 'http://www.bluecortex.com',
			'description' => "Status: "
		);
	}
	
	public function setup()
	{
		parent::setup();
	}
	public function hUpdateExtensionCredits( &$sp, &$extensionTypes )
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
		else unset( $content_actions['viewsource'] );

		return true;
	}
} // end class definition.
?>