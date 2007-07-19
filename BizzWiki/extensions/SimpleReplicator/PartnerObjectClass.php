<?php
/*<wikitext>
{| border=1
| <b>File</b> || PartnerObjectClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/

abstract class PartnerObjectClass extends TableClass
{
	// Partner Machine related
	var $p_url;
	var $p_port;
	var $p_timeout;
	
	// Table Object related
	var $cur_timestamp_field_name;
	var $id_field_name;
	var $document_tag_field;
	
	public function __construct() 
	{ 
		parent::__construct( ); 
	
		$this->p_url	= PartnerMachine::$url;
		$this->p_port	= PartnerMachine::$url;
		$this->p_timeout= PartnerMachine::$timeout;		
	}

	private function adjustCurTime( &$lst )
	{
		if (empty( $this->cur_timestamp_field_name ))
			return;
			
		// no need to be that precise in the timestamp
		$cur_time = wfTimestamp( TS_MW );
		foreach( $lst as $index => &$e )
			$e[$this->cur_timestamp_field_name] = $cur_time;
	}

	/**
		Filters the list for records falling below $id
	 */
	private function filterList( &$lst, $next_expected_id, &$filtered_count )
	{
		$filtered_count = 0;
		
		// Because of a bug in php v5 wrt to arrays passed by reference,
		// we need to make a copy of the records we are going to carry forward.
		$flist = null;
		
		foreach( $lst as $index => $e )
			if ( $next_expected_id > $e[$this->id_field_name] )
				$filtered_count++;
			else
				$flist[$e[$this->$id_field_name]] = $e;	// copy here

		if (!empty( $flist ))
			ksort( $flist );

		return $flist;	
	}
	/**
		Parse the received XML formatted document.
	 */
	private function parseDocument( &$document, &$paramsList, &$missing_id, &$duplicate_id )
	{
		if (empty( $document ))
			return true;	// the document was empty, hence no problem.
		
		$p = null;
		
		// start by loading the document	
		$x = new DOMDocument();
		@$x->loadXML( $document );

		// next, extract the relevant elements
		$llist = $x->getElementsByTagName($this->document_tag_field);
		
		// place the elements in a PHP friendly array
		foreach( $llist as &$e )
		{
			$a = null;
			foreach( $paramsList as $param => $dbkey )
			{
				$value = $e->getAttribute( $param );
				
				// must adjust TIMESTAMP field
				if ( $param == 'timestamp' )
					$value = wfTimestamp( TS_MW, $value );
				$a[ $dbkey ] = $value;
			}
			
			// make sure we have an 'id' present
			if (!isset( $a[$this->id_field_name] ))
				{ $missing_id = true; $p=null; break; }
				
			$id = $a[$this->id_field_name];
			// now make sure we didn't encounter this 'id' yet in the transaction
			if (isset( $p[$id] ))
				{ $duplicate_id = $id; $p=null; break; }
				
			// everything looks ok... for this row
			$p[ $id ] = $a; 
		}

		// document empty? special return code.		
		if (empty( $p ))
			return true;

		// if the document was not empty and we end up
		// with an empty array, something is wrong.
		if ( ($missing_id != null) || ($duplicate_id != null) )
			return false;

		// sort the list for convenience
		ksort( $p );
					
		return $p;
	}
	/**
		Use the Mediawiki API to retrieve a 'document' from the partner replication node.
	 */
	private function getPartnerList( $url, &$document )
	{
		$ch = curl_init();    									// initialize curl handle

		curl_setopt($ch, CURLOPT_URL, $url);					// set url to post to
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);				// Fail on errors
		#curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);   		// allow redirects
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 			// return into a variable
		curl_setopt($ch, CURLOPT_PORT, $this->p_port); 			//Set the port number
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->p_timeout);	// times out after 15s
		#curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		
		$document = curl_exec($ch);
		
		$error = curl_errno($ch);
		curl_close($ch);
		
		// CURLE_OK if everything OK.
		return $error;
	}


} // end class declaration

?>