<?php
/*<!--<wikitext>-->
{{Extension
|name        = SecureHTML
|status      = stable
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/SecureHTML/ SVN]
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
== Purpose==
This extension enables the usage of 'html' tags (functionality which is controlled through the '$wgRawHtml' global variable) within protected pages.
The extension also offers the functionality to add content securily to the document's head section.

== Features ==
* Cascading: if the base page is allowed to use 'html' tags, then all included pages will be processed
as if they could.
* Namespace exemption: configured namespaces are exempted from 'protection' requirement
* Parser cache friendliness: content inserted in the script's head persists in the parser cache
** The extension must be enabled to continue the support of the inserted content

== Usage ==
* Use the standard <nowiki><html></nowiki> tags (see [[Manual:$wgRawHtml]]) within a protected page. One can either protect the page before or after the inclusion of the said tag(s).
* Use <code><addtohead> some html code to insert in the document's head </addtohead></code>

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/SecureHTML/SecureHTML_stub.php');
</source>

== History ==
* added namespace exemption functionality i.e. namespaces where article do not need to be
protected in order to use 'html' tags
** use <code>SecureHTMLclass::enableExemptNamespaces = false; </code> to turn off
** use <code>SecureHTMLclass::exemptNamespaces[] = NS_XYZ; </code> to add namespaces
* enhanced with functionality to 'add' content to the document's 'head' section
* Removed dependency on ExtensionClass
* Enabled for 'StubManager'
* Added 'addExemptNamespaces' function

== Todo ==
* Fix for allowing more customization of 'exempt' namespaces even when using StubManager

== See also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[SecureHTML::thisType][] = array( 
	'name'        => SecureHTML::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Enables secure HTML code on protected pages',
	'url' 		=> 'http://mediawiki.org/wiki/Extension:SecureHTML',			
);

class SecureHTML
{
	// constants.
	const thisName = 'SecureHTML';
	const thisType = 'other';
	  
	static $enableExemptNamespaces = true;
	static $exemptNamespaces = array();

	public static function addExemptNamespaces( $list )
	{
		if (!is_array( $list ))	
			$list = array( $list );
			
		self::$exemptNamespaces = array_merge( self::$exemptNamespaces, $list );
	}

	function __construct( )
	{
		// default exempt namespaces from the BizzWiki platform.
		// won't affect installs of the extension outside the BizzWiki platform.
		if (defined('NS_BIZZWIKI'))   self::$exemptNamespaces[] = NS_BIZZWIKI;
		if (defined('NS_FILESYSTEM')) self::$exemptNamespaces[] = NS_FILESYSTEM;
	}
	/**
		This hook is required for adapting to 'parser cache' article saving
	 */
	public function hArticleSave( &$article, &$user, &$text, &$summary, $minor, $dontcare1, $dontcare2, &$flags )
	{ return $this->process( $article ); }
	/**
		 This hook is required when 'parser caching' functionality is not used.	
	 */
	public function hArticleViewHeader( &$article )
	{ return $this->process( $article ); }

	/**
		Attempt article processing with 'raw html tags'.
	 */	
	private function process( &$article )
	{
		if (!$this->canProcess( $article ) ) return true;
				
		// Now that we know we are on a protected page,
		// enable raw html for the benefit of the 'parser cache' saving process
		global $wgRawHtml;
		$wgRawHtml = true;
		
		return true; // continue hook-chain.
	}
	/**
		Verify's article protection status.
	 */
	private function canProcess( &$obj )
	{
		if (!is_object( $obj ))
			return false; // paranoia
			
		if (is_a( $obj, 'Article'))
			$title = $obj->mTitle;
		else
			return false;
		
		if (self::$enableExemptNamespaces)
		{
			$ns = $title->getNamespace();
			if ( !empty(self::$exemptNamespaces) )
				if ( in_array( $ns, self::$exemptNamespaces) )
					return true;	
		}
		
		// check protection status
		if ( $title->isProtected( 'edit' ) ) return true;
		
		return false;
	}

} // END CLASS DEFINITION
//</source>