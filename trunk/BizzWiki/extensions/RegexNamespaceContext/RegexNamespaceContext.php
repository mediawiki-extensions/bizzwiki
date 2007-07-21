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
			
	var $cpNsName;
	var $thisPageName;
	var $cpTitle;
	var $cpArticle;
	var $cpPo;		// parser output object
	
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
		
		return $this->page1;
	}
	/**
	 */
	protected function getHeaderFooterText( &$a, &$u, &$h, &$f )
	{
		$this->setupContext( $a );
		$this->processContext( self::headerfooter );
		
		$h = $this->page1;
		$f = $this->page2;
	}
	/**
	
	 */
	private function setupContext( &$obj )
	{
		$this->getPageParams( $obj, $ns, $pn );
		
		$this->cpNsName	= 		$ns; 
		$this->thisPageName =	$pn;
	}
	/**
	
	 */
	private function processContext( $contextType )
	{
		$this->loadContextPage();
		$this->parseContextPage();
		$this->findMatch();
	}
	
	/**
	 */
	private function loadContextPage()
	{
		// context page is located in the same namespace
		// under the defined base name
		$this->cpTitle = Title::makeTitle( $this->cpNsName, $this->thisPageName );
		$this->cpArticle = new Article( $this->cpTitle );

		if ($this->cpArticle->getID() == 0)
			$this->cp = null;
		else
			$this->cp = $this->cpArticle->getContent();
	}
	/**
		Parses the loaded context page.
		Magic Words registered with the MW Parser will do all the job.
	 */
	private function parseContextPage()
	{
		if (empty( $this->cp ))
			return;

		// the currently loaded parser contains all relevant information.
		global $wgParser;
		$this->cpPo = $wgParser->parse( $this->cp, $this->cpTitle, new ParserOptions );
		
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