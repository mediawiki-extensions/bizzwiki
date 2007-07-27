<?php
/*(($disable$))<wikitext>
ScriptingTools.php by Jean-Lou Dupont

== Purpose ==
Provides an interface to page scripting (i.e. Javascript).

== Features ==
* Respects BizzWiki's global setting for scripts directory '$bwScriptsDirectory'

== Usage ==

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
		$err	= self::store( $mincode );
		
		if ($err!==false)
			self::outputErrorMessage( $err );
			
		// continue hook-chain
		return true;
	}

	public static function extractJsCode( &$text )
	{
		
	}
	
	public static function minify( &$code )
	{
		
	}
	
	public static function store( &$code )
	{
		
	}
	
}  // end class declaration