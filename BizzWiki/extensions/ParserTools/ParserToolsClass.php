<?php
/*<wikitext>
{| border=1
| <b>File</b> || ParserToolsClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
*/

class ParserToolsClass extends ExtensionClass
{
	// constants.
	const thisName = 'ParserToolsClass';
	const thisType = 'other';
	const id       = '$Id$';		
	  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function ParserToolsClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Parser cache enabling/disabling through <noparsercaching/> tag',
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	public function setup() 
	{ 
		parent::setup();	

		// <noparsercaching/>
		global $wgParser;
		$wgParser->setHook( 'noparsercaching', array( &$this, 'noParserCaching' ) );
	}
	public function noParserCaching( &$text, &$params, &$parser )
	{ $parser->disableCache(); }

} // end class
?>