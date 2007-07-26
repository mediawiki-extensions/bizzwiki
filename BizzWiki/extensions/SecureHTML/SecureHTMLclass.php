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
	  
	static $enableExemptNamespaces = true;
	static $exemptNamespaces;
	  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function SecureHTMLclass( )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Enables secure HTML code on protected pages',
			'url' => self::getFullUrl(__FILE__),			
		);
	
		// default exempt namespaces from the BizzWiki platform.
		// won't affect installs of the extension outside the BizzWiki platform.
		if (defined('NS_BIZZWIKI'))   self::$exemptNamespaces[] = NS_BIZZWIKI;
		if (defined('NS_FILESYSTEM')) self::$exemptNamespaces[] = NS_FILESYSTEM;
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
		if (!$this->canProcess( $article ) ) return true;
				
		// Now that we know we are on a protected page,
		// enable raw html for the benefit of the 'parser cache' saving process
		global $wgRawHtml;
		$wgRawHtml = true;
		
		return true; // continue hook-chain.
	}
	private function canProcess( &$obj )
	{
		if (!is_object( $obj ))
			return false; // paranoia
			
		if (is_a( $obj, 'Article'))
			$title = $obj->mTitle;
		elseif (is_a( $obj, 'Title'))
			$title = $obj;
		else
			return false;
		
		if (self::$enableExemptNamespaces)
		{
			$ns = $title->getNamespace();
			if ( !empty(self::$exemptNamespaces) )
				if ( in_array( $ns, self::$exemptNamespaces) )
					return true;	
		}
		
		// check protection status
		if ( $title->isProtected( 'edit' ) ) return true;
		
		return false;
	}

} // END CLASS DEFINITION
?>