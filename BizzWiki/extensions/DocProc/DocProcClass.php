<?php
/*<wikitext>
{{extension:
|DocProcClass.php
|$Id$
|Jean-Lou Dupont
}}

== Code ==
</wikitext>*/

class DocProcClass extends ExtensionClass
{
	// constants.
	const thisName = 'DocProcClass';
	const thisType = 'other';
	const id       = '$Id$';	
	
	static $allowedDocTags = array( 'code', 'pre' );
	static $defaultDocTag = 'code';
	
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function __construct( )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => "Documents wikitext with 'markup/magic words' whilst still processing as per normal.",
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	public function setup() 
	{ parent::setup();	}

	public function tag_docproc( &$text, &$params, &$parser )
	{
		// make sure the user is asking for a valid HTML tag for the documentation part.
		$docTag = (in_array($params[0], self::$allowedDocTags)) ? ($params[0]) : (self::$defaultDocTag);		
		
		// parse the wikitext as per required as if the said text wasn't being automatically documented.
		$pt = $parser->recursiveTagParse( $text, null, $parser );
		
		return '<'.$docTag.'>'.htmlspecialchars($text).'</'.$docTag.'>'.$pt;
	}
} // end class

?>