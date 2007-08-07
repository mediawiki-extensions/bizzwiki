<?php
/*<wikitext>
{{Extension
|name        = SidebarEx
|status      = stable
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/SidebarEx/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}

== Purpose ==
Provides a means of adding page links to the 'sidebar' based on group membership & per-user basis.

== Features ==
* Can be used independantly of BizzWiki environment
* all defined groups are supported (standard MW and ones defined in installation)
* sidebar page name corresponds to 'group' name
* No patches to standard MW installation for MW version >= 1.10
* Group name prioritization
* Per-User sidebars using 'username/Sidebar' page
 
== DEPENDANCY ==
* ExtensionClass extension (>v1.5)
** Tested Compatibility:  MW 1.10
** Patches for MW 1.8.x and MW 1.9.x available

=== Parameters ===
<source lang='php'>
//Define (if desired) the base namespace where the pages will be fetched:
$bwSidebarNs = NS_ADMIN;  // must be defined prior, defaults to 'NS_MEDIAWIKI'
 
//Define (if desired) the base page where the 'sidebar' pages will be fetched:
$bwSidebarPage = 'Sidebars';

// Define the priority list i.e. group membership search order.
$bwSidebarSearch = array ('somegroup', 'sysop', 'user', '*' );
</source>

== INSTALLATION NOTES ==
=== Group membership based sidebars ===
<pre>
 Add to LocalSettings.php:
 
 1) Define (if desired) the base namespace where the pages will be fetched:
    $bwSidebarNs = NS_ADMIN;  // must be defined prior
 
 2) Define (if desired) the base page where the 'sidebar' pages will be fetched:
    $bwSidebarPage = 'Sidebars';
 
 3) Define the priority list i.e. group membership search order.
    $bwSidebarSearch = array ('somegroup', 'sysop', 'user', '*' );
 
    Corresponding sidebar pages:
       MediaWiki:Sidebar/somegroup
       MediaWiki:Sidebar/sysop
       MediaWiki:Sidebar/user
	   MediaWiki:Sidebar/*
 
 4) Include the required scripts: 
  require("extensions/ExtensionClass.php");
  require("extensions/SidebarEx/SidebarEx.php");
</pre>
=== Per-User sidebars ===
Edit the page 'username/Sidebar'.

== History ==
* Corrected bug with article validity checking (e.g. affects BizzWiki fresh installs)
* Moved singleton invocation to address some PHP warning
* Added 'per-user' sidebars

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
</wikitext>*/
// <source lang=php>
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
		$gbar = $this->doGroupSidebar();
		$ubar = $this->doUserSidebar();
		
		// get current sidebar text
		$cbar = $tpl->data['sidebar'];

		// add our own here
		$tpl->set( 'sidebar', array_merge($cbar, $gbar, $ubar) );		
		
		return true;
	}
	private function doUserSidebar()
	{
		global $wgUser;
		
		$userName = $wgUser->getName();

		$title = Title::makeTitle( NS_USER, $userName.'/Sidebar' );
		$a     = new Article( $title );
		
		// does 'username/Sidebar' page exist?
		if (($a==null) || ($a->getID()==0))		
			return array();
			
		$text = $a->getContent();
		$bar  = $this->processSidebarText( $text );

		return $bar;		
	}
	private function doGroupSidebar()
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
		if (empty( $page )) 
			return array();
		
		// form the path to the article:
		// Namespace:base page/group name
		$title = Title::makeTitle( $this->Ns, $this->Page.'/'.$page );
		$a     = new Article( $title );		
		
		// is the corresponding page found?
		if (($a==null) || ($a->getID()==0))
			return array();
		
		$text = $a->getContent();
		$bar  = $this->processSidebarText( $text );

		return $bar;		
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

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'SidebarEx extension: ExtensionClass missing.';	
else
	SidebarExClass::singleton();
// </source>