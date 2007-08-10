<?php
/*<!--<wikitext>-->
{{Extension
|name        = ShowRedirectPageText
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ShowRedirectPageText/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose ==
This extension enables the display of the text included in a 'redirect' page.
The inclusion of wikitext in a redirect page is helpful in situations, for example, where redirects are used to manage a  'cluster' of Mediawiki serving machines.

== FEATURES ==
* No mediawiki installation source level changes
* No impact on parser caching

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/ShowRedirectPageText/ShowRedirectPageText_stub.php');
</source>

== HISTORY ==
* Moved singleton invocation to end of file to accomodate some PHP versions
* Removed dependency on ExtensionClass
* Added 'stubbing' through StubManager

== TODO ==
* Clean up the '#redirect' wikitext before displaying

<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[ShowRedirectPageText::thisType][] = array( 
	'name'        => ShowRedirectPageText::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Provides viewing a wikitext included in a redirect page',
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);

class ShowRedirectPageText
{
	const defaultAction = true;   // by default, show the text
	
	const thisName = 'ShowRedirectPageText';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	
	var $found;
	var $actionState;

	public function __construct() 
	{
		$this->found = null;
		$this->actionState = self::defaultAction;
	}

	public function setActionState( $s ) { $this->actionState = $s ;}

	public function hArticleViewHeader( &$article )
	{
		// check if we are dealing with a redirect page.
		$this->found = Title::newFromRedirect( $article->getContent() );
		
		return true;		
	}
	public function hOutputPageParserOutput( &$op, $parserOutput )
	{
		// are we dealing with a redirect page?
		if ( ( !is_object($this->found) ) || ( !$this->actionState ) )return true;
	
		// take care of re-entrancy
		if ( !is_object($this->found) ) return true;
		$this->found = null;
		
		$op->addParserOutput( $parserOutput );
		return true;	
	}
	
} // end class definition.
//</source>