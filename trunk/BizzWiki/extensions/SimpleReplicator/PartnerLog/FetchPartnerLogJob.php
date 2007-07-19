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
require_once('LoggingPartnerTable.php');

class FetchPartnerLogJob extends Job
{
	function __construct( $title=null, $parameters=null, $id = 0 ) 
	{
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct( 'fetchLog', Title::newMainPage()/* don't care */, $parameters, $id );
	}

	function run() 
	{
		$logt = new LoggingPartnerTable();
		$err = $logt->update();
		
		switch ( $err )
		{
			case LoggingPartnerTable::errFetchingUrl:
					return $this->errorFetchingList();			
					
			case LoggingPartnerTable::errListEmpty:
					return $this->listEmpty();			
					
			case LoggingPartnerTable::errParsing:
					$missing_id = $logt->missing_id;
					$duplicate_id = $logt->duplicate_id;
					return $this->errorParsingList( $missing_id, $duplicate_id );
					
			case LoggingPartnerTable::errOK:
					break;
		}
		$compte 		= $logt->compte;
		$filtered_count = $logt->filtered_count;
		$updated_rows 	= $logt->affected_rows;
		
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
	private function errorParsingList( $missing_id, $duplicate_id )
	{
		if ( $missing_id )	$param1 = "Missing 'log_id'.";
		if ( $duplicate_id!=null )$param2 = "Duplicate 'log_id'=".$duplicate_id.'.';
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
		$message = wfMsgForContent( 'fetchlog-'.$msgid, $param1, $param2, $param3, $param4, $param5 );
		
		$title = Title::makeTitle( NS_SPECIAL, 'log/fetchlog' );		
		$log = new LogPage( 'fetchlog' );
		$log->addEntry( $action, $title, $message );
	}
	
} // end class declaration
?>