<?php
/*
<!--<wikitext>-->
 <file>
  <name>TagToTemplate.body.php</name>
  <version>$Id$</version>
  <package>Extension.TagToTemplate</package>
 </file>
<!--</wikitext>-->
*/
// <source lang=php>

class TagToTemplate
{
	const thisType = 'tag';
	const thisName = 'Etag';
	
	// 
	static $tablePageName = 'MediaWiki:Registry/TagToTemplate';
	static $open_pattern =  '/\<$tag(.*)\>/siU';
	static $close_pattern = '</$tag>';
	static $open_replace = '{{$tag|$params|';
	static $close_replace = '}}';
	var $loaded;

	var $map;
	
	public function __construct() 
	{ 
		$this->loaded = false;
		$this->map = array();
	}
	/**
		Helper function that helps us populate the 'map' table.
		This parser function should be used in the 'Table' page
		referenced through self::$tablePageName
	 */
	public function mg_tag_to_template( &$parser, $tag, $template )
	{
		$this->map[ $tag ] = $template;
	}
	/**
		Do the substitute before MediaWiki's parser as a chance
		to parse the actual text.
	 */
	public function hParserBeforeStrip( &$parser, &$text, &$strip_state )
	{
		if (!$this->loaded)
			$this->loadTable();
	
		$this->substitute( $text );
		
		return true;		
	}
	/**
	 */
	private function loadTable()
	{
		$this->loaded = true;
		
		$title = Title::newFromText( self::$tablePageName );
		$tablePageRev = Revision::newFromTitle( $title );
		
		if (!is_object( $tablePageRev ))
			return;
			
		$tablePage = $tablePageRev->getText();
		
		// use the global parser to parse the page in question.
		global $wgParser;
		$parser = clone $wgParser;
		
		// this will populate the 'map' variable
		// assuming of course that the page was edited with
		// {{#tag_to_template| ... }} instructions.
		$parser->recursiveTagParse( $tablePage );
	}
	private function substitute( &$text )
	{
		if (empty( $this->map ) || empty( $text ) )	
			return;

		foreach( $this->map as $tag => $template )
		{
			$this->replaceOpen( $tag, $template, $text );	
			$this->replaceClose( $tag, $text );
		}
	}
	/**
		Replaces all the 'open' tags e.g. < taghere paramshere >
		The parameters are passed as {{{1}}} variable in the resulting template.
	 */
	private function replaceOpen( &$tag, &$template, &$text )	
	{
		$p = str_replace('$tag', $tag, self::$open_pattern );
		
		$r = preg_match_all( $p, $text, $m );
		// make sure we have some entries.
		if ( ($r===0) || ($r===false))
			return;

		// base open replace pattern
		$orb = str_replace('$tag', $template, self::$open_replace );
		
		foreach( $m[0] as $index => $full_match )
		{
			// prepare the parameters substitution.
			$params = $m[1][$index];
			$or = str_replace( '$params', $params, $orb );
			
			// do the actual full substitution
			$text = str_replace( $full_match, $or, $text);
		}
	}
	/**
		Replaces all the 'close' tags e.g. < /taghere >
	 */
	private function replaceClose( &$tag, &$text )
	{
		$p = str_replace( '$tag', $tag, self::$close_pattern );
		$text = str_replace( $p, self::$close_replace, $text );
	}
} // end class

//</source>