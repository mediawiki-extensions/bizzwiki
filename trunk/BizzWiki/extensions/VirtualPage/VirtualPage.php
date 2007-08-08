<?php
/*<!--<wikitext>-->
{{Extension
|name        = VirtualPage
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/VirtualPage/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
== Purpose==
Provides regex based virtual page serving functionality based on a virtual directory.

== Theory of Operation ==
When a user requests a non-existing page (i.e. not in the database) from namespace X, 
this extension looks up the virtual directory page 'X:Virtual Directory', parses it
and performs a regular expression match to find a 'target template' for the
requested page. If a template can not be found, the transaction reverts to the 
standard MediaWiki one.

== Features ==
* No patches
* Fast - stub functionality for usual queries

== Usage ==
=== Basics ===
Create a page titled 'Virtual Directory' for each namespace where this extension should execute.

=== Format of the Virtual Directory ===
* One 'regex' expression per '\n' (i.e. newline) terminated line.
* Each line must contain 1 regex pattern + 1 link
** Regex pattern format: <nowiki><regex>/..regex expression here../</regex></nowiki>
** Link format: <nowiki>[[namespace:page]]</nowiki>
The 'link' corresponds to the page 'template' which will be served upon a successful regex match.

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Download the two files of this extension (VirtualPageSwitch.php & VirtualPage.php)
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/VirtualPage/VirtualPage.php');
</source>
* Make sure to have 'VirtualPage.php' and 'VirtualPageSwitch.php' in the same directory.

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>//--><source lang=php>*/

$wgExtensionCredits[VirtualPage::thisType][] = array( 
	'name'    		=> VirtualPage::thisName, 
	'version'		=> StubManager::getRevisionId( '$Id$' ),
	'author'		=> 'Jean-Lou Dupont', 
	'description'	=>  'Provides configurable per-namespace virtual pages',
	'url' 			=> StubManager::getFullUrl(__FILE__),			
);

class VirtualArticle extends Article
{
	var $virtualTitle;
	var $mDbkeyform;		// variable to undo THE ugly hack.
		
	/**
		The situation is the following:
		- $this contains the 'template page' content
		- $this->virtualTitle contains the virtual title (duh!)

		What we need to do here: display the template page
		with:
		- correct title
		- correct skin 
		- correct actions etc.
		as if the page was really the 'virtual' one.
	 */
	function view()
	{
		global $wgOut;
		global $wgNamespaceRobotPolicies;
		global $wgEnableParserCache;
		global $wgUser;
		
		// undo our little hack.
		$this->virtualTitle->mDbkeyform = $this->mDbkeyform;
		
//{{
		# Discourage indexing of printable versions, but encourage following
		if( $wgOut->isPrintable() ) {
			$policy = 'noindex,follow';
		} elseif( isset( $wgNamespaceRobotPolicies[$ns] ) ) {
			# Honour customised robot policies for this namespace
			$policy = $wgNamespaceRobotPolicies[$ns];
		} else {
			# Default to encourage indexing and following links
			$policy = 'index,follow';
		}
		$wgOut->setRobotPolicy( $policy );
//}}
		
		//case #1: the content is available in the parser cache.
		$parserCache =& ParserCache::singleton();
		
		# Should the parser cache be used?
		$pcache = $wgEnableParserCache &&
			intval( $wgUser->getOption( 'stubthreshold' ) ) == 0 &&
			$this->exists() &&
			empty( $oldid );

		$outputDone = false;
		wfRunHooks( 'ArticleViewHeader', array( &$this ) );
		if ( $pcache ) 
		{
			if ( $wgOut->tryParserCache( $this, $wgUser ) ) 
			{
				$outputDone = true;
			}
		}

		// case #2: the content is available in the database
		// We need to feed the correct 'title' object

		$this->mTitle = $this->templateTitle;
//{{
		if ( !$outputDone ) 
		{
			$text = $this->getContent();
			if ( $text === false ) {
				# Failed to load, replace text with error message
				$t = $this->mTitle->getPrefixedText();
				if( $oldid ) {
					$t .= ',oldid='.$oldid;
					$text = wfMsg( 'missingarticle', $t );
				} else {
					$text = wfMsg( 'noarticletext', $t );
				}
			}
		}
//}}
		// Put in place the necessary title corresponding
		// to the virtual page requested.
		$this->mTitle = $this->virtualTitle;
		global $wgTitle;
		$wgTitle = $this->mTitle;
		
		$wgOut->mTitle = $this->mTitle;
		
//{{	
		# Another whitelist check in case oldid is altering the title
		if ( !$this->mTitle->userCanRead() ) 
		{
			$wgOut->loginToUse();
			$wgOut->output();
			exit;
		}
//}}		
		# Display content and save to parser cache
		if ( $pcache )
			$this->outputWikiText( $text );
		else
			$this->outputWikiText( $text, false );

		$wgOut->setPageTitle( $this->mTitle->getPrefixedText() );		
			
		
		$this->viewUpdates();			
	} // view method
	
}//end class VirtualArticle

class VirtualPage
{
	const thisName = 'VirtualPage';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	
	const vdName   = 'VirtualDirectory';
	
	public function __construct() {	}
	
	/**
		This hook gets called when a title does not have
		a corresponding article in the database.
		
		The method retrieves the 'VirtualDirectory' page
		on the target namespace, parses it in order to
		derive which 'template' page should be served.
	 */
	public function hVirtualPage( &$title, &$article )
	{
		$ns = $title->getNamespace();
		
		$tvd= Title::makeTitle( $ns, self::vdName );
		$vd = new Article( $tvd );
		
		// the VirtualDirectory page might not exist;
		// in this case, just bail-out graciously
		if ($vd->getID() == 0 )
			return true;
		
		// from this point, we have a valid article
		// that serves as 'Virtual Directory'
		$vdPage = $vd->getContent();
		
		$target = $this->getTarget( $title, $vdPage );
		
		// no match? return silently
		if ($target === null)
			return true;
			
		// We have a match!  Let's try to load the
		// template article.
		$nsT  = $target['linkNs'];
		$page = $target['linkPage'];
		if (empty($nsT)) 
			$ns = NS_MAIN;
		else
			$ns	= Namespace::getCanonicalIndex( $nsT );
		
		$tmplTitle   = Title::makeTitle( $ns, $page );
		$tmplArticle = new VirtualArticle( $tmplTitle );
				
		// did we find satisfaction?
		if ( $tmplArticle->getID() == 0 )
			return true;
			
		// yes we did find a template article!
		$article = $tmplArticle;
		$article->virtualTitle   = $title;
		$article->templateTitle  = $tmplTitle;
		
		// prepare an undo for our hack below.
		$article->mDbkeyform     = $title->getDBkey();
		
		// ugly, ugly hack to get us pass 'wiki.php' hurdles		
		$title->mDbkeyform = $tmplTitle->getDBkey();
	
		return true;	
	}
	/**
		Parses the VirtualDirectory page in search
		of a match to the requested 'title'.
	 */
	private function getTarget( &$title, &$vdPage )
	{
		$m = $this->parse( $vdPage );	

		$pageTitle = $title->getText();
		
		$result = null;
		foreach( $m as &$e )
		{
			$regex = $e['regex'];
			$r = preg_match( $regex, $pageTitle );	
			if ( ( false === $r ) || (0 == $r) )
				continue;
			$result = array( 'linkNs' => $e['linkNs'], 'linkPage' => $e['linkPage'] );
		}
		
		return $result;
	}
	/**
		Parses the VirtualDirectory page
	 */
	private function parse( $vdPage )
	{
		// one entry per line maximum
		$e = explode( "\n", $vdPage );

		$m = array();		
		// some other stuff is allowed on each line;
		// just pick up what we need, namely the pair:
		// <regex> ... </regex>  and  [[NS:PAGE]]
		foreach( $e as &$line )
		{
			$mr = preg_match( "/<regex\>(.*)(?:\<.?regex)>/siU", $line, $matchRegex );
			
			// did we find a valid line?
			if ( (false === $mr) || (0 == $mr) )
				continue;
				
			// pick the regex expression
			$regex = $matchRegex[1];
			
			// now onto the page link
			$ml = preg_match( "/\[\[(.*):(.*)\]\]/siU", $line, $matchLink );			

			// did we find a valid link?
			if ( (false === $ml) || (0 == $ml) )
				continue;
			
			$linkNs = $matchLink[1];
			$linkP = $matchLink[2];			
			
			// at this point, we have a valid regex+link pair; get it.
			$m[] = array( 'regex' => $regex, 'linkNs' => $linkNs, 'linkPage' => $linkP );
			
		} // end foreach
		
		return $m;
	} // end parse method
	
} // end class

//</source>