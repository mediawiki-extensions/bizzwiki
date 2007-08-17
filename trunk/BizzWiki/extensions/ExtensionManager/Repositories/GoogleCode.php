<?php
/*<!--<wikitext>-->
{{Repository
|name        = GoogleCode
|status      = beta
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ExtensionManager/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
== Purpose==
Provides a means of easily installing 'extensions' to MediaWiki.

== Features ==
* Definition of 'repositories'

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/ExtensionManager/ExtensionManager_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

class GoogleCode extends ExtensionRepository
{
	// base URI of googlecode
	const baseURI  = 'http://$1.googlecode.com/svn/';
	
	const port 		= 80;
	const timeout	= 15;

	// variables

	public function __construct( $project, $directory )
	{
		// don't forget to call the parent!
		return parent::__construct( self::baseURI, $project, $directory );	
	}

	/**
		Checks whether the repository exists i.e. is available at all.
	 */
	public function exists()
	{
		$error = $this->getPage( '.' );
		return ( $error === CURLE_OK ) ? true:false;
	}

	public static function getCode( &$project, &$file, &$code )
	{
		$uri = self::formatURI( $project, $file );	
		
		return self::getPage( $uri, $code );
	}


	/**
		Uses the CURL library to fetch the code off Google's WEB site.
	 */
	public function getFileCode( &$file, &$document )
	{
		 // initialize curl handle
		$ch = curl_init();

		// set url to post to
		curl_setopt($ch, CURLOPT_URL, $this->uri.$file);
		
		// Fail on errors		
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		// return into a variable		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 	
		//Set the port number				
		curl_setopt($ch, CURLOPT_PORT, self::port );
		// times out after 15s		
		curl_setopt($ch, CURLOPT_TIMEOUT, self::timeout );
		
		$document = curl_exec($ch);
		
		$error = curl_errno($ch);
		curl_close($ch);
		
		// CURLE_OK if everything OK.
		return $error;
	}

	/**
		Recursive function which preserves whole (relative) path information
		
		Example of a directory 'page':
		==============================
	 */
	 /*		
		<html><head><title>Revision 683: /trunk/BizzWiki/extensions/ExtensionManager</title></head>
		<body>
		 <h2>Revision 683: /trunk/BizzWiki/extensions/ExtensionManager</h2>
		 <ul>
		  <li><a href="../">..</a></li>
		  <li><a href="Extension.php">Extension.php</a></li>
		  <li><a href="ExtensionDirectory.php">ExtensionDirectory.php</a></li>
		
		  <li><a href="ExtensionManager.i18n.php">ExtensionManager.i18n.php</a></li>
		  <li><a href="ExtensionManager.php">ExtensionManager.php</a></li>
		  <li><a href="ExtensionManager_stub.php">ExtensionManager_stub.php</a></li>
		  <li><a href="ExtensionRepository.php">ExtensionRepository.php</a></li>
		  <li><a href="Repositories/">Repositories/</a></li>
		 </ul>
		
		 <hr noshade><em><a href="http://code.google.com/">Google Code</a> powered by <a href="http://subversion.tigris.org/">Subversion</a> </em>
		</body></html>		
	*/
	/**
		This method uses the 'Simple XML' functionality of PHP5.
		
		1) get the page
		2) extract 'ul' section or else Simple XML gets confused
		3) wrap new 'document' in an arbitrary section
		4) feed to Simple XML
		5) extract information from the Simple XML object
	 */

	public function getDirectoryList( &$d )
	{
		$pattern = '/\<ul\>(.*)\<\/ul\>/siU';
		
		// try to fetch the page located on the base uri
		$error = $this->getFileCode( $d, $document );
		if ( $error !== CURLE_OK )
			return self::codeFetchURIfailed;
			
		// extract the 'ul' section
		$r = preg_match( $pattern, $document, $m );
		if ( ($r===false) || ($r===0) )
			return self::codeDirectoryEmpty;
		
		// format a document just like Simple XML likes them
		$doc = '<list><ul>'.$m[1].'</ul></list>';	
		
		// do some heavy lifting.
		try { $xml = new SimpleXMLElement( $doc ); } 
		catch( Exception $e ) { return self::codeInvalidDirectoryList; }
		
		$liste = array();
		
		if ( ($d === '.') || ($d==='..') )
			$dir = '';
		else
			$dir = $d;
		
		foreach( $xml->ul[0] as $e )
		{
			$f = (string) $e->a;
			if ( ($f==='.') || ($f==='..'))
				continue;
			$liste[] = $dir.$f;
		}	
		// help PHP a bit.
		unset( $xml );
			
		return $liste;
	}

	/**
		The Google Code repository returns directory names using a trailing '/'
	 */
	public function isDir( &$uri )
	{
		$trail = substr( $uri, -1 );
		return ( $trail === '/' ) ? true:false;
	}

	/**
		Recursive function for getting an unordered list
		of all the files of the specified directory $d
	 */
	public function getFileList( $d )
	{
		$liste = $this->getDirectoryList( $d );

		$liste2 = array();

		if (is_array( $liste ))
			foreach( $liste as $index => &$e )
			{
				if ($this->isDir( $e ))
				{
					$l = $this->getFileList( $e );
					unset( $liste[$index] );
				}	
				if (is_array( $l ))
					$liste2 = array_merge( $liste2, $l );
			}

		if (is_array( $liste ))
			return array_merge( $liste, $liste2 );		
			
		return $liste2;
	}

} // end class

//</source>