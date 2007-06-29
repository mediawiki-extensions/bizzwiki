<?php
# GeSHiHighlight.php (confirmed working for MediaWiki 1.4 - 1.6.5 and GeSHi-1.0.7.3)
# Updated: 08 September 2006
# Updated: 14 February  2007
# 
# By: Andrew Nicol
# http://www.nanfo.com
# Example: http://tivoza.nanfo.com/wiki/index.php/EmuProxyZA_/_emuProxyZA.c
#
# Modified by: Jean-Lou Dupont
# Confirmed working with MediaWiki v1.8.2
# New features:
# 1) Included "$lang-page" tag : enables highlighting the content of a page title.
#    e.g. <php-page> page title </php-page>
#
# 2) Included "$lang-filex" tag : enables highlighting of a file located anywhere in the
#    wiki installation EXCEPT for the files "LocalSettings.php" & "AdminSettings.php" 
#    which expose sensitive information about a Mediawiki installation.
#    e.g. <php-filex> extensions/GeSHiHighlight.php<php-filex>
#
# 3) Included the "line" option: controls line numbering.
#    e.g. <php-page line=0>page name<php-page>
#         line = 0 --> no line numbers
#         line = 1 -->    line numbers included
#
# 4) Added "noshow" option: controls the highlighting
#    function. This is especially useful when executable code is embedded 
#    in a page and the action of viewing the page shouldn't trigger
#    the highlighting function. Example: a page level processor is assigned
#    to a page AND the code is actually located on the same page (!).
#    See "NamespaceLevelProcessor" & "RunPHP Page" extensions for more details.
#
# This extension allows for the easy implementation of adding code 
# highlighting to you wiki pages, you can use it for highlighting both 
# files and code. 
#
# It is a modified version of the extension originally by 
#   Coffman, (www.wickle.com)           
# and the later modifications by
#   E. Rogan Creswick (aka: Largos), creswick_at_gmail.com, wiki.ciscavate.org
#
# To highlight code, select you language choice from the $langArray list below 
# and add it as follows (e.g. using php language):
#   <php>
#   $foo = 45;
#   for ( $i = 1; $i < $foo; $i++ )
#   {
#     echo "$foo<br />\n";
#     --$foo;
#   }
#   </php>
#
# To highlight an uploaded file, select you language choice from the $langArray 
# list below and add it as follows (e.g. using php language):
#   <php-file>CodeExample.txt</php-file>
#
# You will need to upload GeSHi to your wiki for this extension to work, Geshi 
# is available at:
#   http://qbnz.com/highlighter/
# Once you have downloaded it, uncompress it and copy the files into a sub-directory
# named geshi in your extensions directory, you don't need to copy the doc or 
# contrib directory (they are large an unnecessary).
#
# To activate the extension, include it from your LocalSettings.php
# with: include("extensions/GeSHiHighlight.php");
#
# This extension makes use of another one of my extensions which prevents page
# caching when desired, it is very useful in this instance as when a using 
# highlighting on a file a cached page will show the old file without this
# extension. The purgePage extension is available at:
#   http://meta.wikimedia.org/wiki/User:Ajqnic:purgePage
# If you would rather remove the need for the extension find the line below 
# and just comment it out:
#   purgePage(); //Function in purgePage.php
#
# License: GeSHi Highlight is released under the Gnu Public License (GPL), and comes with no warranties.
# The text of the GPL can be found here: http://www.gnu.org/licenses/gpl.html

include_once('geshi/geshi.php');
             
$wgExtensionFunctions[] = "wfSyntaxExtension";

$wgExtensionCredits['other'][''] = array(
        'name' => 'GeSHiHighlight',
		'version' => '$Id$',
        'url' => 'http://meta.wikimedia.org/wiki/User:Ajqnic:GeSHiHighlight',
        'description' => 'Allows for the highlighting of various types of code including php, html, xml, sql, c, pascal, etc');
                                                                                                                                                              
function wfSyntaxExtension() {                                                                                                                                 
        global $wgParser, $wgVersion;

        $langArray = array(     "xml", "php", "java", "javascript", "c", "cpp", "bash", "css", "sql",
							);
							/*
								"actionscript-french", "actionscript", "ada", "apache", "applescript", 
                                "asm", "asp", "caddcl", "cadlisp", "csharp", 
                                "c_mac", "d", "delphi", "diff", "dos", "eiffel", 
                                "freebasic", "gml", "html4strict", "ini", "inno",  
                                "lisp", "lua", "matlab", "mpasm", "nsis", "objc", "ocaml-brief", 
                                "ocaml", "oobas", "oracle8", "pascal", "perl", "php-brief",  
                                "python", "qbasic", "ruby", "scheme", "sdlbasic", "smarty",  
                                "vb", "vbnet", "vhdl", "visualfoxpro");
		
							*/

        if ( version_compare( $wgVersion, "1.5" ) >= 0 ) { //If version 1.5 or above, $attrib param is included
                foreach ( $langArray as $lang ){                                                                                                                                                                                              
                        $wgParser->setHook( $lang, create_function( '$text,$attrib', 'return wfSyntaxCode("' . $lang . '", $text, $attrib);'));
                        $wgParser->setHook( $lang.'-file', create_function( '$file_name,$attrib', 'return wfSyntaxUploadFile("' . $lang . '", $file_name, $attrib);'));
					    $wgParser->setHook( $lang.'-filex', create_function( '$file_name,$attrib', 'return wfSyntaxFile("' . $lang . '", $file_name, $attrib);'));
						$wgParser->setHook( $lang.'-page', create_function( '$page_name,$attrib', 'return wfSyntaxPage("' . $lang . '", $page_name, $attrib);'));
                }
        } else {
                foreach ( $langArray as $lang ){                                                                                                                                                                                              
                        $wgParser->setHook( $lang, create_function( '$text', 'return wfSyntaxCode("' . $lang . '", $text);'));
                        $wgParser->setHook( $lang.'-filex', create_function( '$file_name', 'return wfSyntaxFile("' . $lang . '", $file_name);'));
						$wgParser->setHook( $lang.'-file', create_function( '$file_name', 'return wfSyntaxUploadFile("' . $lang . '", $file_name);'));
						$wgParser->setHook( $lang.'-page', create_function( '$page_name,$attrib', 'return wfSyntaxPage("' . $lang . '", $page_name);'));
                }
        }
}

function wfSyntaxCode($lang, $text, $attrib) {
        $geshi = new GeSHi($text, $lang, "extensions/geshi/geshi"); 
        return wfSyntaxDefaults($geshi, $attrib );
}

function wfSyntaxFile($lang, $file_name, $attrib) {
	global $IP;
	
	# Reference home of wiki installation
	$file_name=$IP."/".$file_name;
	
	if (strtolower(basename($file_name))=="localsettings.php")
		return "The file <i>LocalSettings.php</i> can not be highlighted due to security issue.";
	if (strtolower(basename($file_name))=="adminsettings.php")
		return "The file <i>AdminSettings.php</i> can not be highlighted due to security issue.";

	
        //Process the file
        if (is_readable($file_name)) {          
                $handle = fopen($file_name, "r");
                $contents = fread($handle, filesize($file_name));
                fclose($handle);
                $geshi = new GeSHi($contents, $lang, "extensions/geshi/geshi");
                return wfSyntaxDefaults($geshi, $attrib );
        } else {
                return "GeSHiHighlight: File not Found! ($file_name)";
        }
}

function wfSyntaxUploadFile($lang, $file_name, $attrib) {
        global $wgUploadPath;

        //purgePage(); //Function in purgePage.php (you may comment this out if you don't wish to make use of purgePage)

        //Determine the uploaded file_name path 
        $file_name = basename($file_name);
        $path = basename($wgUploadPath);
        $hash = md5( $file_name );
        $file_name = "{$path}/" . $hash{0} . "/" . substr( $hash, 0, 2 ) . "/{$file_name}";

        //Process the file
        if (is_readable($file_name)) {          
                //$geshi = new GeSHi("//nothing", $lang, "extensions/geshi/geshi");
                //$geshi->load_from_file($file_name, array($lang => array('txt')) );
                $handle = fopen($file_name, "r");
                $contents = fread($handle, filesize($file_name));
                fclose($handle);
                $geshi = new GeSHi($contents, $lang, "extensions/geshi/geshi");
                return wfSyntaxDefaults($geshi , $attrib );
        } else {
                return "GeSHiHighlight: File not Found!";
        }
}

function wfSyntaxPage($lang, $page_name, $attrib) 
{
	global $mediaWiki;

    $a=null;
    $t=Title::newFromText($page_name);
    $a=$mediaWiki->articleFromTitle($t);
	$a->loadContent();

    $geshi = new GeSHi($a->mContent, $lang, "extensions/geshi/geshi");
	
    return wfSyntaxDefaults($geshi , $attrib );
}

function wfSyntaxDefaults( $geshi, $attrib ) {
        $geshi->enable_classes(); 
        $geshi->set_header_type(GESHI_HEADER_PRE); 
        $geshi->set_overall_class("code"); 
        $geshi->set_encoding("utf-8"); 

		# see if the "noshow" attribute is present.
		if ( isset($attrib["noshow"]) )
			return '';

		# highlighting is ON by default.
		$line=true;
		if ( isset($attrib["line"]) )
			$line = $attrib["line"];

		if ($line == true) 
			$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
		else
			$geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS);
		
        return "<style>".$geshi->get_stylesheet()."</style>".$geshi->parse_code();        
}
?>