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
* PHP compiled with 'cURL' extension

== Notes ==
* The parameter 'rc_timestamp' is not sufficient to determine entry unicity (lack of resolution).

== Installation ==

== History ==

== Code ==
</wikitext>*/
require('FetchPartnerRC.i18n.php');
require_once('RecentChangesPartnerTable.php');

class FetchPartnerRC
{
	const thisName = 'FetchPartnerRC';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	const id       = '$Id$';	

	// Database
	static $tableName = 'recentchanges_partner';
	
	// i18n messages.
	static $msg;
	
	// Logging
	static $logName = 'WikiSysop';
	
	// Our class defines magic words: tell it to our helper class.
	public function __construct() 
	{ 
		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'    		=> self::thisName, 
			'version'		=> StubManager::getRevisionId( self::id ),
			'author'		=> 'Jean-Lou Dupont', 
			'description'	=> "Fetches the replication partner's RecentChanges table.",
			#'url'			=> self::getFullUrl(__FILE__),			
		);
	}
	
	public function setup()
	{ parent::setup(); }
	
	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	// setup of this hook occurs in 'ExtensionClass' base class.
	{
		global $wgExtensionCredits;

		$update = $this->getUpdate();
		$result = ' Status: '.$this->getUpdate();
	
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if ($el['name']==self::thisName)
				$el['description'].=$result;
				
		return true; // continue hook-chain.
	}
	private function getUpdate()
	{
		// 1) check existence of 'recentchanges_partner' table
		// 2) get last entry
		$result  = $this->checkTable();
		$r1      = 'database table ';
		$r1     .= $result ? 'exists.':'does not exist.';
		
		return $r1;
	}
	public function checkTable()
	{
		$dbr = wfGetDB(DB_SLAVE);
		return $dbr->tableExists(self::$tableName);
	}

} // end class declaration
?>