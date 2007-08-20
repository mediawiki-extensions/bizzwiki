<?php
/*<!--<wikitext>-->
{{Extension
|name        = PageServer
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/PageServer/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose==
Provides parser functions (#mwmsg, #mwmsgx) and services to other extensions ( 'loadAndParse' ).

== Features ==
* Loads only when required (i.e. Autoloading)
* On-demand loading of wiki page from filesystem
* Optional parsing (with the MediaWiki parser) of the wiki page
** All stock & extended functionality (i.e. through parser functions, parser tags) available during parsing phase
* Parser functions:
** #mwmsg    ( 'MediaWiki Message' )
** #mwmsgx   ( 'MediaWiki Message with parameters' )

== Usage ==
=== Parser Functions ===
* <nowiki>{{#mwmsg:msg id}}</nowiki> will output the raw message from the message cache
* <nowiki>{{#mwmsgx:msg id [|p1][|p2][|p3][|p4]}}</nowiki> will output the parsed message from the message cache
including up to 4 parameters (i.e. the $n parameters when using 'wfMsgForContent' global function)
=== Server to other extensions ===
Use <code>PageServer::XYZ</code> where XYZ is the desired function name.

== Dependancy ==
* [[Extension:StubManager]]

== Installation ==
To install independantly from BizzWiki:
* Download and install [[Extension:StubManager]]
* Dowload all this extension's files and place in the desired directory e.g. '/extensions/PageServer'
and place after the declaration of [[Extension:StubManager]]:
<source lang=php>
require('extensions/PageServer/PageServer_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[PageServer::thisType][] = array( 
	'name'    	=> PageServer::thisName,
	'version' 	=> StubManager::getRevisionId( '$Id$'),
	'author'  	=> 'Jean-Lou Dupont',
	'description' => "Provides functionality to load & parse wiki pages stored in the filesystem.", 
	'url' 		=> StubManager::getFullUrl(__FILE__),		
);

class PageServer
{
	const thisType = 'other';
	const thisName = 'PageServer';
	
	static $instance = null;
	static $parser;
	
	public function __construct() 
	{ self::$instance = $this;	}
	
	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	// SERVICES to other extensions
	//
	
	/**
		Called using PageServer::loadPage()
	 */
	public static function loadPage( &$filename )
	{
		return @file_get_contents( $filename );	
	}

	/**
		Called using PageServer::loadAndParse()

		This function is targeted at 'template' pages where newline characters
		are used to document the said page but would otherwise cause 
		unnecessary 'holes' once processed.
		
		E.g. use when combining an 'header' page to a 'form' page where the
		'header' page consists mostly of commands/templates etc.

	 */
	public static function loadAndParse( &$filename, &$title, $minify = false )
	{
		$contents = @file_get_contents( $filename );
		if (empty( $contents ))
			return null;
		
		if ($minify)
			$contents = str_replace("\n", '', $contents );
			
		self::initParser();
		$po = self::$parser->parse( $contents, $title, new ParserOptions() );
		
		return $po->getText();
	}
	public static function load( &$filename, $minify = false )
	{
		$contents = @file_get_contents( $filename );
		if (empty( $contents ))
			return null;
		
		if ($minify)
			$contents = str_replace("\n", '', $contents );

		return $contents;		
	}
		
	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	// PARSER FUNCTIONS
	//	
	/**
		Parser Function: #mwmsg
	 */
	public function mg_mwmsg( &$parser, $msgId )
	{
		return wfMsg( $msgId );	
	}

	/**
		Parser Function: #mwmsgx
	 */
	public function mg_mwmsgx( &$parser, $msgId, $p1 = null, $p2 = null, $p3 = null, $p4 = null )
	{
		return wfMsgForContent( $msgId, $p1, $p2, $p3, $p4 );	
	}

	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	// HELPER FUNCTIONS
	// NOTE: CAN'T BE CALLED BY OTHER EXTENSIONS
	//	
	private static function initParser()
	{
		if (self::$parser !== null)	
			return;

		// get a copy of wgParser handy.
		global $wgParser;
		self::$parser = clone $wgParser;
	}
	
} // end class

//</source>
