<?php
/*<!--<wikitext>-->
{{Extension
|name        = rsync
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id: rsync_stub.php 667 2007-08-16 00:54:03Z jeanlou.dupont $)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/rsync/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose==
This is the 'stub' file for the [[Extension:rsync]] extension.

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/
if (!class_exists('StubManager'))
	echo 'Extension:rsync <b>requires</b> Extension:StubManager';
elseif (!method_exists('StubManager','getVersion'))
{
	echo "Extension:rsync <b>requires</b> Extension:StubManager of version > 757.\n";
	echo "This warning could also be the result of an unsupported PHP version (requires at least PHP v5.1.x).";
}
elseif (!StubManager::isExtensionRegistered( 'Backup' ))
	echo 'Extension:rsync <b>requires</b> Extension:Backup';	
else
{
StubManager::createStub2(	array(	'class' 		=> 'rsync', 
									'classfilename'	=> $bwExtPath.'/rsync/rsync.php',
									'hooks'			=> array(	'Backup' ),
								)
						);
if (defined('NS_FILESYSTEM'))	StubManager::configureExtension('rsync', 'enss', array( NS_FILESYSTEM ) );
if (defined('NS_DIRECTORY'))	StubManager::configureExtension('rsync', 'enss', NS_DIRECTORY );
}
//</source>
