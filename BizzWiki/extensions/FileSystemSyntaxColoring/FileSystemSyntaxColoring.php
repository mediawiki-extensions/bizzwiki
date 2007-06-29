<?php
/*
 * FileSystemSyntaxColoring.php
 *
 * @author Jean-Lou Dupont
 * 
 * <b>Purpose:</b>  This extension 'colors' a page in the NS_FILESYSTEM namespace 
 *                  based on its syntax.
 *                  Currently, only PHP type is supported.
 *
 * FEATURES:
 * =========
 * 0) Can be used independantly of BizzWiki environment 
 * 1) No mediawiki installation source level changes
 *
 * DEPENDANCIES:
 * =============
 * 1) ExtensionClass
 *
 *
 * HISTORY:
 * ========
 * - Added <wikitext> section support
 *
 * TODO:
 * =====
 * - Implement 'hook' to get a proper syntax highlighter in place
 *   Pass file extension in parameter
 */

FileSystemSyntaxColoring::singleton();

class FileSystemSyntaxColoring extends ExtensionClass
{
	const thisName = 'FileSystem Syntax Coloring';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	
	var $found;
	var $text;

	public static function &singleton( )
	{ return parent::singleton( ); }
	
	// Our class defines magic words: tell it to our helper class.
	public function FileSystemSyntaxColoring() 
	{ 
		parent::__construct( ); 
	
		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'    => self::thisName, 
			'version' => '$Id$',
			'author'  => 'Jean-Lou Dupont', 
		);
	}
	
	public function setup()
	{
		parent::setup();
		
		$this->text  = null;
		$this->found = false;
	}

	public function hParserBeforeStrip( &$parser, &$text, &$mStripState )
	// wfRunHooks( 'ParserBeforeStrip', array( &$this, &$text, &$this->mStripState ) );
	{
		// first round of checks
		if (!$this->isPHP( $parser )) return true; // continue hook-chain
		
		// second round
		$p= '<?php';
		if (strncmp($text, $p, strlen( $p ) ) !== 0 ) return true;
		
		$this->found = true;
		$this->text = $text;
		
		// Check for a <wikitext> section
		$text = $this->getWikitext( $text );
		
		return true;		
	}
	public function hParserAfterTidy( &$parser, &$text )
	{
		// the parser gets called two times in one transaction
		// when editing/creating an article and when viewing the resulting page.
		// Use ParserCacheControl extension or patched Article::editUpdates.

		if (! $this->found ) return true;
		$this->found = false;
		
		$this->removeWikitext();
		
		ob_start();
		highlight_string( $this->text );
		$stext = ob_get_contents();
		ob_end_clean();
		
		// merge with possible <wikitext> section
		$text .= $stext;
		
		return true;	
	}
	
	private function isPHP( &$parser )
	{
		// is the namespace defined at all??
		if (!defined('NS_FILESYSTEM')) return false;
		
		// is the current article in the right namespace??
		$ns = $parser->mTitle->getNamespace();
		if ( $ns != NS_FILESYSTEM ) return false;
		
		$titre = $parser->mTitle->getText();
		
		// does the filename matches a valid PHP file extension??
		$ext   = strtolower( substr( $titre, -4, 4) );
		if ( $ext != '.php' ) return false;
		
		return true;
	}

	private function getWikitext( &$text )
	{
		$p = "/<wikitext\>(.*)(?:\<.?wikitext)>/siU";
					
		$result = preg_match( $p, $text, $m );
		if ( ($result===FALSE) or ($result===0)) return '';

		return $m[1];
	}
	private function removeWikitext()
	{
		$this->text = preg_replace( "/\<wikitext(.*)wikitext\>/siU", "wikitext", $this->text);	
	}
	
} // end class definition.
?>