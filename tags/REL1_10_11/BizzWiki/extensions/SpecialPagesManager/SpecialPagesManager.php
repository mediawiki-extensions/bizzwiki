<?php
/*<wikitext>
{{Extension
|name        = SpecialPagesManager
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/XYZ/ SVN]
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
Gives the ability to a sysop to enhance a Mediawiki installation with custom 'special pages'
managed directly from the database (instead of PHP files).

== Features ==
* Default to 'Bizzwiki:Special Pages' page
* Can be changed through using
<source lang=php>
SpecialPagesManager->singleton()->setSpecialPage('page name');
</source>

== Dependancy ==
* [[Extension:StubManager]] extension

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/SpecialPagesManager/SpecialPagesManager.php');
</source>

== Rights ==
The extension defines a new right 'siteupdate' required to access the update functionality.

== History ==
* Removed dependency on ExtensionClass
* Added 'stubbing' capability through StubManager

== Code ==
</wikitext>*/

// Create the special page (the standard MW style one)
global $wgSpecialPages, $wgAutoloadClasses;
$wgSpecialPages['SpecialPagesManagerUpdater'] = 'SpecialPagesManagerUpdater';
$wgAutoloadClasses['SpecialPagesManagerUpdater'] = dirname(__FILE__) . "/SpecialPagesManagerUpdater.php" ;		

if (!isset( $bwExtPath ))
	$bwExtPath = $IP.'/extensions';

StubManager::createStub(	'SpecialPagesManagerClass', 
							$bwExtPath.'/SpecialPagesManager/SpecialPagesManagerClass.php',
							null,
							array( 'SpecialPageExecuteAfterPage' ),
							false,	// no need for logging support
							null,	// tags
							null,	// no parser functions
							null	// no magic words
						 );
