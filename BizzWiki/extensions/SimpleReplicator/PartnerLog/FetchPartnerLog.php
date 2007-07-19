<?php
/*<wikitext>
{| border=1
| <b>File</b> || FetchPartnerLog.php
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
require('FetchPartnerLog.i18n.php');
require_once('LoggingTable.php');

class FetchPartnerLog extends ExtensionClass  // so many extensions rely on ExtensionClass it does't hurt to 'use' it here.
{
	const thisName = 'FetchPartnerLog';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	const id       = '$Id$';	

	// Database
	static $tableName = 'logging_partner';
	
	// must be setup in settings file
	// e.g. FetchPartnerRC::$partner_url = 'http://xyz.com';
	static $partner_url = null;
	static $timeout 	= 15; // in seconds
	static $port 		= 80; // tcp port
	static $limit 		= 100;

	// i18n messages.
	static $msg;
	
	// Logging
	static $logName = 'WikiSysop';
	
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
			'description' => "Fetches the replication partner's RecentChanges table.",
			'url' => self::getFullUrl(__FILE__),			
		);
		
		$dir = dirname( __FILE__ );
		global $wgAutoloadClasses;
		$wgAutoloadClasses['FetchPartnerRCjob'] = $dir.'/FetchPartnerRCjob.php' ;

		global $wgJobClasses;
		$wgJobClasses['fetchRC'] = 'FetchPartnerRCjob'; 
	}
	
	public function setup()
	{	
		parent::setup(); 

		global $wgMessageCache;
		foreach( self::$msg as $key => $value )
			$wgMessageCache->addMessages( self::$msg[$key], $key );

		// LOGGING			
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]						= 'ftchrclog';
		$wgLogNames  ['ftchrclog']			= 'ftchrclog'.'logpage';
		$wgLogHeaders['ftchrclog']			= 'ftchrclog'.'logpagetext';
		$wgLogActions['ftchrclog/fetchok']	= 'ftchrclog'.'-fetchok-entry';
		$wgLogActions['ftchrclog/fetchfail']= 'ftchrclog'.'-fetchfail-entry';		
	}
	public function hUpdateExtensionCredits( &$sp, &$extensionTypes )
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
FetchPartnerRC::singleton();
?>