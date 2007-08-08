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
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/XYZ/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
== Purpose==
This is the 'stub' file for the [[Extension:rsync]] extension.

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

StubManager::createStub2(	array(	'class' 		=> 'rsync', 
									'classfilename'	=> $bwExtPath.'/rsync/rsync.php',
									'hooks'			=> array( 'ArticleSaveComplete' 
															),
								)
						);

//</source>
