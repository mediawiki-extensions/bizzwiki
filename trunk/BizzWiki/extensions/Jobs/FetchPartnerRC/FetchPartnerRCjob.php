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
							#				=> 'rc_patrolled';		// CHECKME														
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
		$result = $this->getPartnerList( $this->url, $this->port, $this->timeout );
		if (!$result)
			return $this->errorFetchingList();
		
		// 2) PARSE THE LIST
		$this->plst = $err = $this->parseDocument();
		if ($err === false)	return $this->errorParsingList();
		if ($err === true)	return $this->listEmpty();
		
		// 3) FILTER THE LIST
		$compte = $this->filterList();
		
		// 4) INSERT THE LIST
		
		// 5) SUCCESSFUL OPERATION
		$this->successLog();
		
		return true;
	}
	private function errorFetchingList()
	{
		// add an entry log.
		$this->updateLog( 'fetchfail',);
	}
	private function errorParsingList()
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
	private function getPartnerList( $url, $port, $timeout, $start, $limit )
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
		
		$this->document = curl_exec($ch);
		
		$error = curl_errno($ch)
		curl_close($ch);
		
		return $error;
	}
	private function parseDocument()
	{
		if (empty( $this->document ))
			return true;	// the document was empty, hence no problem.
		
		$p = null;
		
		// start by loading the document	
		$x = new DOMDocument();
		$x->loadXML( $this->document );
		
		// next, extract the relevant elements
		$rclist = $x->getElementsByTagName('rc');
		
		// place the elements in a PHP friendly array
		foreach( $rclist as &$rce )
		{
			$a = null;
			foreach( self::$params as $param )
				$a[ $param ] = $rce->getAttribute( $param );
			$p[] = $a;
		}
		
		// if the document was not empty and we end up
		// with an empty array, something is wrong.
		if (empty( $p ))
			return false;
			
		return $p;
	}
	/**
		Filter List for:
		- duplicate entries etc.	
		- fetchRC log entries (!)
	 */
	private function filterList()
	{
		// check that we have the 'rc_id' field
		// We will only get this parameter if we have a patched 'ApiQueryRecentChanges.php' file....

		
		// fetch last id from the recentchanges_partner table

		
		// our fetched entries cannot be 'lower' than the ones we already got:
		// filter them out (and keep a record of this)

		
		// make sure the list is ordered properly.

	}
	private function filterFetchRC()
	{
		$c = null;
		if (!empty( $this->lst ))	
			foreach( $this->lst as $index => &$e )
				
	}
	/**
			This function gets the last 'compte' entries
			from the 'recentchanges_partner' table.
	 */
	private function getLastEntries( $compte )
	{
		$dbr = wfGetDB( DB_SLAVE ); 
		
		$row = $dbr->selectRow( self::$tableName,			// table name
								array('sgr_group'),			//
								array('sgr_user' => $uid),	// 'WHERE'
								__METHOD__      );			// debug info.

		if (!empty($row))
			return $row->sgr_group;
		
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
	private function insertList()
	{
		$dbw = wfGetDB( DB_MASTER );

		$dbw->insert(	$this->table,
						array( $field => $id ),
						__METHOD__,
			'IGNORE' );
		wfDebug( __METHOD__.":   \n" );
	}
	
} // end class declaration
?>