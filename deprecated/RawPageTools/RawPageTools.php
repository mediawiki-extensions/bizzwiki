<?php
/*<!--<wikitext>-->
{{Extension
|name        = RawPageTools
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/RawPageTools/ SVN]
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
Removes 'js' and 'css' tag sections from a queried 'raw page'. This allows for documenting the page in normal page views using
'geshi' type extensions.

== Features ==
* Allows documenting Javascript/CSS pages whilst still 
* Preserving the ability to fetch the said page using 'action=raw'
* Handles <nowiki><js></nowiki> Javascript section
* Handles <nowiki><css></nowiki> CSS section
* Since only the extracted section is returned to the requesting browser, additional wikitext can be used on the page
** Improves documentation possibilities

== Usage ==
As example, suppose one as an article page where some Javascript code is documented using
a 'geshi' extension:
<pre>
 <js>
  // MediawikiClient.js
  // @author Jean-Lou Dupont
  // $Id$
  MediawikiClient = function()
  {
	// declare the custom event used to signal
	// status update re: document loading
	this.onDocStatusChange =	new YAHOO.util.CustomEvent( "onDocStatusChange" );
  ...
  </js>
</pre>
A request could be sent for the page using 'action=raw&ctype=text/javascript' and the corresponding 'js' would be
returned from the said page.

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/RawPageTools/RawPageTools_stub.php');
</source>

== History ==

== Code ==
<!--</wikitext>--><source lang=php>*/
$wgExtensionCredits[RawPageTools::thisType][] = array( 
	'name'    => RawPageTools::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => 'Provides removal of `js` and `css` tag sections for raw page functionality', 
);

class RawPageTools
{
	const thisType = 'other';
	const thisName = 'RawPageTools';
	
	static $map = array( 
						'js' 	=> 'text/javascript',
						'css'	=> 'text/css',
						);
	
	public function __construct()
	{}
	
	public function hRawPageViewBeforeOutput( &$rp, &$text )
	{
		// make sure it is a document type we support.
		$tag  = $this->getRequestedTag( $rp );
		
		if (empty( $tag ))
			return true;
		
		// try to extract a tagged section.
		// If we don't succeed, then don't touch anything.
		$section = $this->getSection( $tag, $text );
		if ( $section !== false )
			$text = $section;
		
		return true;
	}

	public function getSection( &$tag, &$content )
	{
		if (empty( $tag ))
			return false;
			
		$pattern = '/'.$tag.'(?:.*)\>(.*)(?:\<.?'.$tag.'>)/siU';
		
	 	$result = preg_match( $pattern, $content,  $section );
		if ( $result >0 )
			return $section[1];
			
		return false;			
	}
	private function getRequestedTag( &$rp )
	{
		// examines 'ctype' request parameter
		return array_search( $rp->mContentType, self::$map );			
	}
}
//</source>