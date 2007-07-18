<?php
/*<wikitext>
{| border=1
| <b>File</b> || LoggingTable.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>

== Implementation ==

== NOTES ==
 
== History ==

== Code ==
</wikitext>*/

require_once( dirname(dirname(__FILE__)).'/TableClass.php');

class RecentChangesPartnerTable extends TableClass
{
	// related to partner API access.
	var $url;
	var $port;
	var $timeout;
	var $limit;
	
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
	
	// error codes.
	const errOK			 = 0;
	const errFetchingUrl = 1;
	const errListEmpty   = 2;
	const errParsing     = 3;
	
	// state variables
	var $missing_rc_id;		// only valid if errParsing is returned by update()
	var $duplicate_rc_id;	// only valid if errParsing is returned by update()
	var $filtered_count;	// only valid after 'filterList' method
	var $affected_rows;
	
	var $startup;
	
	public function __construct( $table = 'recentchanges_partner', $index = 'rc_id', $ts = 'rc_timestamp' )
	{
		return parent::__construct( $table, $index, $ts );
	}
	public function setup( $url, $port, $timeout, $limit )
	{
		$this->url		= $url;
		$this->port		= $port;
		$this->timeout	= $timeout;
		$this->limit 	= $limit;
	}

	/**
		This method runs an update cycle.
	 */
	public function update()
	{
		$this->startup = false;
		$this->almostInSync = false;		
		$this->catchingUp = false;
		
		// let's check if there are any 'holes' in the local table.
		$holeid = $this->getFirstHole();
		
		// are we at the beginning of the table?
		// If yes, then we do not have a previous entry on which
		// to base a 'timestamp based retrieval'. Let's just
		// fetch an update from the partner to kick-start things.
		if ($holeid == 1)
		{
			$err = $this->getPartnerList( $this->url, $this->port, $this->timeout, $document, '', 'newer',$this->limit );
			$this->startup = true;
		}
		else
		{
			// let's find a first hole.
			$holeid = $this->getFirstHole();
			// and get the timestamp of the preceeding entry
			$bholeid = $this->getIdTsBeforeFirstHole( $holeid, $ts, true );
			// convert timestamp to the API's liking
			// The one returned by the database is in TS_MW format.
			$tsAPI = wfTimestamp(TS_ISO_8601, $ts );
			$err = $this->getPartnerList( $this->url, $this->port, $this->timeout, $document, $tsAPI, 'newer',$this->limit );
		}
		if ($err !== CURLE_OK )
			return errFetchingUrl;
			
		// At this point, we have a document to parse.
		$plist = $err= $this->parseDocument( $document, self::$paramsList, $this->missing_rc_id, $this->duplicate_rc_id );
		if ($err === false)	return errParsing;
		if ($err === true)	return errListEmpty;
		
		// make sure we have the timestamp in the db format.
		$this->adjustCurTime( $plist );
		
		// Now we have a parsed document to process.
		// -----------------------------------------
		$lastid = $this->getLastId( $ts );
		
		// If the last id recorded in the local table equals
		// that of the 'previous' hole, then we have ~ synchronized situation;
		// filter out all records that fall below $lastid.
		// Make sure we have some records in the db (i.e. $lastid !== null)
		if ( ( $lastid == $bholeid ) && ( $lastid !== null ))
		{
			$this->almostInSync = true;
			$flist = $this->filterList( $plist, $lastid+1, $this->filtered_count );
			$this->compte = count( $flist );
			// update the table
			$this->affected_rows = $this->updateList( $flist );
			return errOK;
		}
		$this->filtered_count = 0;
			
		// At this point, just insert the records we got from the partner.
		// We are not really near 'synchronization': gather up as many records
		// as possible to catch up.
		$this->catchingUp = true;
		$this->compte = count( $plist );
		$this->affected_rows = $this->updateList( $plist );
		return errOK;
	}

	private function getPartnerList( $url, $port, $timeout, &$document, $start='', $dir = 'newer', $limit='' )
	{
		$dir = '&rcdir='.$dir;
		
		if ($limit !== '')
			$limit = '&rclimit='.$limit;
		if ($start !== '')
			$start = '&rcstart='.$start;
			
		// NOTE: the api currently does not support the 'start' parameter.
		// we need to adjust the url to access the MW API.
		$url .= '/api.php?action=query&list=recentchanges&format=xml'.$start.$limit.$dir;

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
		
		// CURLE_OK if everything OK.
		return $error;
	}
	private function parseDocument( &$document, &$paramsList, &$missing_rc_id, &$duplicate_rc_id )
	{
		if (empty( $document ))
			return true;	// the document was empty, hence no problem.
		
		$p = null;
		
		// start by loading the document	
		$x = new DOMDocument();
		@$x->loadXML( $document );

		// next, extract the relevant elements
		$rclist = $x->getElementsByTagName('rc');
		
		// place the elements in a PHP friendly array
		foreach( $rclist as $rce )
		{
			$a = null;
			foreach( $paramsList as $param => $dbkey )
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

		// sort the list for convenience
		ksort( $p );
					
		return $p;
	}
	/**
		Filters the list for records falling below $id
	 */
	private function filterList( &$lst, $next_expected_rc_id, &$filtered_count )
	{
		$filtered_count = 0;
		
		// Because of a bug in php v5 wrt to arrays passed by reference,
		// we need to make a copy of the records we are going to carry forward.
		$flist = null;
		
		foreach( $lst as $index => $e )
			if ( $next_expected_rc_id > $e['rc_id'] )
				$filtered_count++;
			else
				$flist[$e['rc_id']] = $e;	// copy here

		if (!empty( $flist ))
			ksort( $flist );

		return $flist;	
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

} // end class
?>