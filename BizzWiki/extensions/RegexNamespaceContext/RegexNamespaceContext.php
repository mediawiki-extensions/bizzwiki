<?php
/*<wikitext>
{{extension:
|RegexNamespaceContext.php
|$Id$
|Jean-Lou Dupont
}}
 
== Purpose==
Supports regex based 'edit form' text preloading and 'header'/'footer' wikitext pages insertion.

== Features ==
* Can load 'preload edit form' text based on a per-namespace regex 'context'
* Can add 'headers' and 'footers' text pages based on a per-namespace regex 'context'
* Creates a hook 'ContextPageParsingComplete'

== Usage ==
On a per-namespace basis (only the ones required), edit the page 'Context' and place+customize the following:
<pre>
== Preload Patterns ==
* Preload page upon '*.log' page name
* add other patterns below
{{#varaset:preloadPattern|{{NAMESPACE}}:preloadPage|(.*)\.log}}

{{#varaset:ContextVars|preloadPageName|
 {{#regx_vars:preloadPattern|{{#varaget:ContextVars|PageName}} }}
}}

== Header Patterns ==
* Header page for all pages in namespace following the '*.log' pattern
* add other patterns below
{{#varaset:headerPattern|{{NAMESPACE}}:PHPheaderPage|(.*)\.php}}

{{#varaset:ContextVars|headerPageName|
 {{#regx_vars:headerPattern|{{#varaget:ContextVars|PageName}} }}
}}

== Footer Patterns ==
* Footer page for all pages in namespace following the '*.log' pattern
* add other patterns below
{{#varaset:footerPattern|{{NAMESPACE}}:PHPfooterPage|(.*)\.php}}

{{#varaset:ContextVars|footerPageName|
 {{#regx_vars:footerPattern|{{#varaget:ContextVars|PageName}} }}
}}
</pre>
== Dependancies ==
* [[Extension:StubManager|StubManager extension]]
* [[Extension:RegexTools|RegexTools extension]]
* [[Extension:PageFunctions|PageFunctions extension]]
* [[Extension:ParserCacheControl|ParserCacheControl extension]]

== Installation ==
To install independantly from BizzWiki:
* Download [[Extension:ExtensionClass]] extension & put in 'extensions' directory
* Download [[Extension:ParserCacheControl]] extension & put in 'extensions' directory
* Download [[Extension:PageFunctions]] extension & put in 'extensions' directory
* Download [[Extension:RegexTools]] extension & put in 'extensions' directory
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/PageFunctions.php');
require('extensions/ParserCacheControl.php');
require('extensions/RegexTools.php');
require('extensions/RegexNamespaceContext/RegexNamespaceContext.php');
</source>

== History ==

== Code ==
</wikitext>*/
$wgExtensionCredits[RegexNamespaceContext::thisType][] = array( 
	'name'    => RegexNamespaceContext::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => "Supports regex based 'edit form' text preloading and 'header'/'footer' wikitext pages insertion.", 
);

class RegexNamespaceContext
{
	const thisType = 'other';
	const thisName = 'RegexNamespaceContext';

	const headerOpen	= '<!--header_open-->';
	const headerClose	= '<!--header_close-->';	
	const footerOpen	= '<!--footer_open-->';
	const footerClose	= '<!--footer_close-->';	

	static $cpBaseName = 'Context';
			
	var $cpNsId;
	var $thisPageName;
	var $cpTitle;
	var $cpArticle;
	
	//
	var $headerPageName;
	var $footerPageName;
	var $preloadPageName;
	
	public function __construct() { }
	/**
		Page preloading happens here.
	 */
	public function hEditFormPreloadText( &$textbox, &$title )
	{
		$textbox .= $this->getPreloadText( $title );	
		return true;
	}
	/**
		This hook makes sure that the parsing phase is completed for the page being
		_viewed__ *before* adding the 'header' and 'footer' pages.
		IMPORTANT: parser cache saving must be *disabled* upon editing/updating.
		Use [[Extension:ParserCacheControl]] if not operating under the BizzWiki platform.
	 */
	public function hParserAfterTidy( &$parser, &$text ) 
	{
		static $inProgress = false;
		if ($inProgress) return true;
		$inProgress = true;
		
		global $action;
		if ($action != 'view') return true;
		
		$title = $parser->mTitle;
	
		$this->getHeaderFooterText( $title, $header, $footer );
		$text = self::headerOpen.$header.self::headerClose.
				$text.
				self::footerOpen.$footer.self::footerClose;
		
		$inProgress = false;
		return true;
	}
	/**
	 */
	private function removeHeaderFooter( &$c )
	{
		$ph = '/'.self::headerOpen.'(.*)'.self::headerClose.'/';		
		$pf = '/'.self::footerOpen.'(.*)'.self::footerClose.'/';
		
		$c = preg_replace( $ph, '', $c );
		$c = preg_replace( $pf, '', $c );		
	}
	protected function getPreloadText( &$title )
	{
		$this->processContext( $title );

		return $this->getPageContent( $this->preloadPageName );
	}
	protected function getHeaderFooterText( &$a, &$h, &$f )
	{
		$this->processContext( $a );
		
		$h = $this->getPageContent( $this->headerPageName );
		$f = $this->getPageContent( $this->footerPageName );
	}
	private function processContext( &$obj )
	{
		$this->getPageParams( $obj, $ns, $pn );
		
		$this->cpNsId	= 		$ns; 
		$this->thisPageName =	$pn;

		$this->loadContextPage();
		$this->parseContextPage();
	}
	
	/**
		Load the {{NAMESPACE}}:Context  page
	 */
	private function loadContextPage()
	{
		// context page is located in the same namespace
		// under the defined base name
		$this->cp = $this->getPageContent( $this->cpNsId, self::$cpBaseName, $article, $title );
		
		$this->cpTitle = $title;
		$this->cpArticle = $article;
		
	}
	/**
			Just returns the page content.
			Accepts either:
			- a namespace id in $ns parameter OR
			- a string corresponding to a 'prefixed DBkey' serving as page title name.
	 */
	public function getPageContent( &$ns, &$pagename=null, &$article=null, &$title=null )
	{
		if (is_string( $ns ))
			$title = Title::newFromText( $ns );
		else
			$title = Title::makeTitle( $ns, $pagename );
		
		// paranoia.
		if (!is_object( $title ))
			return null;
		
		$article = new Article( $title );

		if ($article->getID() == 0)
			return null;

		return $article->getContent();
	}
	/**
		Parses the loaded context page.
		Magic Words registered with the MW Parser will do all the job;
		this is where the companion extension 'RegexTools' comes in handy.
	 */
	private function parseContextPage()
	{
		if (empty( $this->cp ))
			return;

		$params = array(
							'NamespaceId'	=> $this->cpNsId,
							'PageName'		=> $this->thisPageName,
						);

		// Pass some parameters in the 'Page' variables
		// Requires the 'PageFunctions' extension
		//
		// The parameter 'PageName' is especially important.
		
		wfRunHooks('PageVarSet', array( 'ContextVars', &$params) );

		// the currently loaded parser contains all relevant information.
		global $wgParser;
		$wgParser->parse( $this->cp, $this->cpTitle, new ParserOptions );
		
		// Grab the result from the 'Page' variables
		wfRunHooks('PageVarGet', array( 'ContextVars', &$oParams) );
		
		$this->headerPageName	= $oParams['headerPageName'];

		$this->footerPageName	= $oParams['footerPageName'];
		
		$this->preloadPageName	= $oParams['preloadPageName'];

		wfRunHooks('ContextPageParsingComplete', array( &$this, 'ContextVars' ) );		
	}	 
	/**
		Versatile function for getting information about a page from either:
		- a Title class object
		- an Article class object
		- or just a prefixed DBkey text string as page title name
	 */
	protected function getPageParams( &$obj, &$ns, &$pn )
	{
		if (!is_object( $obj ))
			return null;

		if (is_a( $obj, 'Article' ))
			$title = $obj->mTitle;
		
		if (is_a( $obj, 'Title' ))
			$title = $obj;

		if (!is_a( $title, 'Title'))
			return false;

		$ns = $title->getNamespace();
		$pn = $title->getDBkey();

		return true;			
	} 	
} // end declaration.
?>