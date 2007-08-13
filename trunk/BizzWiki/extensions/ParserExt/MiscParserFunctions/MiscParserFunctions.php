<?php
/*<!--<wikitext>-->
{{Extension
|name        = MiscParserFunctions
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ParserExt/MiscParserFunctions/ SVN]
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
Provides miscellaneous parser functions (e.g. #trim, #nowikitext).

== Features ==
* <nowiki>{{#trim: input string}}</nowiki>
* <nowiki>{{#nowikitext: input string}}</nowiki>
* <nowiki>{{#gettagsection: tag | article page name }}</nowiki>
** Secure: requires the page to be protected for 'edit'

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/XYZ/XYZ_stub.php');
</source>

== History ==
* Adapted to StubManager's stubbing facility

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[MiscParserFunctions::thisType][] = array( 
	'name'        => MiscParserFunctions::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Miscellaneous parser functionality',
	'url' => StubManager::getFullUrl(__FILE__),			
);

class MiscParserFunctions
{
	// constants.
	const thisName = 'MiscParserFunctions';
	const thisType = 'other';
	  
	function __construct( )
	{	}
	
	/**
		Trims a string.
	 */
	public function mg_trim( &$parser, &$input )
	{ 
		return trim( $input );
	}
	/**
		Wraps a string in <nowiki> section.
	 */
	public function mg_nowikitext( &$parser, &$input )
	{
		return '<nowiki>'.htmlspecialchars( $input ).'</nowiki>';
	}
	
	/**
		Gets the text enclosed in the specified tag section
		from the specified page article.
	 */
	public function mg_gettagsection( &$parser, &$tag, &$page )
	{
		if (!isset( $page ) || empty( $page ))
			return null;
			
		if (!$this->canProcess( $parser->mTitle, $title ))
			return wfMsg('badaccess');
		
		// just make sure we are not feed with a pattern that would
		// break preg_match.
		$t = preg_quote( $tag );
		$pattern = '/'.$t.'(?:.*)\>(.*)(?:\<.?'.$t.'>)/siU';
		
		$content = $this->getRawPage( $page );
		
	 	$result = preg_match( $pattern, $content,  $section );
		
		return $section[1];
	}
# %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	private static function &getTitle( $page )
	{
		return Title::newFromText( $page );
	}

	/**
		Gets the 'raw' content from an article page.
	 */
	public function getRawPage( &$obj )
	{
		if (!isset( $obj ) || empty( $obj ) )
			return null;
			
		if (!is_a( $obj, 'Title' ))			
			$title   = self::getTitle( $obj );	
		else
			$title = $obj;
			
		$article = new Article( $title );
		if ( $article->getID() == 0 )
			return null;
			
		return $article->getContent();	
	}

	/**
			Security Verification
	 */
	private function canProcess( &$obj, &$title )
	{
		if (is_string( $obj ))
			$title = self::getTitle( $obj );
		elseif (is_a( $obj, 'Article'))
			$title = $obj->mTitle;
		elseif (is_a( $obj, 'Title'))
			$title = $obj;
		else
			return false;
		
		// check protection status
		if ( $title->isProtected( 'edit' ) ) return true;
		
		return false;
	}


} // end class
//</source>