<?php
/*<wikitext>
== geshi extension ==
The purpose of this extension is to provide a 'generic syntax highlighting' function to Mediawiki.

== Revision Id ==
$Id$

== Features ==
Use <nowiki><geshi lang=LANG lines=LINES source=SOURCE></geshi></nowiki> where:
* <b>LANG</b>
* <b>LINES</b>
** line = 0 --> no line numbers
** line = 1 --> line numbers included

* <b>SOURCE</b> can be use to highlight
** page
** file

* Use <nowiki><php lines=LINES source=SOURCE></nowiki>
* Use <nowiki><source lines=LINES source=SOURCE></nowiki>

== History ==
* Added 'source' tag for aligning with some similar extensions.
* Added 'js' tag for highlighting 'Javascript'
* Added 'css' tag for highlighting 'CSS'

== Code ==
</wikitext>*/

$wgExtensionCredits[geshiClass::thisType][] = array( 
	'name'        	=> geshiClass::thisName, 
	'version'     	=> StubManager::getRevisionId( '$Id$' ),
	'author'      	=> 'Jean-Lou Dupont', 
	'description' 	=> 'Handles generic syntax highlighting',
	'url' 			=> StubManager::getFullUrl(__FILE__),			
);

include_once('geshi/geshi.php');
	
class geshiClass
{
	// constants.
	const thisName = 'geshiClass';
	const thisType = 'other';
	
	function __construct() {}

	public function tag_geshi( &$text, &$argv, &$parser )
	{
		$this->extractArgs( $argv, $lang, $lines, $source );
		return $this->executeMain( $text, $lang, $lines, $source );	
	}
	public function tag_source( &$text, &$argv, &$parser )
	{
		$this->extractArgs( $argv, $lang, $lines, $source );
		return $this->executeMain( $text, $lang, $lines, $source );	
	}
	public function tag_php( &$text, &$argv, &$parser )
	{
		$this->extractArgs( $argv, $lang, $lines, $source );
		return $this->executeMain( $text, 'php', $lines, $source );	
	}

	public function tag_js( &$text, &$argv, &$parser )
	{
		$this->extractArgs( $argv, $lang, $lines, $source );
		return $this->executeMain( $text, 'javascript', $lines, $source );	
	}
	public function tag_css( &$text, &$argv, &$parser )
	{
		$this->extractArgs( $argv, $lang, $lines, $source );
		return $this->executeMain( $text, 'css', $lines, $source );	
	}
	public function extractArgs( &$argv, &$lang, &$lines, &$source )
	{
		if (isset( $argv['lang'] ))  $lang = $argv['lang'];
		if (isset( $argv['lines']) ) $lines = $argv['lines'];
		if (isset( $argv['source'])) $source = $argv['source']; 
	}
	public function executeMain( &$text, $lang, $lines, $source )
	{
		switch( $source )
		{
			case 'page':
			    $title   = Title::newFromText( $text );
			    $article = new Article( $title );
				$text = $article->getContent();
				break;
				
			case 'file':				
				$text = $this->getFileText( $text, $result );
				if ( !$result )  
					return $text;
				break;
		}
		
		return $this->highlight( $text, $lang, $lines );			
	}
	private function highlight( &$text, $lang='php', $lines=false )
	{
        $geshi = new GeSHi($text, $lang );	

        $geshi->enable_classes(); 
        $geshi->set_header_type(GESHI_HEADER_PRE); 
        $geshi->set_overall_class("code"); 
        $geshi->set_encoding("utf-8");
		// [[mw:user:Brianegge]] suggestion
		$geshi->set_overall_style('background: #EEEEEE; border: padding: 0.2em'); 

		if (($lines == true) or ($lines==1) or ($lines=='1'))
			$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
		else
			$geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS);
		
        return "<style>".$geshi->get_stylesheet()."</style>".$geshi->parse_code();        
	}

	private function getFileText( $file_name, &$result )
	{
		global $IP;
		
		# Reference home of wiki installation
		$file_name=$IP."/".$file_name;
		
		$result = false;
		
		if (strtolower(basename($file_name))=="localsettings.php")
			return "The file <i>LocalSettings.php</i> can not be highlighted due to security issue.";
		if (strtolower(basename($file_name))=="adminsettings.php")
			return "The file <i>AdminSettings.php</i> can not be highlighted due to security issue.";
	
		
	        //Process the file
	        if (is_readable($file_name)) 
			{          
                $handle = fopen($file_name, "r");
                $contents = fread($handle, filesize($file_name));
                fclose($handle);
				$result = true;
	        } 
			else
                $contents = "File not Found! ($file_name)";

		return $contents;
	}

	public function hSyntaxHighlight( &$text, $lang, $lines, &$result )
	{
		$result = $this->highlight( $text, $lang, $lines );

		return true; // be nice with other possible extensions.
	}
	
} // END CLASS DEFINITION

?>