<?php
/*<wikitext>
{{extension:
|RawPageTools.php
|$Id$
|Jean-Lou Dupont
}}
 
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
* [[Extension:StubManager|StubManager]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');

StubManager::createStub(	'RawPageTools', 
							$IP.'/extensions/RawPageTools/RawPageTools.php',
							null,							
							array( 'RawPageViewBeforeOutput' ),
							false
						 );

</source>

== History ==

== Code ==
</wikitext>*/
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
?>