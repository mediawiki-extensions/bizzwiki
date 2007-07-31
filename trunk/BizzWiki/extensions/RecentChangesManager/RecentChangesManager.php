<?php
/*<wikitext>
{| border=1
| <b>File</b> || RecentChangesManager.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Prevents RecentChanges table entries from being deleted.

== Features ==


== Dependancy ==
* [[Extension:ExtensionClass|ExtensionClass]]

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/RecentChangesManager/RecentChangesManager.php');
</source>

== History ==

== Code ==
</wikitext>*/

class RecentChangesManager extends ExtensionClass
{
	const thisName = 'RecentChangesManager';
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
			'description' => "Prevents RecentChanges entries from being deleted",
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	
	public function setup()
	{	parent::setup(); }

	public function hArticleEditUpdatesDeleteFromRecentchanges( &$article )
	{
		// don't delete entries from RecentChanges
		return false;
	}

} // end class definition.

RecentChangesManager::singleton();
?>