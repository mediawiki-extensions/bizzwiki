<?php
/*<wikitext>
{| border=1
| <b>File</b> || WatchRight.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Provides watch/unwatch rights enforcement.

== Features ==


== Dependancy ==
* [[Extension:ExtensionClass|ExtensionClass]]

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/WatchRight/WatchRight.php');
</source>

== History ==

== Code ==
</wikitext>*/

class WatchRight extends ExtensionClass
{
	const thisName = 'WatchRight';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	const id       = '$Id$';	
	
	public static function &singleton( )
	{ return parent::singleton( ); }
	
	// Our class defines magic words: tell it to our helper class.
	public function __construct() 
	{ 
		parent::__construct( ); 
	
		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'    => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'  => 'Jean-Lou Dupont', 
			'description' => "Enforces 'watch/unwatch' rights",
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	
	public function setup()
	{	parent::setup(); }

	public function hWatchArticle( &$user, &$article )
	{
		if (!$user->isAllowed( 'watch' ))
			return $this->error( 'watch' );
		return true;
	}

	public function hUnwatchArticle( &$user, &$article )
	{
		if (!$user->isAllowed( 'unwatch' ))
			return $this->error( 'unwatch' );
		return true;			
	}
	private function error( $msg )
	{
		global $wgOut;
	
		$wgOut->addWikiText( wfMsg( 'badaccess' ) );
		
		return false;
	}
	
	public function hSkinTemplateTabs( &$st , &$content_actions )
	{
		global $wgUser;
		
		if (!$wgUser->isAllowed( 'watch') )
			unset( $content_actions['watch'] );

		if (!$wgUser->isAllowed( 'unwatch') )
			unset( $content_actions['unwatch'] );

		return true;
	}
} // end class definition.

WatchRight::singleton();
?>