<?php
/*<wikitext>
{| border=1
| <b>File</b> || FileSystemSyntaxColoring.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension 'colors' a page in the NS_FILESYSTEM namespace based on its syntax.

== Features ==
* Can be used independantly of BizzWiki environment 
* No mediawiki installation source level changes
* For parser cache integration outside BizzWiki, use ParserCacheControl extension
* Uses the hook 'SyntaxHighlighting' or defaults to PHP's highlight

== Dependancy ==
* ExtensionClass

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<geshi lang=php>
require('extensions/ExtensionClass.php');
require('extensions/FileSystemSyntaxColoring/FileSystemSyntaxColoring.php');
</geshi>

== History ==
* Added 'wiki text' section support
* Added support for hook based syntax highlighting
* Moved singleton invocation to end of file to accomodate some PHP versions

== Code ==
</wikitext>*/

class FileSystemSyntaxColoring extends ExtensionClass
{
	const thisName = 'FileSystem Syntax Coloring';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	const id       = '$Id$';	
		
	var $found;
	var $text;
	var $lang;
	
	// add other mappings if required.
	static $map = array( 
							'php' => 'php',
							'js'  => 'javascript',
							'xml' => 'xml',
							'css' => 'css',
							'py'  => 'python',
							#'' => '',
						);

	public static function &singleton( )
	{ return parent::singleton( ); }
	
	// Our class defines magic words: tell it to our helper class.
	public function FileSystemSyntaxColoring() 
	{ 
		parent::__construct( ); 
	
		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'    => self::thisName, 
			'version' => self::getRevisionId( self::id ),
			'author'  => 'Jean-Lou Dupont', 
			'description' =>  'Syntax highlights filesystem related pages',
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	
	public function setup()
	{
		parent::setup();
		
		$this->text  = null;
		$this->found = false;
	}

	public function hArticleAfterFetchContent( &$article, &$content )
	{
		// we are only interested in page views.
		global $action;
		if ($action != 'view') return true;
		
		// grab the content for later inspection.
		$this->text = $article->mContent;		
	}

	public function hParserBeforeStrip( &$parser, &$text, &$mStripState )
	// wfRunHooks( 'ParserBeforeStrip', array( &$this, &$text, &$this->mStripState ) );
	{
		// first round of checks
		if (!$this->isFileSystem( $parser )) return true; // continue hook-chain
		
		// since the parser is called multiple times, 
		// we need to make sure we are dealing the with article per-se
		if (strcmp( $this->text, $text)!=0 ) return true;
		
		// check file extension & map to language
		$titre = $parser->mTitle->getText();
		
		$ext = $this->getExtension( $titre );
		
		$this->lang = $this->getLanguage( $ext );
		
		$this->found = true;
		$this->text = $text;
		
		// Check for a <wikitext> section
		$text = $this->getWikitext( $text );
		
		return true;		
	}
	public function hParserAfterTidy( &$parser, &$text )
	{
		// the parser gets called two times in one transaction
		// when editing/creating an article and when viewing the resulting page.
		// Use ParserCacheControl extension or patched Article::editUpdates.

		if (! $this->found ) return true;
		$this->found = false;
		
		$this->removeWikitext();
		
		$stext = $this->highlight( $this->text, $this->lang );
		
		// merge with possible <wikitext> section
		$text .= $stext;
		
		return true;	
	}
	
	private function isFileSystem( &$parser )
	{
		// is the namespace defined at all??
		if (!defined('NS_FILESYSTEM')) return false;
		
		// is the current article in the right namespace??
		$ns = $parser->mTitle->getNamespace();
		
		return ($ns == NS_FILESYSTEM ? true:false );
	}

	private function getWikitext( &$text )
	{
		$p = "/<wikitext\>(.*)(?:\<.?wikitext)>/siU";
					
		$result = preg_match( $p, $text, $m );
		if ( ($result===FALSE) or ($result===0)) return '';

		return $m[1];
	}
	private function removeWikitext()
	{
		$this->text = preg_replace( "/\<wikitext(.*)wikitext\>/siU", "wikitext", $this->text);	
	}
	
	private function highlight( &$text, $lang='php', $lines=0 ) 
	{
		if ( wfRunHooks('SyntaxHighlight', array( &$text, $lang, $lines, &$result ) ) )
			return $result;
		else
			return $this->default_highlight( $text );
	}
	private function default_highlight( &$text )
	{
		ob_start();
		highlight_string( $this->text );
		$stext = ob_get_contents();
		ob_end_clean();

		return $stext;
	}
	private function getExtension( $titre )
	{
		$pos = strrpos($titre,'.');
		if ( $pos === false ) 
			$ext = '';	
		else
			$ext = substr( $titre, $pos+1 );

		return $ext;		
	}
	private function getLanguage( $ext ) { return self::$map[ $ext ]; }
	
} // end class definition.

FileSystemSyntaxColoring::singleton();
?>