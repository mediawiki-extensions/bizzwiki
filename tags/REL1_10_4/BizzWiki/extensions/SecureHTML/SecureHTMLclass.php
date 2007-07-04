<?php
/*
 * SecureHTMLclass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont
 * $Id$
 * 
 */

class SecureHTMLclass extends ExtensionClass
{
	// constants.
	const thisName = 'SecureHTMLclass';
	const thisType = 'other';
	const id       = '$Id$';		
	  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function SecureHTMLclass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Enables secure HTML code on protected pages'
		);
	}
	public function setup() 
	{ parent::setup();	}


	public function hArticleSave( &$article, &$user, &$text, &$summary, $minor, $dontcare1, $dontcare2, &$flags )
	// This hook is required for adapting to 'parser cache' article saving
	{ return $this->process( $article ); }

	public function hArticleViewHeader( &$article )
	// This hook is required when 'parser caching' functionality is not used.
	{ return $this->process( $article ); }
	
	private function process( &$article )
	{
		// if article is not protected, then bail out.
		if ( !$article->mTitle->isProtected( 'edit' ) ) return true;
		
		// Now that we know we are on a protected page,
		// enable raw html for the benefit of the 'parser cache' saving process
		global $wgRawHtml;
		$wgRawHtml = true;
		
		return true; // continue hook-chain.
	}

} // END CLASS DEFINITION
?>