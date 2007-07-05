<?php
/*<wikitext>
{| border=1
| <b>File</b> || CacheToolsClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
*/

class CacheToolsClass extends ExtensionClass
{
	// constants.
	const thisName = 'CacheToolsClass';
	const thisType = 'other';
	const id       = '$Id$';		
	 
	// magic words defined here
	static $mwl   = array( 'NOCLIENTCACHING' );
	static $mwlid = array( 'MWORD_NOCLIENTCACHING' );
	  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function CacheToolsClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Client side cache enabling/disabling through <nowiki>{{NOCLIENTCACHING}}</nowiki> magic word',
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	public function setup() 
	{ 
		parent::setup();	
	}

	public function hMagicWordMagicWords( &$mw )
	{
		foreach ( self::$mwl as $index => $e )
			$mw[] = "MWORD_$e";
		return true;
	} 

	public function hMagicWordwgVariableIDs( &$mw )
	{
		foreach ( self::$mwl as $index => $e )
			$mw[] = constant( "MWORD_$e" );
		return true; // be nice.
	}

	public function hParserGetVariableValueSwitch( &$parser, &$varCache, &$varid, &$ret )
	{
		if ( in_array( $varid, self::$mwlid) )
			$this->$varid( $parser, $varCache, $ret );	

		return true;
	}

	public function hLanguageGetMagic( &$langMagic, $langCode = 0 )
	{
		foreach ( self::$mwl as $index => $e )
		{
			$magic = "MWORD_$e";
			$langMagic[defined($magic) ? constant($magic) : $magic] = array(0,$e);			
		}
		return true; // be nice.
	}

	public function MWORD_NOCLIENTCACHING( &$parser, &$varcache, &$ret )
	{
		echo 'HERE !!!!!!!!!!!!!';
		global $wgOut;
		$wgOut->enableClientCache(false);
	}
} // end class
?>