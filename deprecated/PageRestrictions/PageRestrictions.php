<?php
/*<!--<wikitext>-->
{{Extension
|name        = PageRestrictions
|status      = stable
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/PageRestrictions/ SVN]
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
== Purpose==
Adds page level restrictions. The setting of these restrictions is granted to a user with the 'protect' right.

== Features ==

== Notes ==
* Currently, the only 'right' enforced directly by this extension is 'read'. From an implementation point of view, 
a request comes in with either 'action=view' or no 'action' field. This must be translated to 'read'; this function
occurs centrally in 'HierarchicalNamespacePermissions' extension.

== Dependancy ==
* [[Extension:ExtensionClass|ExtensionClass]]

== Installation ==
* Installation outside of the BizzWiki platform is not currently documented.
* Installation requires downloading 'User.php' from BizzWiki distribution.

== History ==

= See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

class PageRestrictionsClass extends ExtensionClass
{
	// constants.
	const thisName = 'PageRestrictionsClass';
	const thisType = 'other';
	const id       = '$Id$';	

	static $msg;
	static $rList  = array(	
							'read',			// This right is enforced by this extension
							'raw',			// This right is enforced by another extension
							'viewsource',	// This right is enforced by another extension
						);

	public static function &singleton()
	{ return parent::singleton( );	}

	function __construct( )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Adds page level restrictions definitions & enforcement',
		);
		
		self::$msg = array();
	}
	public function setup() 
	{ 
		global $wgRestrictionTypes, $wgHooks ;
		
		parent::setup();
		self::loadMessages();

		foreach( self::$rList as $index => $rest )
			$wgRestrictionTypes[] = $rest;
			
		$wgHooks['ArticleViewHeader'][] = array( &$this, 'hArticleViewHeader' );
	    
	}//end setup()
	private static function loadMessages()
	{
		global $wgMessageCache;
		foreach( self::$msg as $key => $value )
			$wgMessageCache->addMessages( self::$msg[$key], $key );		
	}
	public static function addRestrictionLevels( &$l = null )
	{
		global $wgRestrictionLevels;
		
		if (!is_array( $l ))
			$l = array( $l );
			
		if (!empty( $l ))
			foreach( $l as $index => $rest )
				$wgRestrictionLevels[] = $rest;
	}

	public function hArticleViewHeader( &$a )
	{
		global $wgUser;
		global $action;
		
		$titre = $a->mTitle->getDBkey();
		$ns = $a->mTitle->getNamespace();
		
		if ( !$wgUser->isAllowed( $action, $ns, $titre ) )
			self::accessError(); // dies here.
		
		return true;
	}
	private static function accessError()
	{
		global $wgOut;
		$wgOut->setPageTitle( wfMsg( 'badaccess' ) );
		$wgOut->addWikiText( wfMsg( 'badaccess-group0' ) );
		$wgOut->output();
		exit();
	}

} // end class declaration

if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: PageRestrictions extension will not work!';	
else
{
	PageRestrictionsClass::singleton();
	require('PageRestrictions.i18n.php');	
}
//</source>