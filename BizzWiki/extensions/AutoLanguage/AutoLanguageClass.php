<?php
/*<wikitext>
{| border=1
| <b>File</b> || AutoLanguageClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/

class AutoLanguageClass extends ExtensionClass
{
	// constants.
	const thisName = 'AutoLanguageClass';
	const thisType = 'other';
	  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function AutoLanguageClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => '$Id$',
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Page language automatic switching based on user preference'
		);
	}
	public function setup() 
	{ parent::setup();	}


} // end class
?>
