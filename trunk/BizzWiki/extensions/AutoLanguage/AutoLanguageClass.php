<?php
/*<wikitext>
{| border=1
| <b>File</b> || AutoLanguageClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/

class AutoLanguageClass extends ExtensionClass
{
	// constants.
	const thisName = 'AutoLanguageClass';
	const thisType = 'other';
	const id       = '$Id$';	
	//
	static $exemptNamespaces = array( 	NS_CATEGORY,  // special treatement in 'Wiki.php'
										NS_TEMPLATE,  // !
										NS_IMAGE, 	  // special treatement in 'Wiki.php'
										NS_MEDIA, 	  // special treatement in 'Wiki.php'
										NS_SPECIAL,   // !
										NS_MEDIAWIKI  // !
									);
	static $exemptTalkPages = true;
	
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function AutoLanguageClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Automatic page language switching based on user preference',
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	public function setup() 
	{ parent::setup();	}

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
?>