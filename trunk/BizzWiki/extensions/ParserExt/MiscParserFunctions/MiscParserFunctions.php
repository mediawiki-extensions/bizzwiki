<?php
/*<wikitext>
{{extension:
|MiscParserFunctions.php
|$Id$
|Jean-Lou Dupont
}}
 
== Purpose==
Provides miscellaneous parser functions (e.g. #trim, #nowikitext).

== Features ==
* <nowiki>{{#trim: input string}}</nowiki>
* <nowiki>{{#nowikitext: input string}}</nowiki>
* <nowiki>{{#gettagsection: tag | article page name }}</nowiki>
** Secure: requires the page to be protected for 'edit'

== Dependancy ==
* [[Extension:ExtensionClass|ExtensionClass]]

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/MiscParserFunctions/MiscParserFunctions.php');
</source>

== History ==

== Code ==
</wikitext>*/

class MiscParserFunctions extends ExtensionClass
{
	// constants.
	const thisName = 'MiscParserFunctions';
	const thisType = 'other';
	const id       = '$Id$';		
	  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function __construct( )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Miscellaneous parser functionality',
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	public function setup() 
	{ parent::setup(); }
	
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

if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: MiscParserFunctions extension will not work!';	
else
	MiscParserFunctions::singleton();
?>