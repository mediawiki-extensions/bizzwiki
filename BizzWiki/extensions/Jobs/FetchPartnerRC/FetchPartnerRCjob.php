<?php
/*<wikitext>
{| border=1
| <b>File</b> || FetchPartnerRCjob.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== NOTES ==
* 'start' parameter of api does not seem to work...
 
== History ==

== Code ==
</wikitext>*/

class FetchPartnerRCjob extends Job
{
	var $url;
	var $user;
	var $lst;
	var $plst;
	var $table;
	var $start;
	var $list_empty;
	var $flist;
	
	static $paramsList = array( 'rcid'		=> 'rc_id',				// BIZZWIKI specific
								'type'		=> 'rc_type', 
								'ns'		=> 'rc_namespace',
								'pageid'	=> 'rc_cur_id',			// checked
								'user'		=> 'rc_user',			// ok
							#				=> 'rc_user_text',		// CHECKME
								'bot'		=> 'rc_bot',			// ok
								'minor'		=> 'rc_minor',			// ok
								'new'		=> 'rc_new',			// ok
								'title'		=> 'rc_title', 			// ok
								'revid'		=> 'rc_this_oldid',		// checked 
								'old_revid'	=> 'rc_last_oldid',		// checked
							#				=> 'rc_moved_to_ns',	// CHECKME
							#				=> 'rc_moved_to_title',	// CHECKME
								'patrolled'	=> 'rc_patrolled',		// BIZZWIKI specific
							#				=> 'rc_ip',				// CHECKME							
							#				=> 'rc_old_len',		// CHECKME							
							#				=> 'rc_new_len',		// CHECKME							
							#				=> 'rc_deleted',		// CHECKME							
							#				=> 'rc_logid',			// CHECKME							
							#				=> 'rc_logtype',		// CHECKME							
							#				=> 'rc_log_action',		// CHECKME							
							#				=> 'rc_params',			// CHECKME							
								'timestamp'	=> 'rc_timestamp', 		// ok
							#	''			=> 'rc_cur_time',		// NEED TO FILL
								'comment'	=> 'rc_comment',		// checked
							);
	
	function __construct( $title=null, $parameters=null, $id = 0 ) 
	{
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct( 'fetchRC', Title::newMainPage()/* don't care */, $parameters, $id );
		
		$this->start	= null;
		$this->list_empty = null;
		$this->url		= FetchPartnerRC::$partner_url;
		$this->port		= FetchPartnerRC::$port;
		$this->timeout	= FetchPartnerRC::$timeout;
		$this->table	= FetchPartnerRC::$tableName;
		$this->limit 	= FetchPartnerRC::$limit;
		$this->tableName= FetchPartnerRC::$tableName;
		$this->logName  = FetchPartnerRC::$logName;
	}

	function run() 
	{
		// User under which we will file the log entry
		$this->user = User::newFromName( $this->logName );
		
		// 1) GET THE LIST
		$this->last_rc_id = $this->getLastEntry( $this->tableName );
		
		if ($this->last_rc_id === null)
		{		
			$this->list_empty = true;
			$this->expected_rc_id = 1;
		}
		else
		{
			$this->list_empty = false;			
			$this->expected_rc_id = $this->last_rc_id+1;
		}
		$result = $this->getPartnerList(	$this->url, $this->port, $this->timeout, 
											$this->expected_rc_id, $this->limit, $document, 
											$this->list_empty );
		if ( $result !== CURLE_OK )
			return $this->errorFetchingList();

		// 2) PARSE THE LIST
		$this->plst = $err = $this->parseDocument( $document, &$missing_rc_id, &$duplicate_rc_id );
		if ($err === false)	return $this->errorParsingList( $missing_rc_id, $duplicate_rc_id );
		if ($err === true)	return $this->listEmpty();

		// 3) SORT THE LIST
		$this->sortList( $this->plst );

		// 4) FILTER THE LIST
		$filtered_count = 0;
		$missing_count	= 0;
		$this->flist = $this->filterList(	$this->plst,
											$this->expected_rc_id,
											&$last_rc_id, 
											&$first_fetched_rc_id, 
											&$filtered_count, 
											&$missing_count );
			
		// 5) ADJUST 'rc_cur_time'
		$this->adjustCurTime( $this->flist );

		// 6) INSERT THE LIST
		$this->insertList( $this->flist )	;
		
		// 7) SUCCESSFUL OPERATION
		$this->successLog( $this->expected_rc_id, $first_fetched_rc_id, count($this->plst), $filtered_count, $missing_count );
		
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
		if ( $missing_rc_id )
			$param1 = "Missing 'rc_id'.";
		if ( $duplicate_rc_id )
			$param2 = "Duplicate 'rc_id'.";
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
	private function successLog( $expected_rc_id, $first_fetched_rc_id, $compte, $filtered_count, $missing_count )
	{
		// were there any entries made?
		$msg = $compte==0 ? 'fetchnc-text' : 'fetchok-text';
		
		// add an entry log.
		$this->updateLog( 'fetchok', $msg, $compte, $expected_rc_id, $first_fetched_rc_id, $filtered_count, $missing_count );
		return true;
	}
	/**
		Actual logging takes place here.
	 */
	private function updateLog( $action, $msgid, $param1=null, $param2=null, $param3=null, $param4=null, $param5=null )
	{
		$message = wfMsgForContent( 'ftchrclog-'.$msgid, $param1, $param2, $param3, $param4, $param5 );
		
		$log = new LogPage( 'ftchrclog' );
		$log->addEntry( $action, $this->user->getUserPage(), $message );
	}
	/**
		Fetch list from partner replication node.
		
	 */
	private function getPartnerList( $url, $port, $timeout, $start, $limit, &$document, $empty )
	{
		// NOTE: the api currently does not support the 'start' parameter.
		// we need to adjust the url to access the MW API.
#		if ($empty)
			$url .= '/api.php?action=query&list=recentchanges&rclimit='.$limit.'&rcprop=user|comment|flags&format=xml';
#		else
#			$url .= '/api.php?action=query&list=recentchanges&start='.$start.'&rclimit='.$limit.'&rcprop=user|comment|flags&format=xml';		
		
		// make sure we only fetch from the point where we had stopped previously
		// use rc_id identifier / rc_timestamp for this purpose.

		$ch = curl_init();    							// initialize curl handle

		curl_setopt($ch, CURLOPT_URL, $url);			// set url to post to
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);		// Fail on errors
		#curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);   // allow redirects
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 	// return into a variable
		curl_setopt($ch, CURLOPT_PORT, $port);         	//Set the port number
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);	// times out after 15s
		#curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		
		$document = curl_exec($ch);
		
		$error = curl_errno($ch);
		curl_close($ch);
		
		return $error;
	}
	private function parseDocument( &$document, &$missing_rc_id, &$duplicate_rc_id )
	{
		// assume best case.
		$missing_rc_id = $duplicate_rc_id = null;
		
		if (empty( $document ))
			return true;	// the document was empty, hence no problem.
		
		$p = null;
		
		// start by loading the document	
		$x = new DOMDocument();
		$x->loadXML( $document );
		
		// next, extract the relevant elements
		$rclist = $x->getElementsByTagName('rc');
		
		// place the elements in a PHP friendly array
		foreach( $rclist as $rce )
		{
			$a = null;
			foreach( self::$paramsList as $param => $dbkey )
			{
				$value = $rce->getAttribute( $param );
				// must adjust TIMESTAMP field
				if ( $param == 'timestamp' )
					$value = wfTimestamp( TS_MW, $value );
				$a[ $dbkey ] = $value;
			}
			
			// make sure we have an 'rc_id' present
			if (!isset( $a['rc_id'] ))
				{ $missing_rc_id = true; $p=null; break; }
				
			$rc_id = $a['rc_id'];
			// now make sure we didn't encounter this rc_id yet in the transaction
			if (isset( $p[$rc_id] ))
				{ $duplicate_rc_id = $rc_id; $p=null; break; }
				
			// everything looks ok... for this row
			$p[ $rc_id ] = $a; 
		}

		// document empty? special return code.		
		if (empty( $p ))
			return true;

		// if the document was not empty and we end up
		// with an empty array, something is wrong.
		if ( ($missing_rc_id != null) || ($duplicate_rc_id != null) )
			return false;
			
		return $p;
	}
	/**
		Sort the list by increasing uid
	 */
	private function sortList( &$lst )
	{
		return ksort( $lst, SORT_NUMERIC );
	}
	/**
		Filter List for:
		- duplicate entries etc.	
		- fetchRC log entries (!)
	 */
	private function filterList(	&$lst,
									$next_expected_rc_id,
									&$last_rc_id, 
									&$first_fetched_rc_id, 
									&$filtered_count, 
									&$missing_count )
	{
		// Get our first element from the fetched list
		reset( $lst );
		$first_fetched_entry = &current( $lst );
		$first_fetched_rc_id = $first_fetched_entry['rc_id'];
		
		// At this point, we can be faced with 3 cases:
		// case 1: the normal case (current list & fetched list are synchronized
		// case 2: the fetched list contains rc_id we already got in our current list
		// case 3: we are missing rc_id entries
		
			// case 2 (filter out)
		// assume best case.
		$filtered_count = 0;
		
		// Because of a bug in php v5 wrt to arrays passed by reference,
		// we need to make a copy of the records we are going to carry forward.
		$nlist = null;
		
		if ( $next_expected_rc_id  > $first_fetched_rc_id )
		{
			foreach( $lst as $index => $e )
				if ( $next_expected_rc_id > $e['rc_id'] )
					$filtered_count++;
				else
					$nlist[$e['rc_id']] = $e;	// copy here
		}
		else
			$nlist = $lst; // copy here
		
		ksort( $nlist );
		
			// case 3
			// Even at this point we should check if we are missing some rc_id's...
			// We are assuming the list we are receiving is ordered by increasing 'rc_id' (see sortList)
		$compte = count( $nlist ); // max # of entries to deal with regardless
		$missing_count = 0;

		reset( $nlist );	
		$e=current( $nlist );
		$next_rc_id = $e['rc_id'];		
		
		if ($compte>1)
			do
			{
				$e=current( $nlist );				
				$rc_id = $e['rc_id'];
				
				if ($rc_id != $next_rc_id)
					$missing_count++;
				next( $nlist );
				// since the api does not respond to the 'start' parameter,
				// we must reset the 'next_expected_rc_id' everytime as we
				// do not have a solid reference point.
				$next_rc_id = $rc_id+1;
				$compte--;			
			} while( $compte>0 );
	
		return $nlist;
	}
	/**
		Adjust the 'rc_cur_time' field to match the current time
		where the local 'partner' table was updated.
	 */
	private function adjustCurTime( &$lst )
	{
		// no need to be that precise in the timestamp
		$cur_time = wfTimestamp( TS_MW );
		foreach( $lst as $index => &$e )
			$e['rc_cur_time'] = $cur_time;
	}
	/**
			This function gets the last 'compte' (default to 1) entries
			from the 'recentchanges_partner' table.
	 */
	private function getLastEntry( $tableName )
	{
		$dbr = wfGetDB( DB_SLAVE ); 
			
		$row = $dbr->selectRow( $tableName,			// FROM table name
								array(	'rc_id',				// select
										'rc_timestamp' ), 	
								null,						// 'WHERE'
								__METHOD__,					// debug info.
								array(
									'ORDER BY'  => 'uid DESC',
									'LIMIT' => 1,
									)
						      );

		if (isset( $row->rc_id ))
			$rc_id = $row->rc_id;
		else
			$rc_id = null;
			
		return $rc_id;		
	}
	/**
		Inserts the processed list in the 'recentchanges_partner' table.
	 */
	private function insertList( &$lsts )
	{
		$dbw = wfGetDB( DB_MASTER );

		foreach( $lsts as $index => &$e )
		{
			#$uid = $dbw->nextSequenceValue( 'recentchanges_partner_uid_seq' );
			#$params = array_merge( array('uid' => $uid), $params );
			$dbw->insert( $this->table, $e, __METHOD__ );
		}
		$dbw->commit();		
		wfDebug( __METHOD__.": end \n" );		
	} // end insert
	
} // end class declaration
?>