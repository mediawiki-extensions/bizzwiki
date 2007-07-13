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
	
	static $params = array( 'type', 'ns', 'title', 'revid', 'old_revid', 'timestamp' );
	
	function __construct( $title, $params, $id = 0 ) 
	{
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct( 'fetchRC', Title::newMainPage()/* don't care */, $params, $id );
		
		$this->url  = FetchPartnerRC::$partner_url;
		$this->port = FetchPartnerRC::$port;
		$this->timeout = FetchPartnerRC::$timeout;
	}

	function run() 
	{
		// User under which we will file the log entry
		$this->user = User::newFromName( FetchPartnerRC::$logName );
		
		$result = $this->getPartnerList( $this->url, $this->port, $this->timeout );
		if (!$result)
		{
			
		}
		
		$this->plst = $this->parseDocument();
		
		return true;
	}
	private function updateLog( $action, $result, $param )
	{
		$result = ($result) ? 'fetchok' : 'fetchfail';
			
		$message = wfMsgForContent( 'ftchrclog-'.$result.'-text', $param );
		
		$log = new LogPage( 'ftchrclog' );
		$log->addEntry( $result, $this->user->getUserPage(), $message );
	}
	/**
		Fetch list from partner replication node.
		
	 */
	private function getPartnerList( $url, $port, $timeout, $start, $limit )
	{
		// we need to adjust the url to access the MW API.
		$url .= '/api.php?action=query&list=recentchanges&start='.$start.'&rclimit='.$limit;
		
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
			return false;
		
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
		
		return $p;
	}
	/**
		Filter List for:
		- duplicate entries etc.	
		- fetchRC log entries (!)
	 */
	private function filterList()
	{
		
	}
	private function filterFetchRC()
	{
		if (!empty( $this->lst ))	
			foreach( $this->lst as $index => &$e )
				
	}
	private function ()
	{
		$dbr = wfGetDB( DB_SLAVE ); 
		
		$row = $dbr->selectRow( self::$tableName,
								array('sgr_group'),
								array('sgr_user' => $uid),
								__METHOD__      );

		if (!empty($row))
			return $row->sgr_group;
		
	}
} // end class declaration
?>