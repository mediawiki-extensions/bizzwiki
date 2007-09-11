<?php
/*<wikitext>
{| border=1
| <b>File</b> || FetchPartnerUser.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension fetches the 'user' table from the partner replication node.

== Features ==

== Installation ==

== History ==

== Code ==
</wikitext>*/
require('FetchPartnerUser.i18n.php');
require_once('UserPartnerTable.php');

class FetchPartnerUser
{
	const thisName = 'FetchPartnerUser';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	const id       = '$Id$';	

	// Database
	static $tableName = 'user_partner';
	
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
			'description'	=> "Fetches the replication partner's User table.",
			#'url'			=> self::getFullUrl(__FILE__),			
		);
	}
	
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