<?php
/*
 * ImageLinkClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 */
class ImageLinkClass extends ExtensionClass
{
	var $links;
	
	public static function &singleton( )
	{ return parent::singleton( ); }
	
	public function __construct() 
	{ parent::__construct( ); }
	
	public function setup()
	{ parent::setup(); }
	
	public function mg_imagelink( &$parser, $img, $page,  							// mandatory parameters  
								$alt=null, $width=null, $height=null, $border=null )// optional parameters
	/**
	 *  $img  = image reference i.e. a valid image name e.g. "New Clock.gif" 
	 *  $page = page reference i.e. a valid page name e.g. "Admin:Show Time"
	 *
	 * {{#imagelink:New Clock.gif|Admin:Show Time|alternate text}}
	 */
	{
		$image = Image::newFromName( $img );
		if (!$image->exists()) return;
		
		if (empty($page)) return;
			
		$title = Title::newFromText( $page );
		if (!is_object($title)) return;
		
		$iURL = $image->getURL();
		
		// distinguish between local and interwiki URI
		if ($title->isLocal())
		{
			$tURL = $title->getLocalUrl();
			$aClass=''; 			
		}
		else
		{
			$tURL = $title->getFullURL();
			$aClass = 'class="extiw"';
		}		
		// Optional parameters
		if ($alt    !== null)	$alt    = "alt='${alt}'"; 		else $alt='';
		if ($width  !== null)	$width  = "width='${width}'"; 	else $width='';
		if ($height !== null)	$height = "height='${height}'";	else $height='';
		if ($border !== null)	$border = "border='${border}'";	else $border='';

		$t = "_imagelink_".date('Ymd').count($this->links)."_/imagelink_";
				
		// let's put an easy marker that we can 'safely' find once we need to render the HTML
		$this->links[] = "<a ".$aClass." href='${tURL}'><img src='${iURL}' $alt $width $height $border /></a>";

		return $t;
	}

	/**
	 	This function is called just before the HTML is rendered to the client browser.
	 */
	public function hParserAfterTidy( &$parser, &$text )
	{
		// Some substitution to do?
		if (empty($this->links)) return true;

		foreach($this->links as $index => $link)
		{
			$p = "/_imagelink_".date('Ymd').$index."_\/imagelink_/si";
			$text = preg_replace( $p, $link, $text );
		}
	
		return true;
	}
} // end class definition.
?>