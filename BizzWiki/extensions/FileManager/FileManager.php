<?php
/*<wikitext> ((@disable@)) (($disable$))
FileManager.php
* 
* MediaWiki extension
* @author: Jean-Lou Dupont (http://www.bluecortex.com)
* $Id$
* 
== Purpose ==
This Mediawiki extension enables a user with the 'commitfile' right to edit files in the Mediawiki installation directory.  

== Features ==
* Can be used independantly of BizzWiki environment 
* New right:      'readfile', 'commitfile'
* Logging
* New Namespace 'NS_FILESYSTEM'
* Support for titles beginning with small caps; need the title to be prefixed with '/'
** e.g. to have access to 'includes/Setup.php' just reference the title 'Filesystem:/includes/Setup.php'
* No auto summary upon page creation

== DEPENDANCY ==
* Extension 'ExtensionClass' (>=v1.92) 

== History ==
* fixed for 'wgCapitalLinks' 
* fixed for suppressing PHP error messages on file_get_contents
* fixed logging messages
* disabled 'auto summary' upon page creation (clogs recentchanges, logs etc.)
* added 'reload' functionality
* Added some protection against !isset indexes in '$wgExtensionCredits'
* Added 'proprietary words' functionality
** @@file@@   replaces for the current filename
** @@mtime@@  replaces for the current filename last modification timestamp
** @@currentmtime@@ replaces for the current extracted filename last modification timestamp
* Removed extraneous '/' in the path name
* Added 'parser phase 2' magic words:
** (($#extractfile|@@file@@$))   : extracts the filename returned through the proprietary word '@@file@@'
** (($#extractmtime|@@mtime@@$)) : extracts 'mtime' returned through the proprietary word '@@mtime@@'

== TODO ==
* internationalization
* add 'edit from filesystem' functionality: capability to 'reload' a file from the filesystem
* enhance 'logging' through $type etc.

== Code ==
</wikitext>*/

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: FileManager extension will not work!';	
else
{
	require( 'FileManager.i18n.php' );
	require( "FileManagerClass.php" );
	FileManagerClass::singleton();
}
?>