<?php
/*
 * InterWikiLinkManagerClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 * 
 */

class InterWikiLinkManagerClass extends ExtensionClass
{
	// constants.
	const thisName = 'InterWikiLinkManager';
	const thisType = 'other';

	const rRead    = "read";
	const rEdit    = "edit";
	const mPage    = "Main Page";

	static $mgwords = array( 'iwl' );

	// preload wikitext
	// ================
	
	const header = "
{| border='1'
! Prefix || URI || Local || Trans";
	const footer = "
|}";
	const sRow = "
|-";
	const sCol = "
| ";

	// Link Table	
	var $iwl;     // the table read from the database
	var $new_iwl; // the desired table elements
	  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function InterWikiLinkManagerClass( $mgword = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( self::$mgwords );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => '$Id$',
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Manages the InterWiki links table. Namespace for extension is '
		);
	}
	public function setup() 
	{ 
		parent::setup();
		
		$this->iwl     = array();
		$this->new_iwl = array();
		
		global $wgMessageCache, $wgFileManagerLogMessages;
		foreach( $wgFileManagerLogMessages as $key => $value )
			$wgMessageCache->addMessages( $wgFileManagerLogMessages[$key], $key );		
	}
		public function hUpdateExtensionCredits( &$sp, &$extensionTypes )
	// setup of this hook occurs in 'ExtensionClass' base class.
	{
		global $wgExtensionCredits;

		// first check if the proper rights management class is in place.
		if (defined('NS_INTERWIKI'))
			$hresult = 'defined.';
		else
			$hresult = '<b>not defined!</b>';

		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if ($el['name']==self::thisName)
				$el['description'].=$hresult;
				
		return true; // continue hook-chain.
	}
	
	public function mg_iwl( &$parser, $prefix, $uri, $local, $trans, $dotableline = true )
	// magic word handler function
	{
		if ( $r = $this->checkElement( $prefix, $uri, $local, $trans, $errCode ) )
		{
			$el = $this->new_iwl[] = array(	'prefix' => $prefix, 
											'uri'    => $uri, 
											'local'  => $local, 
											'trans'  => $trans 	);
		}
		// was there an error?
		if ( !$r )
			return $this->getErrMessage( $errCode );

		
		if ( $dotableline )
			return $this->formatLine( $el );
	}	
	
	public function hArticleSave( &$article, &$user, &$text, &$summary, $minor, $dontcare1, $dontcare2, &$flags )
	{
		// check if we are in the right namespace
		$ns = $article->mTitle->getNamespace();
		if ($ns != NS_INTERWIKI) return true;

		// Paranoia: this should have already been checked.
		// does the user have the right to edit pages in this namespace?
		if (! $article->mTitle->userCan(self::rEdit) ) return true;  

		// Are we dealing with the page which contains the links to manage?
		if ( $article->mTitle->getText() != self::mPage ) return true;

//		$this->updateIWL();
		
		return true; // continue hook-chain.
	}

	public function hEditFormPreloadText( &$text, &$title )
	// This hook is called to preload text upon initial page creation.
	// If we are in the NS_INTERWIKI namespace and no article is found ('initial creation')
	// then let's get the database entries.
	//
	// NOTE that the 'edit' permission is assumed to be checked prior to entering this hook.
	//
	{
		// Are we in the right namespace at all??
		$ns = $title->getNamespace();
		if ($ns != NS_INTERWIKI) return true; // continue hook chain.

		// Paranoia: Is the user allowed committing??
		// We shouldn't even get here if the 'edit' permission gets
		// verified adequately.
		if (! $title->userCan(self::rEdit) ) return true;		

		// start by reading the table from the database
		$this->getIWLtable();
		
		$text .= $this->getHeader();
		
#		var_dump( $this->iwl );
		
		foreach( $this->iwl as $index => &$el )
			$text .= $this->formatMagicWordLine( $el );
	
		$text .= $this->getFooter();
	
		return true; // be nice.
	}
	
	private function getIWLtable()
	// reads the 'interwiki' table into a local variable
	{
		$db =& wfGetDB(DB_SLAVE);
		$tbl = $db->tableName('interwiki');

		$result = $db->query("SELECT iw_prefix,iw_url,iw_local,iw_trans FROM  $tbl");
		
		while ( $row = mysql_fetch_array($result) ) 
			$this->iwl[] = array(	'prefix' => $row[0], 
									'uri'   => $row[1], 
									'local' => $row[2], 
									'trans' => $row[3] );		
	}
	
	private function getHeader() { return self::header; }
	private function getFooter() { return self::footer; }
	
	private function formatMagicWordLine( &$el )
	{
		return '
{{#'.self::$mgwords[0].':'.
	$el['prefix'].'|'.
	$el['uri']   .'|'.
	$el['local'] .'|'.
	$el['trans'] .'}}';
	}
	
	private function formatLine( &$el )
	{
		$text = '';
		$text .= self::sRow;
		
		$text .= self::sCol;	$text .= $el['prefix'];
		$text .= self::sCol;	$text .= $el['uri'];
		$text .= self::sCol;	$text .= $el['local'];
		$text .= self::sCol;	$text .= $el['trans'];

		return $text;				
	}
	private function updateIWL()
	{
		// The update process is fairly straightforward:
		// 1) delete all the entries
		// 2) insert the new list
	
			// Part 1
		$db =& wfGetDB(DB_MASTER);
		$tbl = $db->tableName('interwiki');	

		$db->query("DELETE * FROM $tbl");
		
			// Part 2
		/*
		foreach ( $this->new_iwl as $index => &$el )
			$db->query("INSERT INTO $tbl (iw_prefix,iw_url,iw_local,iw_trans) 
						VALUES('$el[]','$i[0]',$i[1],$i[2])");		
		*/
					
	}
	private function checkElement( &$prefix, &$uri, &$local, &$trans, &$errCode )
	{
		// no validation implemented at this moment.
		
		// everything is OK.
		return true;		
	}
	private function getErrMessage( $errCode )
	{
		// not much checking implemented at the moment...
		return '';	
	}
} // END CLASS DEFINITION
?>