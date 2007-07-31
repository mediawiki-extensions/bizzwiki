<?php
/*<wikitext>
ShowRedirectPageText.php by Jean-Lou Dupont

== Version ==
$Id$

== Purpose ==
This extension enables the display of the text included in a 'redirect' page.
The inclusion of wikitext in a redirect page is helpful in situations, for example, where redirects are used to manage a  'cluster' of Mediawiki serving machines.

== FEATURES ==
* No mediawiki installation source level changes
* No impact on parser caching

== DEPENDANCIES ==
* [[Extension:StubManager]]

== Installation ==
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'ShowRedirectPageText', 
							'extensions/ShowRedirectPageText/ShowRedirectPageText.php',
							null,
							array( 'ArticleViewHeader', 'OutputPageParserOutput' ),
							false,	// no need for logging support
							null,	// tags
							null,	// no parser functions
							null	// no magic words
						 );
</source>

== HISTORY ==
* Moved singleton invocation to end of file to accomodate some PHP versions
* Removed dependency on ExtensionClass
* Added 'stubbing' through StubManager

== TODO ==
* Clean up the '#redirect' wikitext before displaying

</wikitext>*/

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
?>