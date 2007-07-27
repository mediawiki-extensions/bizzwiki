<?php
/*(($disable$))<wikitext>
ScriptingTools.php by Jean-Lou Dupont

== Purpose ==
Provides an interface to page scripting (i.e. Javascript).

== Features ==
* '#jsminandstore' magic word
* Secure: only 'edit' protected pages are allowed
* Respects BizzWiki's global setting for scripts directory '$bwScriptsDirectory'
* Supports only one Javascript code section per page

== Usage ==
* Make sure that the scripts directory is writable by the PHP process

== DEPENDANCIES ==
* [[Extension:StubManager]] extension
* ParserPhase2 extension
** Relies on the hook 'EndParserPhase2' to feed the script snippets collected through this extension.

== HISTORY ==

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

	static $patterns = array(
								'/<javascript(?:.*)\>(.*)(?:\<.?javascript>)/siU',
								'/<js(?:.*)\>(.*)(?:\<.?js>)/siU',
							);

	var $doMinAndStore;
	
	static $base = 'BizzWiki/scripts/';

	public function __construct() 
	{
		// take on global setting, if present.
		global $bwScriptsDirectory;
		if (isset( $bwScriptsDirectory ))		
			self::$base = $bwScriptsDirectory;
		
		$this->doMinAndStore = false;
	}

	/**
		This method injects the aggregated script code
		into the page before it is finally sent to the client
		browser.
	 */
	public function hEndParserPhase2( &$op, &$text )
	{
		
	}

	/**
		Command sent to intercept the Javascript located
		on this page, 'minify' it and store in the 
		configured scripts directory.
	 */
	public function mg_jsminandstore( &$parser )
	{
		// can the user perform this function?
		$title = $parser->mTitle;
		if ( !$title->isProtected( 'edit' ) ) 
			return "<b>ScriptingTools:</b> ".wfMsg('badaccess');
		
		// capture the command; it will be carried out
		// in the ParserAfterTidy hook.
		$this->doMinAndStore = true;		
	}

	/**
	
	 */
	public function hArticleSave( &$article, &$user, &$text, $summary, $minor, $dontcare1, $dontcare2, &$flags ) {}	 
	{
		// were we ask to perform the Minify and Store operation?
		if (!$this->doMinAndStore)
			return true;
			
		$code	= self::extractJsCode( $text );
		$mincode= self::minify( $code );
		$filename = self::getFileName( $article );
		$err	= self::store( $mincode, $filename );
		
		if ($err!==false)
			self::outputErrorMessage( $err );
			
		// continue hook-chain
		return true;
	}
	/**
	 */
	public static function outputErrorMessage( $errCode )
	{
		
	}	 
	/**
		Iterate through the possible patterns
		to find the Javascript code on the page.
	 */
	public static function extractJsCode( &$text )
	{
		foreach( $patterns as $pattern)		
			$r = preg_match( $pattern, $text, $m );
			if ($r>0)
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
		$minifier = new JSMin( $code );
		return $minifier->min();
	}
	/**
	 */
	public static function store( &$code, &$filename )
	{
		return file_put_contents( $filename, $code );
	}
	public static function getFileName( &$article )
	{
		$title = $article->mTitle;
		$name  = $title->getDBkey();
		
		return self::$base.'/'.$name;
	}
}  // end class declaration