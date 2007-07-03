<?php
/*
 * PageTools.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 *
 * Purpose:  Provides a 'magic word' interface to retrieve
 *           useful page level information.           
 *
 * Features:
 * *********
 *
 * {{#pageincategory: 'category' }} 
 *    returns 'true' if the current page is categorised with 'category'
 *
 * {{#pagenumcategories:}}
 *    returns the number of categories found for the current page.
 *
 * {{#pagecategory: 'index' }}
 *    returns the category title indexed with 'index'
 *
 * {{#pagetitle: new title name}}
 *
 * {{#pagetitleadd: text to be added to the title name}} 
 *
 * {{#pagesubtitle: text to be added to the page's subtitle }}
 *
 * {{#pageexists: 'article title' }}
 *
 * {{#pagepath:}}
 *
 * {{#pageext:}}                    current article
 * {{#pageext: 'article title' }}   specified article
 *
 * DEPENDANCIES:
 * 1) 'ArticleEx' extension (from v1.6)
 * 2) 'ExtensionClass' extension (from v1.2)
 *
 * Tested Compatibility:  MW 1.8.2, 1.9.3
 *
 * HISTORY:
 * -- Version 1.0:	initial availability
 * -- Version 1.1:  Added 'pagetitle': to modify the page's title. 
 *                  Added 'pagetitleadd': to add to the current page title.
 *                  Added 'pagesubtitle': to modify the page's subtitle     
 * -- Version 1.2:  Corrected $index bug in 'pagecategory' function
 *                  This correction is due to the change in ExtensionClass behavior.
 *
 * -- Version 1.3:  Added 'pageexists': does the article title exists?
 * -- Version 1.4:  Added 'pagepath': returns the global variable '$wgArticlePath'
 * -- Version 1.5:  Added 'pageext': returns the 'extension' of the title page. 
 =================  Moved to BizzWiki
 *
 */
$wgExtensionCredits['other'][] = array( 
	'name'    => 'PageTools Extension', 
	'version' => '$Id$',
	'author'  => 'Jean-Lou Dupont', 
);

// Let's create a single instance of this class
PageTools::singleton();

class PageTools extends ExtensionClass
{
	static $mgwords = array('pageincategory', 'pagenumcategories' , 'pagecategory', 
							'pagetitle','pagetitleadd',
							'pagesubtitle',
							'pageexists', 'pagepath',
							'pageext' );
	
	public static function &singleton( )
	{ return parent::singleton(); }
	
	// Our class defines magic words: tell it to our helper class.
	public function PageTools()
	{	return parent::__construct( self::$mgwords );	}

	// ===============================================================

	public function mg_pageincategory( &$parser )
	{
		if (!$this->checkArticleExClass() )	return;
		$params = $this->processArgList( func_get_args(), true );

		if (empty($params[0])) return;
		
		// format as Mediawiki wants it ('DBkey' form)
		$cat = str_replace( ' ', '_', $params[0] );
			
		global $wgArticle;
		if (empty($wgArticle->categories)) return;
			
		return in_array($cat, $wgArticle->categories );
	}
	public function mg_pagenumcategories( &$parser )
	{
		if (!$this->checkArticleExClass() ) return;
		$params = $this->processArgList( func_get_args(), true );

		global $wgArticle;
		return (count($wgArticle->categories));
	}
	public function mg_pagecategory( &$parser )
	{
		if (!$this->checkArticleExClass() )	return;

		$params = $this->processArgList( func_get_args(), true );
		$index = $params[0];
		
		if (empty($index)) $index = 0 ; // v1.2 correction.
		
		global $wgArticle;
		$compte = count($wgArticle->categories);
		if ($index>=$compte) return;

		$cat = $wgArticle->categories[$index];
		// reformat to 'text' form (from 'DBkey' form).		
		return str_replace('_', ' ', $cat);
	}
	
	public function mg_pagetitle( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );

		global $wgOut;
		$wgOut->setPageTitle( $params[0] );
	}
	
	var $titleAddition = null;
	var $hookInPlace = false;
	public function mg_pagetitleadd( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );
		$this->titleAddition = $params[0];

		// only hook when we really need it.
		if (!$this->hookInPlace)
		{
			global $wgHooks;	
			$wgHooks['BeforePageDisplay'][]= array($this, 'hBeforePageDisplay');
			$this->hookInPlace = true;
		}			
	}
	public function mg_pagesubtitle( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );
		global $wgOut;
		$wgOut->setSubtitle( $params[0] );
	}
	public function mg_pageexists( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );
		
		$a = $this->getArticle( $params[0] );
		if (is_object($a)) 
			$id=$a->getID();
		else $id = 0;
		
		return ($id == 0 ? false:true);		
	}
	public function mg_pagepath( &$parser )
	{
		global $wgArticlePath;
		return $wgArticlePath;	
	}
	public function mg_pageext( &$parser, $titre )
	{
		global $wgArticle;
		
		if (empty( $titre ))
			$titre = $wgArticle->mTitle->getBaseText();
		
		// extension starts with the ending '.'
		$pos   = strrpos( $titre, '.' );
		if ($pos === false) return null;   // didn't find any '.'
		
		// ok we found a '.', extract what's after it.
		return substr( $titre, $pos+1 );
	}

	// =========================================================================	
	public function hBeforePageDisplay( $op )
	{
		if (!empty($this->titleAddition))
			$op->setPageTitle( $op->getPageTitle()." ".$this->titleAddition );
		
		return true; // continue chain.	
	}
	private function checkArticleExClass( )
	{
		global $wgArticle;
		return ( get_class($wgArticle) == 'ArticleExClass' );	
	}

} // end class	
?>