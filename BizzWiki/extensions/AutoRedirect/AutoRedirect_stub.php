<?php
/*<!--<wikitext>-->
{{Extension
|name        = AutoRedirect_stub
|status      = beta
|type        = parser
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
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->

== Notes ==
See details about this extension on [[Extension:AutoRedirect]].

== Code ==
<!--</wikitext>--><source lang=php>*/

StubManager::createStub2(	array(	'class' 		=> 'AutoRedirect', 
									'classfilename'	=> dirname(__FILE__).'/AutoRedirect.php',
									'mgs' 			=> array( 'autoredirect' ),
								)
						);
//</source>
