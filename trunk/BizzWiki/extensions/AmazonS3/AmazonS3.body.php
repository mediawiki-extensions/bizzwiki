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

require_once 'HTTP/Request.php';
require_once 'Crypt/HMAC.php';

class AmazonS3
{
	// constants
	const c_timeout = 5;
	const c_port = 80;
	
	const amazon_site = 'http://s3.amazonaws.com/';
	
	static $keyId;
	static $secretKey;
	static $site;
	static $lastCode = null;

	// simplified error codes
	const codeError 		= 0;
	const codeOK 			= 1;
	const codeUnauthorized	= 2;
	const codeNotModified	= 3;	
	
	// HTTP error codes
	static $HTTPCodes = array(
	100 => array( 'text' => 'Continue', 					'code' => codeOK ),
	101 => array( 'text' => 'Switching protocols', 			'code' => codeOK ),	
	200 => array( 'text' => 'OK', 							'code' => codeOK ),	
	201 => array( 'text' => 'Created', 						'code' => codeOK ),	
	202 => array( 'text' => 'Accepted', 					'code' => codeOK ),		
	203 => array( 'text' => 'Non-Authorative Information',	'code' => codeOK ),	
	204 => array( 'text' => 'No Content', 					'code' => codeOK ),	
	205 => array( 'text' => 'Reset Content', 				'code' => codeOK ),	
	206 => array( 'text' => 'Partial Content', 				'code' => codeOK ),					
	
	300 => array( 'text' => 'MultipleChoices', 				'code' => codeError ),
	301 => array( 'text' => 'Moved Permanently',			'code' => codeError ),
	302 => array( 'text' => 'Found',		 				'code' => codeError ),
	303 => array( 'text' => 'See Other',	 				'code' => codeError ),
	304 => array( 'text' => 'Not Modified', 				'code' => codeNotModified ),
	305 => array( 'text' => 'Use Proxy',	 				'code' => codeError ),
	307 => array( 'text' => 'Temporary Redirect',			'code' => codeError ),

	400 => array( 'text' => 'Bad Request',	 				'code' => codeError ),
	401 => array( 'text' => 'Unauthorized',					'code' => codeError ),
	402 => array( 'text' => 'Payment Required',	 			'code' => codeError ),
	403 => array( 'text' => 'Forbidden',	 				'code' => codeError ),
	404 => array( 'text' => 'Not Found',	 				'code' => codeError ),
	405 => array( 'text' => 'Method Not Allowed',			'code' => codeError ),
	406 => array( 'text' => 'Not Acceptable',				'code' => codeError ),
	407 => array( 'text' => 'Proxy Authentication Required','code' => codeError ),
	408 => array( 'text' => 'Request Time-Out',				'code' => codeError ),
	409 => array( 'text' => 'Conflict',						'code' => codeError ),
	410 => array( 'text' => 'Gone',							'code' => codeError ),
	411 => array( 'text' => 'Length Required',				'code' => codeError ),
	412 => array( 'text' => 'Precondition Failed',			'code' => codeError ),
	413 => array( 'text' => 'Request Entity Too Large',		'code' => codeError ),
	414 => array( 'text' => 'Request-URI Too Large',		'code' => codeError ),
	415 => array( 'text' => 'Unsupported Media Type',		'code' => codeError ),
	416 => array( 'text' => 'Requested range not satisfiable','code' => codeError ),
	417 => array( 'text' => 'Expectation Failed',			'code' => codeError ),
	
	500 => array( 'text' => 'Internal Server Error',		'code' => codeError ),
	501 => array( 'text' => 'Not implemented',				'code' => codeError ),	
	502 => array( 'text' => 'Bad Gateway',					'code' => codeError ),
	503 => array( 'text' => 'Service Unavailable',			'code' => codeError ),
	504 => array( 'text' => 'Gate Time-out',				'code' => codeError ),
	505 => array( 'text' => 'HTTP Version not supported',	'code' => codeError ),				
	);
	
	public function __construct( $keyId, $secretKey )
	{
		self::$keyId = $keyId;
		self::$secretKey = $secret_key;
		
		self::init();
	}
	
	public function init()
	{
		// initialize the defaults.
		self::$timeout = self::c_timeout;
		self::$port = self::c_port;
		self::$site = self::amazon_site;
	}
	public static function setTimeout( $t ) { self::$timeout = $t; }
	public static function setPort( $p ) { self::$port = $p; }
	public static function setSite( $s ) { self::$site = $s; }

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// BUCKET RELATED
	public static function getAllBuckets()
	{
		$code = self::doRequest('', 
								'/',
								'', 
								"GET", 
								false,
								&$responseHeaders, 
								&$document);
		if ($code == codeOK)
		return $code;		
	}

	public static function createBucket( $bucketName )
	{
		$code = self::doRequest('', 
								'/'.$bucketName, 
								'', 
								"PUT", 
								false,
								&$responseHeaders, 
								&$document);
	}
	
	public static function deleteBucket()
	{}
	
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// OBJECT RELATED
	public static function listObjects()
	{}

	public static function getObject()
	{}
	
	public static function putObject()
	{}

	public static function deleteObject()
	{}
	
	public static function headObject()
	{}
	
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// ACL RELATED
	public static function getBucketAcl()
	{
		
	}

	public static function getObjectAcl()
	{}
	
	public static function setBucketAcl()
	{}
	
	public static function setObjectAcl()
	{}
	
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	/**
		This method handles *all* requests to AmazonS3.
	 */
	public static function doRequest(	$contentType = null, 
										$resource, 
										$acl = null, 
										$verb, 
										$putBody = false,
										&$responseHeaders, 
										&$document )
	{
	    $httpDate = gmdate(DATE_RFC822);
	    $stringToSign = "$verb\n\n$contentType\n$httpDate\nx-amz-acl:$acl\n/$resource";
	    $hasher =& new Crypt_HMAC( self::$secretKey, "sha1" );
	    $signature = self::hex2b64( $hasher->hash($stringToSign) );
		
		$req =& new HTTP_Request( self::amazon_site . $resource);
		
		$req->setMethod($verb);
		
		// optional headers
		if (!empty( $contentType ))
			$req->addHeader("content-type", $contentType);
		if (!empty( $acl ))
			$req->addHeader("x-amz-acl", $acl);			

		// mandatory headers
		$req->addHeader("Date", $httpDate);
		$req->addHeader("Authorization", "AWS " . self::$keyId . ":" . $signature);

		if ($putBody)
			$req->setBody( $document );

		$req->setOptions( array(	'port' 		=> $this->port,
									'timeout'	=> $this->timeout
						) );
						
		$req->sendRequest();

		// return all response headers.
	    $responseHeaders =	$req->getResponseHeader();
		$document = 		$req->getResponseBody();
		self::$lastCode = 	$req->getResponseCode();
		
		return self::translateCode( self::$lastCode );
	}
	public static function hex2b64($str) 
	{
	    $raw = '';
	    for ($i=0; $i < strlen($str); $i+=2)
	        $raw .= chr(hexdec(substr($str, $i, 2)));

	    return base64_encode($raw);
	}
	/**
	 */
	public static function translateCode( $code )
	{
		if (isset( self::$HTTPCodes[ $code ]))
			return self::$HTTPCodes[ $code ]['code'];
			
		return self::codeError;
	}
} // end class AmazonS3


// <><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><>
// <><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><>


class BucketList implements ArrayAccess, Iterator
{
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// Iterator Interface
	public function current()
	{}
	public function key()
	{}
	public function next()
	{}
	public function rewind()
	{}
	public function valid()
	{}

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// ArrayAccess Interface
	public function offsetExists( $offset )
	{}
	public function offsetGet( $offset )
	{}
	public function offsetSet( $offset, $value )
	{}
	public function offsetUnset( $offset )
	{}

} // end class declaration BucketList




// </source>