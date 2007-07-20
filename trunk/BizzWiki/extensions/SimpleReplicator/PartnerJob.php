<?php
/*<wikitext>
{| border=1
| <b>File</b> || PartnerJob.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==


== Features ==


== History ==

== Code ==
</wikitext>*/
require($IP.'/includes/JobQueue.php');

class PartnerJob extends Job
{
	var $tableClass;
	var $logHandle;
	
	function __construct( $ref, $title=null, $parameters=null, $id = 0, $tableClass, $logHandle ) 
	{
		$this->tableClass = $tableClass;
		$this->logHandle  = $logHandle;
		
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct( $ref, Title::newMainPage()/* don't care */, $parameters, $id );
	}

	function run() 
	{
		$classe = $this->tableClass;
		
		echo $classe;
		
		$table = new $classe;
		$err = $table->update();

		switch ($err )
		{
			case PartnerObjectClass::errFetchingUrl:
					return $this->errorFetchingList();			
					
			case PartnerObjectClass::errListEmpty:
					return $this->listEmpty();			
					
			case PartnerObjectClass::errParsing:
					$missing_rc_id		= $table->missing_rc_id;
					$duplicate_rc_id	= $table->duplicate_rc_id;
					return $this->errorParsingList( $missing_rc_id, $duplicate_rc_id );
					
			case PartnerObjectClass::errOK:
					break;
		}
		$compte 		= $table->compte;
		$filtered_count = $table->filtered_count;
		$updated_rows 	= $table->affected_rows;
		
		if ($table->almostInSync)	$state = "'''normal'''";
		if ($table->catchingUp)		$state = "'''catching up'''";
		if ($table->startup)		$state = "'''startup'''";

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
		if ( $missing_id )			$param1 = "Missing 'rc_id'.";
		if ( $duplicate_id!=null )	$param2 = "Duplicate 'rc_id'=".$duplicate_id.'.';
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
		$message = wfMsgForContent( $this->logHandle.'-'.$msgid, $param1, $param2, $param3, $param4, $param5 );
		
		$log = new LogPage( $this->logHandle, false /*don't clog recentchanges list!*/  );
		
		$title = Title::makeTitle( NS_SPECIAL, 'log/'.$this->logHandle );
		$log->addEntry( $action, $title, $message );
	}
	
} // end class declaration.
?>