<?php
/*(($disable$))<wikitext>
{{Extension
|name        = ScriptingTools
|status      = stable
|type        = Parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ScriptingTools/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
== Purpose ==
Provides an interface to page scripting (i.e. Javascript). 
The extension provides 'minify and store' functionality for Javascript scripts.
Furthermore, a special parser function '#epropset' is provided as bridge between an HTML based
page and JS scripts associated with the page (e.g. Mootools based widgets).

== Features ==
* The page can contain normal wikitext without disturbing the intended functionality of this extension
* '__jsminandstore__' magic word to enable 'Minify & Store' operation
* Appends '.js' to the file if the source page doesn't end with the '.js' extension
* Secure: only 'edit' protected pages are allowed
* Respects BizzWiki's global setting for scripts directory '$bwScriptsDirectory'
* Supports only one Javascript code section per page
* Integrates with 'geshi' extensions highlighting the 'js' or 'javascript' tagged section
* Parser Function '#epropset'
** Really meant to be called through 'parser after tidy' functionality of [[Extension:ParserPhase2]] extension

== Usage ==
1) For the 'minify and store' functionality, just edit the desired page and put the JS code
within 'js' tagged section and place the magic word '__jsminandstore__' inside a comment section within the code OR
just in the wikitext page itself.
2) For the 'scripting bridge', more details to come.

== Notes ==
* Make sure that the scripts directory is writable by the PHP process

== DEPENDANCIES ==
* [[Extension:StubManager]] extension
* For the 'scripting bridge' functionality, the following constitute dependencies:
** [[Extension:PageFunctions]] extension
** [[Extension:ParserPhase2]] extension
*** Relies on the hook 'EndParserPhase2' to feed the script snippets collected through this extension
*** ParserPhase2 extension is *not* required for the 'Minify and Store' functionality

== Installation ==
To install outside the BizzWiki platform:
* Download [[Extension:StubManager]]
** Place 'StubManager.php' file in '/extensions' directory
* Download [[Extension:PageFunctions]] extension (if required)
** Place 'PageFunctions.php' in '/extensions/PageFunctions' directory
** Follow the instructions from the extension's description page [[Extension:PageFunctions]]
* Download the extension files from the SVN repository
** Place the files in '/extensions/ScriptingTools' directory
* Perform the following changes to 'LocalSettings.php':
<source lang=php>
require('/extensions/StubManager.php');

StubManager::createStub(	'ScriptingToolsClass', 
							'/extensions/ScriptingTools/ScriptingTools.php',
							null,					// i18n file			
							array('ArticleSave', 'ParserAfterTidy' ),	// hooks
							false, 					// no need for logging support
							null,					// tags
							null,					// parser Functions
							null
						 );
</source>

== HISTORY ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

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

	const open_js  = '<script type= "text/javascript">/*<![CDATA[*/';
	const close_js = '/*]]>*/</script>';	

	// relative directory from MediaWiki installation.
	static $base = 'BizzWiki/scripts/';

	public function __construct() 
	{
		// take on global setting, if present.
		global $bwScriptsDirectory;
		if (isset( $bwScriptsDirectory ))		
			self::$base = $bwScriptsDirectory;
	}

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
   Scripting Helper: interface between MediaWiki and Javascript
   %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */
   var $Elements = array();
   
   /**
		Function: mg_epropset
		
				'Element Property Set'
				((%#epropset: element id contained in pageVariable|property to set|value%))
		
		Parameters:
		
		parser - passed by MediaWiki
		
		pageVariable - page variable containing the element id to set
		property     - the property
		value        - the value to set the property to
		
    */
	public function mg_epropset( &$parser, &$pageVariable, &$property, &$value, &$verbose=null )
	{
		// We rely on [[Extension:PageFunctions]] for page level variables.
		// Usually, the 'pageVariable' containing the element id would have been
		// captured on the page using '((%#varcapset|eid| element id here %))' parser function
		// supported through [[Extension:ParserPhase2]] extension.
		wfRunHooks( 'PageVarGet', array( $pageVariable, &$eid ));
		
		$this->Elements[$eid][$property] = $value;
		
		// for documentation purpose.
		if ($verbose !== null)
			return 'element id='.$eid.' property='.$property.' value='.$value;
			
		return null;
	}

	public function mg_epropset2( &$parser, &$eid, &$property, &$value )
	{
		$this->Elements[$eid][$property] = $value;
	}

	/**
		Function: hParserAfterTidy
		
		This method injects the aggregated script code
		into the page before it is finally sent to the client
		browser / saved in the parser cache.
		
		Parameters:
		
		op   - OutputPage object
		text - string contained the page's text
	 */
	public function hParserAfterTidy( &$op, &$text )
	{
		// Minify & Store Functionality
		self::findMagicWordAndRemove( $text, true );
				
		if (empty( $this->Elements )) return true;
		
		/* go through all the properties we collected
		   and place them in the 'body' of the document
		   using JS code.
		*/
		$elements = array();
		$liste    = null;
		$first_element = true;
		
		foreach( $this->Elements as $eid => &$kvpair )
		{
			if ($first_element) { $elements = '"'.$eid.'"'; $first_element=false; }
			else				$elements .= ', "'.$eid.'"';
			
			$liste .= 'var Element'.$eid."= {\n";
			$first = true;
			foreach( $kvpair as $key => $value )
			{
				if (!$first) $liste .= ",\n"; 
				else $first = false;
				
				if (is_numeric( $value ))
					$liste .= ' "'.$key.'":'.$value;
				else
					$liste .= ' "'.$key.'":"'.$value.'"';
			}
			$liste .= "\n };\n";
		}

		$script  = "\n".self::open_js;
		$script .= "\n".'var PageVars = function() {';
		$script .= "\n".' var Elements = new Array ( '.$elements.' );';		
		$script .= "\n ".$liste;
		$script .= '};';
		$script .= "\n".self::close_js;
		$text .= $script;
		
		return true;
	}


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
   Minify & Store functionality
   %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */

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