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


== Features ==
* Autoloads only when required
* On-demand loading of wiki page from filesystem
* Optional parsing (with the MediaWiki parser) of the wiki page
** All stock & extended functionality (i.e. through parser functions, parser tags) available during parsing phase

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Dowload all this extension's files and place in the desired directory e.g. '/extensions/PageServer'
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
	{
		self::$instance = $this;
		
		// get a copy of wgParser handy.
		global $wgParser;
		self::$parser = clone $wgParser;
	}
	/**
		Reports the status of this extension in the [[Special:Version]] page.
	 */	
	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	{
		global $wgExtensionCredits;

		$result = '';
					
		// Add list of managed extensions 	
				
		// add other checks here.
		
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if (isset($el['name']))		
				if ($el['name']==self::thisName)
					$el['description'] .= $result;
				
		return true; // continue hook-chain.
	}
	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	public static function loadPage( $filename )
	{
		return @file_get_contents( $filename );	
	}
	public static function loadAndParse( $filename, $title )
	{
		$contents = @file_get_contents( $filename );
		if (empty( $contents ))
			return null;
			
		$po = self::$parser->parse( $contents, $title, new ParserOptions() );
		
		return $po->getText();
	}
	
	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	
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
	
} // end class

//</source>
