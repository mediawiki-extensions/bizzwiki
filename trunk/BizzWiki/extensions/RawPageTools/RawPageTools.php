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
* Handles <nowiki><js></nowiki> Javascript section
* Handles <nowiki><css></nowiki> CSS section

== Dependancy ==
* [[Extension:StubManager|StubManager]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/RawPageTools/RawPageTools.php');
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