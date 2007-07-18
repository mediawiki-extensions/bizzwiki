<?php
/*<wikitext>
{| border=1
| <b>File</b> || FetchPartnerLogJob.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>

== History ==

== Code ==
</wikitext>*/
require_once('LoggingTable.php');

class FetchPartnerLogJob extends Job
{
	var $url;
	var $port;
	var $timeout;
	var $limit;
	var $user;
	
	function __construct( $title=null, $parameters=null, $id = 0 ) 
	{
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct( 'fetchLog', Title::newMainPage()/* don't care */, $parameters, $id );
		
		$this->url		= FetchPartnerLog::$partner_url;
		$this->port		= FetchPartnerLog::$port;
		$this->timeout	= FetchPartnerLog::$timeout;
		$this->limit 	= FetchPartnerLog::$limit;
		$this->logName  = FetchPartnerLog::$logName;
	}

	function run() 
	{
		// User under which we will file the log entry
		$this->user = User::newFromName( $this->logName );

		$logt = new LoggingTable();
		$logt->setup( $this->url, $this->port, $this->timeout, $this->limit );

		$err = $logt->update();
		switch ( $err )
		{
			case LoggingTable::errFetchingUrl:
					return $this->errorFetchingList();			
					
			case LoggingTable::errListEmpty:
					return $this->listEmpty();			
					
			case LoggingTable::errParsing:
					$missing_rc_id = $logt->missing_rc_id;
					$duplicate_rc_id = $logt->duplicate_rc_id;
					return $this->errorParsingList( $missing_rc_id, $duplicate_rc_id );
					
			case LoggingTable::errOK:
					break;
		}
		$compte = $logt->compte;
		$filtered_count = $logt->filtered_count;
		$updated_rows = $logt->affected_rows;
		
		if ($logt->almostInSync)
			$state = "'''normal'''";
		if ($logt->catchingUp)
			$state = "'''catching up'''";
		if ($logt->startup)
			$state = "'''startup'''";

		$this->successLog( $compte, $filtered_count, $updated_rows, $state );
		
		return true;
	}
	private function errorFetchingList()
	{
		// add an entry log.
		$this->updateLog( 'fetchfail', 'fetchfail-text1' );
		return false;
	}
	private function errorParsingList( $missing_rc_id, $duplicate_rc_id )
	{
		if ( $missing_rc_id )	$param1 = "Missing 'rc_id'.";
		if ( $duplicate_rc_id )	$param2 = "Duplicate 'rc_id'.";
		// add an entry log.	
		$this->updateLog( 'fetchfail', 'fetchfail-text2', $param1, $param2 );
		return false;		
	}
	private function listEmpty()
	{
		// add an entry log.	
		$this->updateLog( 'fetchok', 'fetchnc-text' );
		return false;		
	}
	/**
		Adds a log entry upon successful operation.
	 */
	private function successLog( $compte, $filtered, $updated, $state )
	{
		// were there any entries made?
		$msg = $compte==0 ? 'fetchnc-text' : 'fetchok-text';
		
		// add an entry log.
		$this->updateLog( 'fetchok', $msg, $compte, $filtered, $updated, $state );
		return true;
	}
	/**
		Actual logging takes place here.
	 */
	private function updateLog( $action, $msgid, $param1=null, $param2=null, $param3=null, $param4=null, $param5=null )
	{
		$message = wfMsgForContent( 'ftchlglog-'.$msgid, $param1, $param2, $param3, $param4, $param5 );
		
		$log = new LogPage( 'ftchlglog' );
		$log->addEntry( $action, $this->user->getUserPage(), $message );
	}
	
} // end class declaration
?>