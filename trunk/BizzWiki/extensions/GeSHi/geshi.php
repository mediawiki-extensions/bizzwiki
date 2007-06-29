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

* <b>SOURCE</b> can be 

== Examples ==

== Installation == 

</wikitext>*/
#
# New features:
# 1) Included "$lang-page" tag : enables highlighting the content of a page title.
#    e.g. <php-page> page title </php-page>
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

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: geshi extension will not work!';	
else
{
	include_once('geshi/geshi.php');
	require( "geshiClass.php" );
	geshiClass::singleton();
}
?>