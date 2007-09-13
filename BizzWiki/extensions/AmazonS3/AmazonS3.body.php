<?php
/*
<!--<wikitext>-->
 <file>
  <name>AmazonS3.body.php</name>
  <version>$Id$</version>
  <package>Extension.AmazonS3</package>
 </file>
<!--</wikitext>-->
*/
// <source lang=php>

class AmazonS3
{
	// constants
	const c_timeout = 10;
	const c_port = 80;
	
	const amazon_site = 'http://s3.amazonaws.com/';
	
	var $timeout;
	var $port;
	
	var $secret_key;
	
	// current entities
	var $bucket;
	var $obj_list;

	public function __construct( $secret_key, $bucket = null )
	{
		$this->secret_key = $secret_key;
		$this->bucket = $bucket;
		$this->obj_list = null;
		
		// initialize the defaults.
		$this->timeout = self::c_timeout;
		$this->port = self::c_port;
		$this->site = self::amazon_site;
	}
	
	public function setTimeout( $t ) { $this->timeout = $t; }
	public function setPort( $p ) { $this->port = $p; }
	public function setSite( $s ) { $this->site = $s; }

	/**
		Uses the CURL library to fetch a WEB page.
	 */
	public static function getPage( &$uri, &$document )
	{
		 // initialize curl handle
		$ch = curl_init();

		// set url to post to
		curl_setopt($ch, CURLOPT_URL, $uri);
		
		// Fail on errors		
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		// return into a variable		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 	
		curl_setopt($ch, CURLOPT_PORT, $this->port );
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout );
		
		$document = curl_exec($ch);
		
		$error = curl_errno($ch);
		curl_close($ch);
		
		// CURLE_OK if everything OK.
		return $error;
	}

}
// </source>