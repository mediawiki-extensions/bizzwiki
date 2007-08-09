<?php
/*<!--<wikitext>-->
{{Extension
|name        = XYZ
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/XYZ/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose==
Provides a magic word to automatically create a redirect page to the current page.

== Features ==

== Usage ==
<code>{{#autoredirect:namespace|page name}}</code> creates a the specified page as a redirect
to the current page i.e. the one containing the magic word.

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/AutoRedirect/AutoRedirect.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[AutoRedirect::thisType][] = array( 
	'name'    => AutoRedirect::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => "Provides a magic word to automatically create redirect pages", 
);

class AutoRedirect
{
	const thisType = 'other';
	const thisName = 'AutoRedirect';
	
	static $msg;
	
	public __construct() 
	{
		global $wgMessageCache;
		foreach( self::$msg as $key => $value )
			$wgMessageCache->addMessages( self::$msg[$key], $key );		
	}
	
	public function mg_autoredirect( &$parser, &$ns, &$page, &$alternateText = null )
	{
		// if ns contains a numeric
		if (is_numeric( $ns ))
		{
			if (empty(Namespace::getCanonicalName( $ns )))
				return wfMsg('AutoRedirect-invalid-namespace');
		}		
		else
		{
			if ( ($n = Namespace::getCanonicalIndex( $ns )) === null)	
				return wfMsg('AutoRedirect-invalid-namespace');				
			$ns = $n;
		}
	
		// if the source page already exists, bail out silently.
		$title   = Title::makeTitle( $ns, $page );
		$article = new Article( $title );
		if ( $article->getID() !=0 )
			return null;
			
		// the source page where the redirect should be created
		// does not exist currently. Great.
		$link = $this->createRedirectPage( $parser, $article, $alternateText );	
		
		return $link;
	}
	
	private function createRedirectPage( &$parser, &$article, &$alternateText )
	{
		$ns   = $parser->mTitle->getNamespace();
		$page = $parser->mTitle->getText();
		
		$pageText = wfMsgForContent( 'AutoRedirect-page-text', $ns, $page );
		$summary  = wfMsgForContent( 'AutoRedirect-summary-text', $ns, $page );
		$article->insertNewArticle( $pageText, $summary, false, false );
	
		if (!empty( $alternateText ))
			return wfMsgForContent('AutoRedirect-link-text', $ns, $page, $alternateText)
			
		return null;
	}
	
} // end class
require('AutoRedirect.i18n.php');
//</source>
