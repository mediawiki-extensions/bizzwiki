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
	}

	public function hParserBeforeStrip( &$parser, &$text, &$mStripState )
	// wfRunHooks( 'ParserBeforeStrip', array( &$this, &$text, &$this->mStripState ) );
	{
		if (!$this->isPHP( $parser )) return true; // continue hook-chain
		
		echo 'here ';
		
		// if we are dealing with PHP:
		$text = '<pre>'.$text.'</pre>';
		
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
		$ext   = strtolower( strtr( $titre, -4, 4) );
		if ( $ext != '.php' ) return false;
		
		return true;
	}
	
} // end class definition.
?>