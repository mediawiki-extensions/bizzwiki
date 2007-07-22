<?php
/*<wikitext>
{{extension:
|RegexNamespaceContext.php
|$Id$
|Jean-Lou Dupont
}}
 
== Purpose==


== Features ==


== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/RegexNamespaceContext/RegexNamespaceContext.php');
</source>

== History ==

== Code ==
</wikitext>*/
$wgExtensionCredits[RegexNamespaceContext::thisType][] = array( 
	'name'    => RegexNamespaceContext::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => '', 
);

class RegexNamespaceContext
{
	const thisType = 'other';
	const thisName = 'RegexNamespaceContext';

	const cpBaseName = 'Context';
	const preload = 0;
	const headerfooter = 1;
			
	var $cpNsId;
	var $thisPageName;
	var $cpTitle;
	var $cpArticle;
	
	//
	var $headerPageNs;
	var $headerPageName;
	var $footerPageNs;
	var $footerPageName;
	var $preloadPageNs;
	var $preloadPageName;
	
	public function __construct()
	{

	}
	/**
	
	 */
	public function hEditFormPreloadText( &$textbox, &$title )
	{
		$textbox = $this->getPreloadText( $title );	
		return true;
	}
	/**
	 */
	public function hArticleSave( &$article, &$user, $text, $summary, $minor, $dontcare1, $dontcare2, &$flags ) 
	{
		$this->getHeaderFooterText( $article, $user, $header, $footer );
		$text = $header.$text.$footer;
		return true;
	}	
	/**
	 */
	protected function getPreloadText( &$title )
	{
		$this->setupContext( $title );
		$this->processContext( self::preload );
		
		return $this->getPageContent( $this->preloadPageNs, $this->preloadPageName );
	}
	/**
	 */
	protected function getHeaderFooterText( &$a, &$u, &$h, &$f )
	{
		$this->setupContext( $a );
		$this->processContext( self::headerfooter );
		
		$h = $this->getPageContent( $this->headerPageNs, $this->headerPageName );
		$f = $this->getPageContent( $this->footerPageNs, $this->footerPageName );
	}
	/**
	
	 */
	private function setupContext( &$obj )
	{
		$this->getPageParams( $obj, $ns, $pn );
		
		$this->cpNsId	= 		$ns; 
		$this->thisPageName =	$pn;
	}
	/**
	
	 */
	private function processContext( $contextType )
	{
		$this->loadContextPage();
		$this->parseContextPage();
	}
	
	/**
	 */
	private function loadContextPage()
	{
		// context page is located in the same namespace
		// under the defined base name
		$this->cp = $this->getPageContent( $this->cpNsId, $this->thisPageName, $article, $title );
		
		$this->cpTitle = $title;
		$this->cpArticle = $article;
	}
	/**
	
	 */
	public function getPageContent( &$ns, &$pagename, &$article=null, &$title=null )
	{
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
		Magic Words registered with the MW Parser will do all the job.
		We need to pass some parameters:
		
	 */
	private function parseContextPage()
	{
		if (empty( $this->cp ))
			return;

		$params = array(
							'Namespace'	=> $this->cpNsId,
							'PageName'	=> $this->thisPageName,
						);

		// Pass the required parameters in the 'Page' variables
		// Requires the 'PageFunctions' extension
		wfRunHooks('PageVarSet', 'ContextVars', $params );

		// the currently loaded parser contains all relevant information.
		global $wgParser;
		$wgParser->parse( $this->cp, $this->cpTitle, new ParserOptions );
		
		// Grab the result from the 'Page' variables
		wfRunHooks('PageVarGet', 'ContextVars', $oParams );
		

		$this->headerPageName	= $oParams['headerPageName'];
		$this->headerPageNs		= $oParams['headerPageNs'];		

		$this->footerPageName	= $oParams['footerPageName'];
		$this->footerPageNs		= $oParams['footerPageNs'];		
		
		$this->preloadPageName	= $oParams['preloadPageName'];
		$this->preloadPageNs	= $oParams['preloadPageNs'];		
	}	 
	/**
	 */
	protected function getPageParams( &$obj, &$ns, &$pn )
	{
		if (!is_object( $obj ))
			return null;

		if (is_a( $obj, 'Article' ))
			$title = $obj->mTitle;
		
		if (is_a( $obj, 'Title' ))
			$title = $obj;
		else
			return false;

		$ns = $title->getNamespace();
		$pn = $title->getDBkey();

		return true;			
	} 	
} // end declaration.

?>