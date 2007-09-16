<?php
/*<wikitext>
{| border=1
| <b>File</b> || SpecialPagesManagerClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/

$wgExtensionCredits[SpecialPagesManagerClass::thisType][] = array( 
	'name'        		=> SpecialPagesManagerClass::thisName, 
	'version'     		=> StubManager::getRevisionId( '$Id$' ),
	'author'      		=> 'Jean-Lou Dupont', 
	'description' 		=> 'Provides management functions for BizzWiki Special Pages',
	'url' 				=> 'http://mediawiki.org/wiki/Extension:SpecialPagesManager',			
);

class SpecialPagesManagerClass
{
	// constants.
	const thisName = 'SpecialPagesManagerClass';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	
	// defines.
	var $spPage;

	public function __construct()
	{
		// Base page for the enhanced Special Pages
		$this->spPage = Namespace::getCanonicalName(NS_BIZZWIKI).':Special Pages';
	}
	
	// Use this method to change the enhanced special page's title.
	public function setSpecialPage( $sp ) { $this->spPage = $sp; }
	
	public function hSpecialPageExecuteAfterPage( &$sp, &$par, &$func )
	// handlers executed after special page's 'execute' method is executed.
	{
		// bail out if we are not dealing with the right page.
	    if (($sp->getName()) != 'Specialpages') return true;

		// Default behavior for standard MW special pages.
		$this->layoutPages( $sp );

		// Verify if the NS_BIZZWIKI namespace is defined
		if ( !defined('NS_BIZZWIKI') ) return true; // nothing more todo 
													// if we are not in the BizzWiki environment
		// Get the page.
		$title   = Title::newFromText( $this->spPage );
		
		// does the article exists?
		if ( $title->getArticleID() == 0 ) return true;
		
		// Is the enhanced special page protected?
		// We don't want just anybody editing this page.
		if ( !$title->isProtected('edit') ) return true;
		
		// get the article content
		$article = new Article( $title );
		$content = $article->getContent();
		
		// Add it.
		global $wgOut;
		$wgOut->addWikiTextTitle( $content, $title, true );
		
		return true; // be nice.
	}
		
	private function layoutPages()
	{
		# TODO	
	}		
		
} // end class declaration
