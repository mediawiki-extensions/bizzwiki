<?php
/*<!--<wikitext>-->
{{Extension
|name        = rsync
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
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

StubManager::createStub2(	array(	'class' 		=> 'rsync', 
									'classfilename'	=> $bwExtPath.'/rsync/rsync.php',
									'hooks'			=> array(	'RecentChange_save',
																'ArticleSaveComplete',
																#'hArticleDeleteComplete',
																#'hSpecialMovepageAfterMove',
																#'hAddNewAccount',
																#'hUploadComplete',
															),
								)
						);

//</source>
