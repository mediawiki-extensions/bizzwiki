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
		parent::setup( );	
	}
	public function MW_NOCLIENTCACHING( &$parser, &$varcache, &$ret )
	{
		global $wgOut;
		$wgOut->enableClientCache(false);
	}
} // end class
?>