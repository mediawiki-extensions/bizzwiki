<?php
/*<!--<wikitext>-->
{{Extension
|name        = RecentChangesManager
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/RecentChangesManager/ SVN]
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

== Purpose ==
This is the stub for the extension. This is the only file which should be listed in <code>LocalSettings.php</code>.

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

StubManager::createStub(	'RecentChangesManager', 
							dirname(__FILE__).'/RecentChangesManager.php',
							null,					// i18n file			
							array('ArticleEditUpdatesDeleteFromRecentchanges'),	// hooks
							false, 					// no need for logging support
							null,					// tags
							null,					// parser Functions
							null
						 );
//</source>
