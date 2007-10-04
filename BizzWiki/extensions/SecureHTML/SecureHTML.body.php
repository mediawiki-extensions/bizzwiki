<?php
/**
 * @author Jean-Lou Dupont
 * @package SecureHTML
 */
//<source lang=php>
$wgExtensionCredits[SecureHTML::thisType][] = array( 
	'name'        => SecureHTML::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Enables secure HTML code on protected pages',
	'url' 		=> 'http://mediawiki.org/wiki/Extension:SecureHTML',			
);

class SecureHTML
{
	// constants.
	const thisName = 'SecureHTML';
	const thisType = 'other';
	  
	static $enableExemptNamespaces = true;
	static $exemptNamespaces = array();

	public static function addExemptNamespaces( $list )
	{
		if (!is_array( $list ))	
			$list = array( $list );
			
		self::$exemptNamespaces = array_merge( self::$exemptNamespaces, $list );
	}

	function __construct( )
	{
		// default exempt namespaces from the BizzWiki platform.
		// won't affect installs of the extension outside the BizzWiki platform.
		if (defined('NS_BIZZWIKI'))   self::$exemptNamespaces[] = NS_BIZZWIKI;
		if (defined('NS_FILESYSTEM')) self::$exemptNamespaces[] = NS_FILESYSTEM;
	}
	/**
		This hook is required for adapting to 'parser cache' article saving
	 */
	public function hArticleSave( &$article, &$user, &$text, &$summary, $minor, $dontcare1, $dontcare2, &$flags )
	{ return $this->process( $article ); }
	/**
		 This hook is required when 'parser caching' functionality is not used.	
	 */
	public function hArticleViewHeader( &$article )
	{ return $this->process( $article ); }

	/**
		Attempt article processing with 'raw html tags'.
	 */	
	private function process( &$article )
	{
		if (!$this->canProcess( $article ) ) return true;
				
		// Now that we know we are on a protected page,
		// enable raw html for the benefit of the 'parser cache' saving process
		global $wgRawHtml;
		$wgRawHtml = true;
		
		return true; // continue hook-chain.
	}
	/**
		Verify's article protection status.
	 */
	private function canProcess( &$obj )
	{
		if (!is_object( $obj ))
			return false; // paranoia
			
		if (is_a( $obj, 'Article'))
			$title = $obj->mTitle;
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
//</source>