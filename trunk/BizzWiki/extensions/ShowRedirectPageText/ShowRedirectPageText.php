<?php
/*
 * ShowRedirectPageText.php
 * $Id$
 * @author Jean-Lou Dupont
 * 
 * <b>Purpose:</b>  This extension enables the display of the text included
 *                  in a 'redirect' page.
 *
 * FEATURES:
 * =========
 * 1) No mediawiki installation source level changes
 * 2) No impact on parser caching
 *
 * DEPENDANCIES:
 * =============
 * 1) ExtensionClass
 *
 * HISTORY:
 * ========
 *
 * TODO:
 * =====
 * 
 */

ShowRedirectPageText::singleton();

class ShowRedirectPageText extends ExtensionClass
{
	const defaultAction = true;   // by default, show the text
	
	const thisName = 'ShowRedirectPageText';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version

	var $found;
	var $text;
	var $actionState;

	public static function &singleton( )
	{ return parent::singleton( ); }
	
	// Our class defines magic words: tell it to our helper class.
	public function ShowRedirectPageText() 
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
		
		$this->found = false;
		$this->actionState = self::defaultAction;
	}
	public function setActionState( $s ) { $this->actionState = $s ;}

	public function hArticleViewHeader( &$article )
	{
		// check if we are dealing with a redirect page.
		$this->found = Title::newFromRedirect( $text )
		return true;		
	}
	public function OutputPageParserOutput( &$op, &$parserOutput )
	{
		// are we dealing with a redirect page?
		if ( ( !$this->found ) || ( !$this->actionState ) )return true;
		
		$op->addParserOutput( $parserOutput );
		return true;	
	}
	
} // end class definition.
?>