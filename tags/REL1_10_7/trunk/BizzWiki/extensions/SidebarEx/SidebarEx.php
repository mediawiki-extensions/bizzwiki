<?php
/*
 * SidebarEx.php
 * $Id$
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont 
 *
 * Purpose:  Provides a means of adding page links to the
 * ========  'sidebar' based on group membership.
 *
 * Features:
 * *********
 * 0) Can be used independantly of BizzWiki environment
 * 1) all defined groups are supported (standard MW and ones defined in installation)
 * 2) sidebar page name corresponds to 'group' name
 * 3) No patches to standard MW installation for MW version >= 1.10
 * 4) Group name prioritization
 * 
 * DEPENDANCY:  ExtensionClass extension (>v1.5)
 * 
 * Tested Compatibility:  MW 1.10
 * Patches for MW 1.8.x and MW 1.9.x available
 * 
 * SUGGESTIONS FROM USER(S):
 * ========================
 * 1) Bluecortex, is there a way I can grant other usergroups the ability to see this sidebar? 
 * I think this is an awesome extension, but with our setup it's not effective.
 * What would be cool is if it was a permission I could grant in the localsettings.php, 
 * like makesysop, userrights, or any other standard wiki permission. 
 * Thanks in advance! --24.164.92.162 12:57, 31 May 2007 (EDT) 
 * 
 *
 * INSTALLATION NOTES:
 * -------------------
 * Add to LocalSettings.php:
 * 
 * 1) Define (if desired) the base namespace where the pages will be fetched:
 *    $bwSidebarNs = NS_ADMIN;  // must be defined prior
 * 
 * 2) Define (if desired) the base page where the 'sidebar' pages will be fetched:
 *    $bwSidebarPage = 'Sidebars';
 * 
 * 3) Define the priority list i.e. group membership search order.
 *    $bwSidebarSearch = array ('somegroup', 'sysop', 'user', '*' );
 * 
 *    Corresponding sidebar pages:
                               MediaWiki:Sidebar/somegroup
                               MediaWiki:Sidebar/sysop
                               MediaWiki:Sidebar/user
							   MediaWiki:Sidebar/*
 * 
 * 4) Include the required scripts: 
 *  require("extensions/ExtensionClass.php");
 *  require("extensions/SidebarEx/SidebarEx.php");
 *
 * 5) Apply any page protection deemed necessary
 * 
== History ==
* Corrected bug with article validity checking (e.g. affects BizzWiki fresh installs)

 */
// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'SidebarEx extension: ExtensionClass missing.';	
else
	SidebarExClass::singleton();

class SidebarExClass extends ExtensionClass
{
	// constants.
	const thisName = 'SidebarEx';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	const id       = '$Id$';	
	
	// default values
	static $baseNs   = NS_MEDIAWIKI;  	// default namespace
	static $basePage = 'Sidebar';     	// default base page
	static $baseSearch   = array(	'sysop',
									'user',
									'*'
						 		);

	// variables.
	var $foundPage;

	public static function &singleton( ) // required by ExtensionClass
	{ return parent::singleton( ); }
	
	function SidebarExClass()
	{
		parent::__construct(); 			// required by ExtensionClass

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Provides sidebar customization on a per-group basis',
			'url' => self::getFullUrl(__FILE__),			
		);

		$this->foundPage = false;
		
		// customization found?
		global $bwSidebarNs, $bwSidebarPage, $bwSidebarSearch;
		$this->Ns     = isset($bwSidebarNs)    ==true ? $bwSidebarNs:     self::$baseNs;
		$this->Page   = isset($bwSidebarPage)  ==true ? $bwSidebarPage:   self::$basePage;
		$this->Search = isset($bwSidebarSearch)==true ? $bwSidebarSearch: self::$baseSearch;				
		
	}
	public function setup() { parent::setup(); } // nothing special to do in this case.

	public function hSkinTemplateOutputPageBeforeExec( &$skin, &$tpl )
	{
		global $wgUser;
		
		// get group membership array
		// even default group '*' is included as well as 'user' is logged in.
		$gr = $wgUser->getEffectiveGroups();
		
		// order the list based on the search order provided
		// Search array:  { 0->highest/first, 1-> ... }
		//
		// The group membership array provided by MW is assumed not to be sorted;
		// let's walk the search array to find a matching group.
		$page = null;
		foreach( $this->Search as $index => $group)
			if (in_array( $group, $gr )) { $page = $group; break; }
			 
		// did we find satisfaction?
		if (empty( $page )) return true;
		
		// form the path to the article:
		// Namespace:base page/group name
		$ns = Namespace::getCanonicalName( $this->Ns );
		$a = $this->getArticle( $ns.':'.$this->Page.'/'.$page );

		// is the corresponding page found?
		if (($a==null) || ($a->getID()==0))
		{
			$this->foundPage = false;
			return true;
		}
		else $this->foundPage = true;
		
		$text = $a->getContent();
		$bar  = $this->processSidebarText( $text );
		
		// get current sidebar text
		$cbar = $tpl->data['sidebar'];

		// add our own here
		$tpl->set( 'sidebar', array_merge($cbar, $bar) );		
		
		return true;
	}
	private function processSidebarText( &$textSideBar )
	// copied from SkinTemplate MW 1.8.x SVN
	{
		$bar = array();
		$lines = explode( "\n", $textSideBar );
		foreach ($lines as $line) {
			if (strpos($line, '*') !== 0)
				continue;
			if (strpos($line, '**') !== 0) {
				$line = trim($line, '* ');
				$heading = $line;
			} else {
				if (strpos($line, '|') !== false) { // sanity check
					$line = explode( '|' , trim($line, '* '), 2 );
					$link = wfMsgForContent( $line[0] );
					if ($link == '-')
						continue;
					if (wfEmptyMsg($line[1], $text = wfMsg($line[1])))
						$text = $line[1];
					if (wfEmptyMsg($line[0], $link))
						$link = $line[0];
					$href = self::makeInternalOrExternalUrl( $link );
					$bar[$heading][] = array(
						'text' => $text,
						'href' => $href,
						'id' => 'n-' . strtr($line[1], ' ', '-'),
						'active' => false
					);
				} else { continue; }
			}
		}
		return $bar;	
	}
	static function makeInternalOrExternalUrl( $name )
	// copied from SkinTemplate MW 1.8.x SVN	 
	{
		if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $name ) ) {
			return $name;
		} else {
			return self::makeUrl( $name );
		}
	}

	static function makeUrl( $name, $urlaction = '' )
	// copied from SkinTemplate MW 1.8.x SVN 
	{
		$title = Title::newFromText( $name );
		self::checkTitle( $title, $name );
		return $title->getLocalURL( $urlaction );
	}

	static function checkTitle( &$title, &$name )
	// copied from SkinTemplate MW 1.8.x SVN 
	{
		if( !is_object( $title ) ) {
			$title = Title::newFromText( $name );
			if( !is_object( $title ) ) {
				$title = Title::newFromText( '--error: link target missing--' );
			}
		}
	}

} // END CLASS DEFINITION
?>