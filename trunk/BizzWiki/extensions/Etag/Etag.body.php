<?php
/*
<!--<wikitext>-->
 <file>
  <name>Etag.body.php</name>
  <version>$Id$</version>
  <package>Extension.Etag</package>
 </file>
<!--</wikitext>-->
*/
// <source lang=php>

class Etag
{
	const thisType = 'tag';
	const thisName = 'Etag';
	
	public function __construct() { }

	public function tag_etag( &$text, &$params, &$parser )
	{
		// prepare the template text
		$s = '{{etag|{{NAMESPACE}}|{{PAGENAME}}|'.$text.'}}';
				
		// parse it in the same context as the current page.
		$pt = $parser->recursiveTagParse( $s );
		
		return $pt;
	}
	
} // end class

//</source>
