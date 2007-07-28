<?php
/*(($disable$))<wikitext>
{{extension:
|ScriptingTools.php
|$Id$
|Jean-Lou Dupont
}}
== Purpose ==
Provides an interface to page scripting (i.e. Javascript).

== Features ==
* '__jsminandstore__' magic word to enable 'Minify & Store' operation
* Secure: only 'edit' protected pages are allowed
* Respects BizzWiki's global setting for scripts directory '$bwScriptsDirectory'
* Supports only one Javascript code section per page
* Integrates with 'geshi' extensions highlighting the 'js' or 'javascript' tagged section

== Usage ==
* Make sure that the scripts directory is writable by the PHP process

== DEPENDANCIES ==
* [[Extension:StubManager]] extension
* ParserPhase2 extension
** Relies on the hook 'EndParserPhase2' to feed the script snippets collected through this extension
** ParserPhase2 extension is *not* required for the 'Minify and Store' functionality.

== HISTORY ==

== Code ==
</wikitext>*/

global $wgExtensionCredits;
$wgExtensionCredits[ScriptingToolsClass::thisType][] = array( 
	'name'        => ScriptingToolsClass::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Provides an interface between MediaWiki scripting tools',
	'url' 		=> StubManager::getFullUrl(__FILE__),						
);

class ScriptingToolsClass
{
	const thisName = 'ScriptingTools';
	const thisType = 'other';

	static $magicWord = '__jsminandstore__';
	
	static $patterns = array(
								'/<javascript(?:.*)\>(.*)(?:\<.?javascript>)/siU',
								'/<js(?:.*)\>(.*)(?:\<.?js>)/siU',
							);

	// relative directory from MediaWiki installation.
	static $base = 'BizzWiki/scripts/';

	public function __construct() 
	{
		// take on global setting, if present.
		global $bwScriptsDirectory;
		if (isset( $bwScriptsDirectory ))		
			self::$base = $bwScriptsDirectory;
	}

	/**
		This method injects the aggregated script code
		into the page before it is finally sent to the client
		browser.
	 */
	public function hEndParserPhase2( &$op, &$text )
	{
		// TODO	
	}

	/**
		Remove the 'magic word' when we display the page.		
	 */
	public function hParserAfterTidy( &$parser, &$text )
	{
		self::findMagicWordAndRemove( $text, true );
		return true;		
	}

	/**
		Grab the JS code and save it in the configured scripts directory
		IFF we find the 'magic word' AND the page is protected for 'edit'.	
	 */
	public function hArticleSave( &$article, &$user, &$text, $summary, $minor, $dontcare1, $dontcare2, &$flags )
	{
		// did we find the magic word asking us
		// to perform the operation?
		if (!self::findMagicWordAndRemove( $text, false ))
			return true;

		// is the page secure?
		$title = $article->mTitle;			
		if ( !$title->isProtected( 'edit' ) ) 			
			return true;
			
		$code		= self::extractJsCode( $text );
		$mincode	= self::minify( $code );
		$filename 	= self::getFileName( $article );
		$err		= self::store( $mincode, $filename );
	
		if ($err===false)
			self::outputErrorMessage( $err );
		
		// continue hook-chain
		return true;
	}
	/**
		TODO.
	 */
	public static function outputErrorMessage( $errCode )
	{ }
		 
	/**
		Iterate through the possible patterns
		to find the Javascript code on the page.
	 */
	public static function extractJsCode( &$text )
	{
		foreach( self::$patterns as $pattern)
			if (preg_match( $pattern, $text, $m ) > 0)
				return $m[1];

		return null;
	}
	/**
		Minify the Javascript code using the provided
		external 'Crockford' engine.
	 */
	public static function minify( &$code )
	{
		require_once( dirname(__FILE__).'/jsmin.php' );
		return JSMin::minify( $code );
	}
	/**
		Store the minified code in the specified directory.
	 */
	public static function store( &$code, &$filename )
	{
		return file_put_contents( $filename, $code );
	}
	/**
		Return the filename to use to store the JS file.
		If the page title doesn't contain a '.js' ending,
		then add one; this way, the file in the filesystem
		will be more 'normalized'.
	 */
	public static function getFileName( &$article )
	{
		$title = $article->mTitle;
		$name  = $title->getDBkey();
		
		// is there a '.js' extension already in the title name?
		// if not, add one.
		if (strpos( $name, '.js' )===false)
			$name .= '.js';
		
		global $IP;
		return $IP.'/'.self::$base.$name;
	}
	/**
		Returns the result of the search for the proprietary
		'magic word' on the page. Optionally removes all the
		occurences of the 'magic word'.
	 */
	public static function findMagicWordAndRemove( &$text, $remove = false )
	{
		$r = strpos( $text, self::$magicWord );
		if ( $remove )
			$text = str_replace( self::$magicWord, '', $text );
		
		return ($r === false) ? false:true;
	}
	
}  // end class declaration