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
	
	var $hlist;
	var $hookInPlace;

	public static function &singleton( ) // required by ExtensionClass
	{ return parent::singleton( ); }
	
	function addHTMLclass()
	{
		parent::__construct(); // required by ExtensionClass
		$this->hookInPlace = false;
	}
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
	public function hAddHtmlTag( $input, $argv, &$parser )
	{
		// check page protection status
		if (!$this->checkPageEditRestriction( $parser->mTitle ))
			return "<b>addHtml</b> extension: ".wfMsg('badaccess');
		
		$id = 0;
		if ( isset($argv['id']) )		
			$id = $argv['id'];
		
		$input = trim( $input );
		if ( !empty( $input ) )
			$this->hlist[ $id ] = $input; 
		
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