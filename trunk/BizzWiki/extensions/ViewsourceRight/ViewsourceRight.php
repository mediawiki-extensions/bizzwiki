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
 * V1.0
 *
 */
 
ViewsourceRight::singleton();

class ViewsourceRight extends ExtensionClass
{
	const thisName = 'ViewsourceRight';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version

	public static function &singleton( )
	{ return parent::singleton( ); }
	
	// Our class defines magic words: tell it to our helper class.
	public function ViewsourceRight() 
	{ 
		parent::__construct( ); 
	
		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'    => self::thisName, 
			'version' => 'v1.0 $LastChangedRevision$',
			'author'  => 'Jean-Lou Dupont', 
			'url'     => 'http://www.bluecortex.com',
			'description' => "Status: "
		);
	}
	
	public function setup()
	{
		parent::setup();
		
		global $wgHooks;
		$wgHooks['AlternateEdit'][]    = array( &$this, 'hAlternateEditHook' );
		$wgHooks['SkinTemplateTabs'][] = array( &$this, 'hSkinTemplateTabs' );		
		
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
	
	public function hAlternateEditHook( &$ep )
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
					$wgOut->setSubtitle( wfMsg( 'viewsourcefor', $skin->makeKnownLinkObj( $wgTitle ) ) );
					$wgOut->addWikiText( wfMsg( 'viewsourceprohibited' ) );
					
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
		if ($st->mTitle->userCan('edit') ) return;

		if ($wgUser->isAllowed( 'viewsource') )
		{
			$content_actions['viewsource'] = array(
				'class' => ($action == 'edit') ? 'selected' : false,
				'text' => wfMsg('viewsource'),
				'href' => $st->mTitle->getLocalUrl( $st->editUrlOptions() )
			);
		}

	}

} // end class definition.
?>