<?php
/*<wikitext> <!--((@disable@))-->
{{Extension
|name        = FileManager
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/FileManager/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{SUBPAGENAME}} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->

== Purpose ==
This Mediawiki extension enables a user with the 'commitfile' right to edit files in the Mediawiki installation directory.  

== Features ==
* Can be used independantly of BizzWiki environment 
* New rights: 'readfile', 'commitfile'
* Logging
* New Namespace 'NS_FILESYSTEM'
* Support for titles beginning with small caps; need the title to be prefixed with '/'
** e.g. to have access to 'includes/Setup.php' just reference the title 'Filesystem:/includes/Setup.php'
* No auto summary upon page creation

== DEPENDANCY ==
* [[Extension:ExtensionClass]] (>=v1.92) 

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

== Installation ==
* Download all the files from the SVN link
* Place in a directory e.g. 'extensions/FileManager'
* Modify <code>LocalSettings.php</code>
<source lang=php>
require_once('extensions/ExtensionClass.php');
require('extensions/FileManager/FileManager.php');
</source>

== Code ==
<!--</wikitext>--><source lang=php>*/

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: FileManager extension will not work!';	
else
{
	require( 'FileManager.i18n.php' );
	require( "FileManagerClass.php" );
	FileManagerClass::singleton();
}

//</source>