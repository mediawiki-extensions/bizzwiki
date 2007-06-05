<?php
/*
 * SyntaxColoring.php
 *
 * @author Jean-Lou Dupont
 * 
 * <b>Purpose:</b>  This extension 'colors' a page in the NS_FILESYSTEM namespace 
 *                  based on its syntax.
 *                  Currently, only PHP type is supported.
 *
 * FEATURES:
 * =========
 * 1) No mediawiki installation source level changes
 *
 * DEPENDANCIES:
 * =============
 * 1) ExtensionClass
 *
 *
 * HISTORY:
 * ========
 *
 * TODO:
 * =====
 * 
 */

SyntaxColoring::singleton();

class SyntaxColoring extends ExtensionClass
{
	const thisName = 'SyntaxColoring';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version

	var $found;
	var $text;

	public static function &singleton( )
	{ return parent::singleton( ); }
	
	// Our class defines magic words: tell it to our helper class.
	public function SyntaxColoring() 
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
		
		$text = '';
		
		// if we are dealing with PHP:
		// $text = '<pre>'.$text.'</pre>';
		
		return true;		
	}
	public function hParserAfterTidy( &$parser, &$text )
	{
		if (! $this->found ) return true;
		$this->found = false;
		
		ob_start();
		highlight_string( $this->text );
		$text = ob_get_contents();
		ob_end_clean();
		
		$this->text = '';
		
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
	
} // end class definition.
?>