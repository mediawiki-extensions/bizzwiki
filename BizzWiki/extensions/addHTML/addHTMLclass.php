<?php
/*<wikitext>
{| border=1
| <b>File</b> || XYZ.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==


== Features ==


== Dependancy ==


== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<geshi lang=php>
require('extensions/ExtensionClass.php');
require('extensions/addHTML/addHTML.php');
</geshi>

== History ==

== Code ==
</wikitext>*/

class addHTMLclass extends ExtensionClass
{
	const tag = 'addhtml';
	
	static $mgwords = array ( 'addhtml' );
	
	var $hlist;

	public static function &singleton( ) // required by ExtensionClass
	{ return parent::singleton( ); }
	
	function addHTMLclass()
	{ parent::__construct( self::$mgwords ); } // required by ExtensionClass
	
	public function setup()
	{
		parent::setup();
		
		global $wgExtensionCredits;
		
		$wgExtensionCredits['other'][] = array( 
			'name'    => 'addHTML Extension', 
			'version' => '$Id$',
			'author'  => 'Jean-Lou Dupont', 
		);
		
		global $wgParser;
		$wgParser->setHook( self::tag, array( $this, 'hAddHtmlTag' ) );
	}
	public function mg_addhtml( &$parser, &$text )
	{
		return $this->process( $text, null /* TODO ? */ );
	}
	public function hAddHtmlTag( $input, $argv, &$parser )
	{
		// check page protection status
		if (!$this->checkPageEditRestriction( $parser->mTitle ))
			return "<b>addHtml</b>: ".wfMsg('badaccess');
		
		if ( isset($argv['id']) ) 
			$id = $argv['id'];
		else $id = null;
		
		return $this->process( $input, $id );
	}
	private function process( &$input, $id )
	{
		$input = trim( $input );
		if ( !empty( $input ) )
			if ( $id !== null )
				$this->hlist[ $id ] = $input; 
			else
				$this->hlist[] = $input; 
		
		$id = count( $this->hlist )-1;
		
		// let's put an easy marker that we can 'safely' find once we need to render the HTML
		$marker = "<".self::tag." id={$id} />";
		return $marker;
	}
	public function hParserAfterTidy( &$parser, &$text )
	{
		parent::hParserAfterTidy( $parser, $text );
		
		// Some substitution to do?
		if (empty($this->hlist)) return true;

		foreach($this->hlist as $index => $html)
		{
			$marker = "<".self::tag." id={$index} />";
			$text = str_ireplace($marker, $html, $text);
		}
		return true; // continue hook chain.
	}
/* -----------------------------------------------------------------------
    PUBLIC INTERFACE FOR OTHER EXTENSIONS WISHING TO ADD ARBITRARY HTML
   ----------------------------------------------------------------------- */
	public function addHtml( $id, $html ) {	$this->hlist[ $id ] = $html; }
	
} // END CLASS DEFINITION
?>