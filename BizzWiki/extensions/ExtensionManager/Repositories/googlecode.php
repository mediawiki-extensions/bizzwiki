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

class GoogleCode
{
	// base URI of googlecode
	const baseURI  = 'http://$1.googlecode.com/svn/';
	
	const port 		= 80;
	const timeout	= 15;

	public static function getCode( &$project, &$file, &$code )
	{
		$uri = self::formatURI( $project, $file );	
		
		return self::getPage( $uri, $code );
	}

	/**
		Formats the URI securily.
	 */
	public static function formatURI( &$project, &$file )
	{
		$project = htmlspecialchars( $project );
		$file    = htmlspecialchars( $file );
		
		$uri = self::baseURI.$file;
		$uri = str_replace( '$1', $project, $uri );
		return $uri;
	}
	/**
		Uses the CURL library to fetch the code off Google's WEB site.
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
		
} // end class

//</source>