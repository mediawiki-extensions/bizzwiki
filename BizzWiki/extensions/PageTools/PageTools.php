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

== Usage ==

* {{#pagetitle: new title name}}
* {{#pagesubtitle: text to be added to the page's subtitle }}
* {{#pageexists: 'article title' }}

== DEPENDANCIES ==
* ExtensionClass extension
* ParserPhase2 extension

== HISTORY ==

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
	public static function &singleton( )
	{ return parent::singleton(); }
	
	// Our class defines magic words: tell it to our helper class.
	public function PageTools()
	{	return parent::__construct( );	}

	// ===============================================================

	public function pp2_pagetitle( &$wgtitle, &$title )
	{ return $this->setTitle( $title ); }

	public function mg_pagetitle( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );
		return $this->setTitle( $params[0] );
	}
	private function setTitle( &$title )
	{
		global $wgOut;
		$wgOut->setPageTitle( $title );
	}

	// ===============================================================

	public function pp2_pagesubtitle( &$wgtitle, &$title )
	{ return $this->setSubTitle( $title ); }

	public function mg_pagesubtitle( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );
		$this->setSubTitle( $params[0] );
	}
	private function setSubTitle( &$title )
	{
		global $wgOut;
		$wgOut->setSubtitle( $title );
	} 

	// ===============================================================

	public function pp2_pageexists( &$wgtitle, &$title )
	{ return $this->doesPageExists( $title ); }
	
	public function mg_pageexists( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );
		return $this->doesPageExists( $params[0] );
	}

	private function doesPageExists( &$title ) 
	{
		$a = $this->getArticle( $title );
		if (is_object($a)) 
			$id=$a->getID();
		else $id = 0;
		
		return ($id == 0 ? false:true);		
	}

} // end class	
?>