<?php
/*
 * ShowRedirectPageText.php
 *
 * $Id$
 * @author Jean-Lou Dupont
 * 
 * <b>Purpose:</b>  This extension enables the display of the text included
 *                  in a 'redirect' page.
 *
 *                  The inclusion of wikitext in a redirect page is helpful
 *                  in situations, for example, where redirects are used to manage a 
 *                  'cluster' of Mediawiki serving machines.
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
 * - Clean up the '#redirect' wikitext before displaying
 *
 */

ShowRedirectPageText::singleton();

class ShowRedirectPageText extends ExtensionClass
{
	const defaultAction = true;   // by default, show the text
	
	const thisName = 'ShowRedirectPageText';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version

	var $found;
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
		
		$this->found = null;
		$this->actionState = self::defaultAction;
	}
	public function setActionState( $s ) { $this->actionState = $s ;}

	public function hArticleViewHeader( &$article )
	{
		// check if we are dealing with a redirect page.
		$this->found = Title::newFromRedirect( $article->getContent() );
		
		return true;		
	}
	public function hOutputPageParserOutput( &$op, $parserOutput )
	{
		// are we dealing with a redirect page?
		if ( ( !is_object($this->found) ) || ( !$this->actionState ) )return true;
	
		// take care of re-entrancy
		if ( !is_object($this->found) ) return true;
		$this->found = null;
		
		$op->addParserOutput( $parserOutput );
		return true;	
	}
	
} // end class definition.
?>