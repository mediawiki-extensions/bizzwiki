<?php
/*(($disable$))<wikitext>
PageFunctions.php by Jean-Lou Dupont

== Purpose ==
Provides a 'magic word' interface to retrieve useful page level information.           

== Features ==
* Page Title change '#pagetitle'
* Page Sub-title change '#pagesubtitle'
* Page existence check '#pageexists'
* Page scope variable set '#varset'
* Page scope variable get '#varget'
* Wikitext conditional 'show'
* Hook based variable get and set

== Usage ==

* <nowiki>{{#pagetitle: new title name}}</nowiki>
* <nowiki>{{#pagesubtitle: text to be added to the page's subtitle }}</nowiki>
* <nowiki>{{#pageexists: 'article title' }}</nowiki>
* <nowiki>{{#varset:variable name|value }}</nowiki>
* <nowiki>{{#varget:variable name}}</nowiki>
* <nowiki>{{#varaset:variable name|array key|array value}}</nowiki>
* <nowiki>{{#varaget:variable name|array key}}</nowiki>
* <nowiki>{{#cshow:group|text}}</nowiki>
** Where 'group' is the user's group membership check to perform

Of course, the same magic words can be used in the context of 'ParserCache2' i.e.
* <nowiki>(($#pagetitle: new title name$))</nowiki>
* <nowiki>(($#pagesubtitle: text to be added to the page's subtitle $))</nowiki>
* <nowiki>(($#pageexists: 'article title' $))</nowiki>
* <nowiki>(($#varset:variable name|value $))</nowiki>
* <nowiki>(($#varget:variable name $))</nowiki>
* <nowiki>(($#varaset:variable name|array key|array value$))</nowiki>
* <nowiki>(($#varaget:variable name|array key$))</nowiki>
* <nowiki>(($#cshow:group|text$))</nowiki>

== DEPENDANCIES ==
* ExtensionClass extension
* ParserPhase2 extension (optional)

== HISTORY ==
* Adjusted singleton invocation to accomodate more PHP versions
* Added hook 'PageVarGet'
* Added hook 'PageVarSet'

</wikitext>*/

class PageFunctionsClass extends ExtensionClass
{
	const thisName = 'PageFunctions';
	const thisType = 'other';
	const id       = '$Id$';	

	var $pageVars;

	public static function &singleton( )
	{ return parent::singleton(); }
	
	// Our class defines magic words: tell it to our helper class.
	public function PageFunctionsClass()
	{	
		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Provides page scope functions',
			'url' => self::getFullUrl(__FILE__),						
		);
	
		$this->pageVars = array();
		return parent::__construct( );	
	}

	// ===============================================================
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

	// ===============================================================
	/**
		Hook based Page Variable 'get'
		%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	 */
	public function hPageVarGet( &$varname, &$value )
	{
		$value = @$this->pageVars[ $varname ];		
		return true; // continue hook-chain.
	}
	/**
		Hook based Page Variable 'set'
		%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	 */
	public function hPageVarSet( &$varname, &$value )
	{
		$this->pageVars[ $varname ] = $value;		
		return true; // continue hook-chain.
	}
	public function mg_varset( &$parser ) 
	{
		$params = $this->processArgList( func_get_args(), true );
		$this->pageVars[ $params[0] ] = $params[1];		
	}
	public function mg_varget( &$parser ) 
	{
		$params = $this->processArgList( func_get_args(), true );
		return @$this->pageVars[ $params[0] ];		
	}
	/**
		Sets a variable to an array.
		param 0: variable name
		param 1: array key
		param 2: array value corresponding to key.
	 */
	public function mg_varaset( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );
		$this->pageVars[ $params[0] ][ $params[1] ] = $params[2];		
	
	}
	/**
		Gets a variable to an array.
		param 0: variable name
		param 1: array key
	 */
	public function mg_varaget( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );
		return @$this->pageVars[ $params[0] ][ $params[1] ];		
	}
	// ===============================================================
	public function mg_cshow( &$parser, &$group, &$text )
	// Conditional Show: if user is part of $group, then allow for '$text'
	// Parser Cache friendly of 'ConditionalShowSection' extension.
	{
		global $wgUser;
		$g = $wgUser->getEffectiveGroups();
		if (in_array( $group, $g ))
			return $text;
	}
} // end class	

// Let's create a single instance of this class
PageFunctionsClass::singleton();
?>