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
	public function setup() 
	{ parent::setup( );	}
	
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
	public function MW_noclientcaching( &$parser, &$varcache, &$ret )
	{
		// The actual action of disabling the client caching process is already performed through
		// 'ParserCache2' extension when processing 'magic words' such as this one (($noclientcaching$)).
		// If on the contrary this function is called through the usual {{noclientcaching}} statement, then
		// 1) If 'parser caching' is used, this statement will have limited effect
		// 2) If 'parser caching' is not used, then this statement will have an effect everytime the page is visited.
		global $wgOut;
		$wgOut->enableClientCache(false);
	}
} // end class
?>