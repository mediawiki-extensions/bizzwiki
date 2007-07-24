<?php
/*<wikitext>
{{extension:
|MiscParserFunctions.php
|$Id$
|Jean-Lou Dupont
}}
 
== Purpose==
Provides miscellaneous parser functions (e.g. #trim, #nowikitext).

== Features ==
* <nowiki>{{#trim: input string}}</nowiki>
* <nowiki>{{#nowikitext: input string}}</nowiki>

== Dependancy ==
* [[Extension:ExtensionClass|ExtensionClass]]

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/MiscParserFunctions/MiscParserFunctions.php');
</source>

== History ==

== Code ==
</wikitext>*/

class MiscParserFunctions extends ExtensionClass
{
	// constants.
	const thisName = 'MiscParserFunctions';
	const thisType = 'other';
	const id       = '$Id$';		
	  
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
			'description' => 'Miscellaneous parser functionality',
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	public function setup() 
	{ parent::setup(); }
	
	/**
		Trims a string.
	 */
	public function mg_trim( &$parser, &$input )
	{ 
		return trim( $input );
	}
	/**
		Wraps a string in <nowiki> section.
	 */
	public function mg_nowikitext( &$parser, &$input )
	{
		return '<nowiki>'.htmlspecialchars( $input ).'</nowiki>';
	}
} // end class

if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: MiscParserFunctions extension will not work!';	
else
	MiscParserFunctions::singleton();
?>