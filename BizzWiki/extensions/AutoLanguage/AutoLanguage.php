<?php
/*<wikitext>
{{Extension
|name        = AutoLanguage
|status      = stable
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/AutoLanguage/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}} 
== Purpose==
This extension provides viewing pages in the language specified by the user's preferences automatically.

== Features ==
* Based language is assumed to be 'en'
* Base 'page' (i.e. no /$lang sub-page) is assumed to be in 'en' language
* Parser Cache Friendly

== Usage ==
* Visit 'page' and redirection to 'page/$lang' will be effected IF $lang != 'en'
* 'page/en' can be visited as per normal
* Visit 'page/' to show 'page' without any redirection based on this extension
  (i.e. same as visiting 'page' )

== Dependancy ==
* [[Extension:StubManager]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'AutoLanguage', 
							'extensions/AutoLanguage/AutoLanguage.php',
							null,							
							array('ArticleFromTitle'),
							false
						 );
</source>

== Notes ==
This extension is heavily based on the 'Polyglot' extension found on Mediawiki.org.

== History ==
* Removed dependency on ExtensionClass

== Code ==
</wikitext>*/
$wgExtensionCredits[AutoLanguage::thisType][] = array( 
	'name'        => AutoLanguage::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Automatic page language switching based on user preference',
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);

class AutoLanguage
{
	// constants.
	const thisName = 'AutoLanguage';
	const thisType = 'other';
	//
	static $exemptNamespaces = array( 	NS_CATEGORY,  // special treatement in 'Wiki.php'
										NS_TEMPLATE,  // !
										NS_IMAGE, 	  // special treatement in 'Wiki.php'
										NS_MEDIA, 	  // special treatement in 'Wiki.php'
										NS_SPECIAL,   // !
										NS_MEDIAWIKI  // !
									);
	static $exemptTalkPages = true;
	
	function __construct( ) 
	{
		// automatically register the special namespaces of the BizzWiki platform
		// BUT won't break if not using BizzWiki
		if (defined('NS_BIZZWIKI'))		
			self::$exemptNamespaces[] = NS_BIZZWIKI;
		if (defined('NS_INTERWIKI'))					
			self::$exemptNamespaces[] = NS_INTERWIKI;
		if (defined('NS_FILESYSTEM'))								
			self::$exemptNamespaces[] = NS_FILESYSTEM;
	}

	function hArticleFromTitle( &$title, &$article ) 
	{
		global $wgLang, $wgRequest;

		if ($wgRequest->getVal( 'redirect' ) == 'no')
			return true;

		$ns = $title->getNamespace();

		if ( $ns < 0 
			|| in_array($ns,  self::$exemptNamespaces) 
			|| (self::$exemptTalkPages && Namespace::isTalk($ns)) )
		return true;

		$n    = $title->getDBKey();
		$lang = $wgLang->getCode();
		
		// case where 'page/' is visited.
		if (!$title->exists() && strlen($n)>1 && preg_match('!/$!', $n))
		{
			$t = Title::makeTitle($ns, substr($n, 0, strlen($n)-1));
			$article = new Article( $t );

			// ugly hack to circumvent a shortcoming of
			// wiki::initializeArticle method
			$title->mDbkeyform = $t->getDBkey();
			return true;
		}

		// base language is assumed to be 'en',
		// let the normal flow handle this one.
		if ( $lang == 'en' ) 
			return true;	

		// case where 'page/$lang' will be the new target
		$title2 = Title::makeTitle($ns, $n . '/' . $lang);
		
		// does the page exist? If not, stick with the base default language page.
		if (!$title2->exists())
			return true;

		// same ugly hack again.
		$title->mDbkeyform = $title2->getDBkey();
		
		$article = new Article( $title2 );

		return true;
	}

} // end class
