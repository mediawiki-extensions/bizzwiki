<?php
/*
 * RunPHP Class
 * Author: Jean-Lou Dupont
 * $Id$
 *
 * Purpose:  serves as general purpose PHP code extraction 
 *           based on source code stored in MediaWiki database page.
 *
 * Features:  - Composite Page support (wikitext + php code section)
 *            - 1 php section per page
 *            - Integration with RunPHP extension
 *           
 * Installation: To activate the extension, include it from your 
 *               "LocalSettings.php" file with: 
 *               include("extensions/Runphp_class.php");
 *
 * History:  This extension is based on the "Runphp page" extension.
 *
 *          v1.0  initial availability
 *          v1.1  Added Javascript support
 *          v1.2  Added support for strict explicit <PHP> section
 *                code return i.e. don't return raw page when
 *                asked explicitly for code in PHP enclosed section.
 *
 *          v1.3  Added security feature: only execute code on 'edit' 
 *                protected page accessible by 'sysop' group members.
 
 --------------		MOVED to BizzWiki
				- Added 'initFromContent' method
 
 *
*/
$wgExtensionCredits['class'][] = array( 
	'version'=> '$Id$',
    'name'   => "RunPHP Class",
	'author' => 'Jean-Lou Dupont' 
);

// Page Types.
define( 'RUNPHPCLASS_NULL' ,      null );
define( 'RUNPHPCLASS_PHPONLY',    1 );
define( 'RUNPHPCLASS_PHPSECTION', 2 );
define( 'RUNPHPCLASS_JSSECTION',  4 );
define( 'RUNPHPCLASS_COMPOSITE',  8 );

class runphpClass
{
	private static $composite_tag        = 'composite';

	var $raw;        #page's raw content, composite page case.
	var $content;    #page's Wikitext content (latest revision)
	var $phpsection; #page's PHP code enclosed in <php> section
	var $phpsectionfound;
	var $jssection;  #page's JS code enclosed in <javascript> section
	var $title;      #name identifier, NOT Title Class
    var $type;       #page type 
	var $protectState;  #v1.3: page's 'edit' protection state. 

	function getType()	     { return $this->type;	     }
	function getContent()    { return $this->content;    }
	function getRawContent() { return $this->raw;        } 
	function getJsCode()     { return $this->jssection;  }
	function getCode( $strict = false )       
	{ 
		if ($strict && (!$this->phpsectionfound))
			return null;
		return $this->phpsection; 
	}	
	 
	/*
	 * NOTE: title here refers to the mw name
	 *       of the article and NOT a Title Class.
	*/
	public function init( $title )
	{
 		$this->title      = $title;
		$this->type       = RUNPHPCLASS_NULL;
		$this->content    = null;
		$this->phpsection = null;
		$this->phpsectionfound = false;
		$this->jssection    = null;
		$this->protectState = null;
	
		$this->load();
	}

	/*
	 * This function loads the page content in the object.
	 * PRIVATE FUNCTION.
	*/
	private function load()
	{
		global $mediaWiki;
		
		$title = Title::newFromText( $this->title );
		  
		// Can't load page if title is invalid.
		if ($title == null)
			return;
		
		$article = $mediaWiki->articleFromTitle($title);
		
		// Also, if article can't be found, bail out.
		if ($article == null )
			return;
		
		// Let's try fetching the page content.
		$article->loadContent();
		
		// V1.3 security feature.
		// At this point, the page's restriction
		// is loaded. Get 'edit' protection state.
		$state = $title->getRestrictions('edit');
        $this->protectState = false;
        $state = $title->getRestrictions('edit');
        foreach ($state as $index => $group )
          if ( $group == 'sysop' )
            $this->protectState = true;
		
		if (! $this->protectState)
			return; 
		
		# if no page or an empty one
		if (!$article->mDataLoaded)
			return;
		 
		$this->content = $article->mContent;
		$this->raw     = $article->mContent;
		
		// Now, let's analyse the page
		// to determine things like page type.
		$this->analyse();
	}

	public function initFromContent( &$content )
	{
		$this->content = $content;
		$this->raw     = $content;
		
		// Now, let's analyse the page
		// to determine things like page type.
		$this->analyse();
	}

	/*
	 * This function should only be called
	 * once the page content is loaded in the object.
	 * PRIVATE FUNCTION.
	*/
	private function analyse()
	{
		// If we end up with an empty page, bail out.
		// The type is already set to NULL by default.
		if ( empty($this->raw) || ($this->raw==null) )
			return;
			
		// If we got here, then it means there is
		// at least something on the page
		// The Default page type: "PHP only"
		$this->type = RUNPHPCLASS_PHPONLY;

		// Assume for now the code is the whole page
		// without any <php> tags for wrapping it.
		$this->phpsection = $this->raw;

		// Now, let's look if we can find some more
		// information affecting our verdict.
		// If we find a <php> code </php> section,
		// then it cannot be a PHP only page
		// BUT it can also be a COMPOSITE type...
		if ( $this->extractPHPsection() )
		{
			$this->type = RUNPHPCLASS_PHPSECTION;
			$this->phpsectionfound = true;
		}
			
		// Test for page type "Composite" i.e. with Wikitext
		// ==============================
		if ($this->extractCompositeTag())
			$this->type |= RUNPHPCLASS_COMPOSITE;
	
		// Try and extract any JS lying around.
		if ($this->extractJSsection())
			$this->type |= RUNPHPCLASS_JSSECTION;		
	}

	private function extractPHPsection()
	{
		# Integration with GeSHi
	 	# The PHP page can be highlighted with GeSHi whilst
	 	# still being executable.
		# We skip over any attributes enclosed in the PHP tag
	 	$r = preg_match( "/<php(?:.*)\>(.*)(?:\<.?php>)/siU", $this->raw,  $c );
	  
	 	// Only one code block is allowed per page.
	 	if ($r==1)
		{
	    	$this->phpsection = $c[1];  # section is potentially GeSHi highlighted.
			return true;
		} 
		
		return false;
	}
	
	private function extractJSsection()
	{
		# Integration with GeSHi
	 	# The PHP page can be highlighted with GeSHi whilst
	 	# still being executable.
		# We skip over any attributes enclosed in the PHP tag
	 	$r = preg_match( "/<javascript(?:.*)\>(.*)(?:\<.?javascript>)/siU", $this->raw,  $c );
	  
	 	// Only one code block is allowed per page.
	 	if ($r==1)
		{
	    	$this->jssection = $c[1];  # section is potentially GeSHi highlighted.
			return true;
		}
		return false;
	}
	
	private function extractCompositeTag()
	{
		// Look for the meta tag semantically intepreted
		// in this class as meaning "composite page"
		$pattern = "/<".self::$composite_tag."*.\>/";
		$result = ( (preg_match($pattern, $this->raw) == 1) ? true: false);
		
		// Now that we know we are dealing with a composite page,
		// we have 2 options on are hands:
		// a) page with <composite [options]>wikitext</composite>
		// b) page with <composite/>  i.e. rest of the page is wikitext

		if ($result == true)
		{
			$pattern = "/<".self::$composite_tag."(?:.*)\>(.*)(?:\<.?".self::$composite_tag.">)/siU";
			preg_match($pattern,$this->raw,$m);
			$this->content = $m[1];
		}
		
		return $result;
	}

	/*
	 * Convert the page's content <php> tags to <runphp> ones
	 * so that the MW parser can go through the page content
	 * and execute the <runphp> sections using the "RunPHP" extension.
	 *
	 * NOTE: this should only be called when dealing with composite pages.
	*/
	public function convert()
	{
		$pattern1 = "/\/php>/siU";
		$replace1 = "/runphp>";
		$pattern2 = "/\<php/siU";
		$replace2 = "<runphp";
	
		$result1       = preg_replace($pattern1, $replace1, $this->content);
		$this->content = preg_replace($pattern2, $replace2, $result1);
	}
	public function removeCode()
	{
		// Get rid of the code section in the base content.
		// This is useful for saving space.
		// This function is useful when dealing with "composite pages" i.e.
		// the processor would:
		// -- fetch the code from the content,
		// -- load the code in the PHP machine using "eval"
		// -- strip the content from the code
		// -- invoke the callback with, as parameters, the source page & 
		//    this page's content 
		$content = preg_replace("/\<php(.*)php\>/siU","", $this->content);
		$this->content = $content;
	}	
	
} // end class definition.
?>