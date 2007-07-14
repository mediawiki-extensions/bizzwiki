<?php
/*<wikitext>
{| border=1
| <b>File</b> || FetchPartnerRCjob.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
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
	
	static $params = array( 	'id'		=> 'rc_id',				// BIZZWIKI specific
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
							#				=> 'rc_moved_to_ns';	// CHECKME
							#				=> 'rc_moved_to_title';	// CHECKME
								'patrolled'	=> 'rc_patrolled';		// BIZZWIKI specific
							#				=> 'rc_ip';				// CHECKME							
							#				=> 'rc_old_len';		// CHECKME							
							#				=> 'rc_new_len';		// CHECKME							
							#				=> 'rc_deleted';		// CHECKME							
							#				=> 'rc_logid';			// CHECKME							
							#				=> 'rc_logtype';		// CHECKME							
							#				=> 'rc_log_action';		// CHECKME							
							#				=> 'rc_params';			// CHECKME							
								'timestamp'	=> 'rc_timestamp', 		// ok
								'comment'	=> 'rc_comment',		// checked
							);
	
	function __construct( $title, $params, $id = 0 ) 
	{
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct( 'fetchRC', Title::newMainPage()/* don't care */, $params, $id );
		
		$this->url  = FetchPartnerRC::$partner_url;
		$this->port = FetchPartnerRC::$port;
		$this->timeout = FetchPartnerRC::$timeout;
		$this->table	= FetchPartnerRC::$tableName;
	}

	function run() 
	{
		// User under which we will file the log entry
		$this->user = User::newFromName( FetchPartnerRC::$logName );
		
		// 1) GET THE LIST
		$result = $this->getPartnerList( $this->url, $this->port, $this->timeout, $document );
		if (!$result)
			return $this->errorFetchingList();
		
		// 2) PARSE THE LIST
		$this->plst = $err = $this->parseDocument( $document, &$missing_uid, &$duplicate_uid );
		if ($err === false)	return $this->errorParsingList( $missing_uid, $duplicate_uid );
		if ($err === true)	return $this->listEmpty();

		// 3) SORT THE LIST
		$this->slst = $this->sortList( $this->plst );
		
		// 4) FILTER THE LIST
		$compte = $this->filterList( $this->slst );
		
		// 5) INSERT THE LIST
		$this->insertList( $this->lst )	;
		
		// 6) SUCCESSFUL OPERATION
		$this->successLog();
		
		return true;
	}
	private function errorFetchingList()
	{
		// add an entry log.
		$this->updateLog( 'fetchfail',);
	}
	private function errorParsingList( $missing_uid, $duplicate_uid )
	{
		// add an entry log.	
		$this->updateLog( 'fetchfail',);
	}
	private function listEmpty()
	{
		// add an entry log.	
		$this->updateLog( 'fetchok',);
	}
	/**
		Adds a log entry upon successful operation.
	 */
	private function successLog( $compte )
	{
		// were there any entries made?
		$msg = $compte==0 ? 'fetchnc' : 'fetchok';
		
		// add an entry log.
		$this->updateLog( 'fetchok', $msg, $compte );
	}
	/**
		Actual logging takes place here.
	 */
	private function updateLog( $action, $msgid, $param1=null, $param2=null )
	{
		$message = wfMsgForContent( 'ftchrclog-'.$msgid.'-text', $param1, $param2 );
		
		$log = new LogPage( 'ftchrclog' );
		$log->addEntry( $action, $this->user->getUserPage(), $message );
	}
	/**
		Fetch list from partner replication node.
		
	 */
	private function getPartnerList( $url, $port, $timeout, $start, $limit, &$document )
	{
		// we need to adjust the url to access the MW API.
		$url .= '/api.php?action=query&list=recentchanges&start='.$start.'&rclimit='.$limit.'&rcprop=user|comment|flags';
		
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
		
		$error = curl_errno($ch)
		curl_close($ch);
		
		return $error;
	}
	private function parseDocument( &$document, &$missing_uid, &$duplicate_uid )
	{
		// assume best case.
		$missing_uid = $duplicate_uid = null;
		
		if (empty( $document ))
			return true;	// the document was empty, hence no problem.
		
		$p = null;
		
		// start by loading the document	
		$x = new DOMDocument();
		$x->loadXML( $document );
		
		// next, extract the relevant elements
		$rclist = $x->getElementsByTagName('rc');
		
		// place the elements in a PHP friendly array
		foreach( $rclist as &$rce )
		{
			$a = null;
			foreach( self::$params as $param )
				$a[ $param ] = $rce->getAttribute( $param );
				
			// make sure we have a 'uid' present
			if (!isset( $a['uid'] ))
				{ $missing_uid = true; $p=null; break; }
				
			$uid = $a['uid'];
			// now make sure we didn't encounter this uid yet in the transaction
			if (isset( $p[$uid] ))
				{ $duplicate_uid = $uid; $p=null; break; }
			// everything looks ok... for this row
			$p[ $uid ] = $a; 
		}

		// document empty? special return code.		
		if (empty( $p ))
			return true;

		// if the document was not empty and we end up
		// with an empty array, something is wrong.
		if ( ($missing_uid !=null) || ($duplicate_uid!=null) )
			return false;
			
		return $p;
	}
	/**
		Sort the list by increasing uid
	 */
	private function sortList( &$lst )
	{
		return ksort( $lst );
	}
	/**
		Filter List for:
		- duplicate entries etc.	
		- fetchRC log entries (!)
	 */
	private function filterList( &$lst, &$broken_table, &$last_uid, &$first_fetched_uid, &$filtered_count )
	{
		// assume best case.
		$broken_table = false;
		$filtered_count = 0;
		
		// fetch last id from the recentchanges_partner table
		// check if the database row looks OK for us.
		// We will only get this parameter if we have a patched 'ApiQueryRecentChanges.php' file....
		$row = $this->getLastEntries();
		if (!isset( $row->rc_id ) || !isset( $row->uid ) )
			{ $broken_table = true; return false; }
		
		$last_uid = $row->uid;

		// Get our first element from the fetched list
		reset( $lst );
		$first_fetched_entry = &current( $lst );
		$first_fetched_uid = key( $first_fetched_entry );
			
		// At this point, we can be faced with 3 cases:
		// case 1: the normal case (current list & fetched list are synchronized
		// case 2: the fetched list contains UID we already got in our current list
		// case 3: we are missing UID entries
		
			// case 1 (normal case... hopefully!)
		if ( ($last_uid+1) == $first_fetched_uid )
			return true;
		
			// case 2 (filter out)
		if ( $last_uid  >= $first_fetched_uid)
			foreach( $lst as $uid => &$e )
				if ( $last_uid >= $uid )
					{ unset( $lst[$uid] ); $filtered_count++; }

			// case 3
			// Even at this point we should check if we are missing some UID...

	}
	private function filterFetchRC()
	{
		$c = null;
		if (!empty( $this->lst ))	
			foreach( $this->lst as $index => &$e )
				
	}
	/**
			This function gets the last 'compte' (default to 1) entries
			from the 'recentchanges_partner' table.
	 */
	private function getLastEntries( $compte=1 )
	{
		$dbr = wfGetDB( DB_SLAVE ); 
			
		$row = $dbr->selectRow( self::$tableName,			// FROM table name
								array(	'uid',				// select
										'rc_id', 
										'rc_timestamp' ), 	
								null,						// 'WHERE'
								__METHOD__,					// debug info.
								array(
									'ORDER BY'  => 'uid DESC',
									'LIMIT' => $compte,
									)
						      );

		if (isset($row->uid))
			return $row;
			
		return null;		
	}
	/**
		Format the rows received from the database
		to a format we can more easily work with
		i.e. the same format as we have set for the list
		we received from the partner replication node.
	 */
	private function formatRowList( &$l )
	{
		if (empty( $l )) 
			return;	
		foreach( $l as $key => $value )
			
	}
	
	/**
		Inserts the processed list in the 'recentchanges_partner' table.
	 */
	private function insertList( &$lsts )
	{
		$dbw = wfGetDB( DB_MASTER );

		foreach( $lsts as $params )
		{
			$uid = $dbw->nextSequenceValue( 'recentchanges_partner_uid_seq' );
			$params = array_merge( array('uid' => $uid), $params );
			$dbw->insert( $this->table, $params, __METHOD__ );
		}
		
		wfDebug( __METHOD__.": end \n" );		
	} // end insert
	
} // end class declaration
?>