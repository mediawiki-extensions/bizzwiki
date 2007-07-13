<?php
/*<wikitext>
{| border=1
| <b>File</b> || FetchPartnerRC.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension fetches the 'recentchanges' table from the partner replication node.

== Features ==


== Dependancies ==
* [[Extension:ExtensionClass|ExtensionClass]]
* JobQueue.php
** Patched  from MW 1.10  *OR*
** MW 1.11

== Installation ==

== History ==

== Code ==
</wikitext>*/
require('FetchPartnerRC.i18n.php');
class FetchPartnerRC extends ExtensionClass  // so many extensions rely on ExtensionClass it does't hurt to 'use' it here.
{
	const thisName = 'FetchPartnerRC';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	const id       = '$Id: WatchRight.php 378 2007-07-13 12:52:41Z jeanlou.dupont $';	

	// must be setup in settings file
	// e.g. FetchPartnerRC::$partner_url = 'http://xyz.com';
	static $partner_url = null;

	// i18n messages.
	static $msg;
	
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
			'description' => "Fetches the replication partner's RecentChanges table",
			'url' => self::getFullUrl(__FILE__),			
		);
		
		global $wgAutoloadClasses;
		$wgAutoloadClasses[] = array( 'FetchPartnerRCjob' => 'FetchPartnerRCjob.php' );

		global $wgJobClasses;
		$wgJobClasses['fetchRC'] = 'FetchPartnerRCjob'; 
	}
	
	public function setup()
	{	parent::setup(); }

} // end class declaration

?>