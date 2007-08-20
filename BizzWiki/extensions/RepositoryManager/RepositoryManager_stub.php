<?php
/*<!--<wikitext>-->
{{Extension
|name        = RepositoryManager
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/RepositoryManager/ SVN]
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


== Features ==


== Dependancy ==
* [[Extension:StubManager|StubManager extension]]
* [[Extension:NamespaceManager|NamespaceManager extension]]
* [[Extension:SecureProperties|SecureProperties extension]]
* [[Extension:PageServer|PageServer extension]]

== Installation ==
To install independantly from BizzWiki:
* Declare the namespace 'NS_REPO' in 'LocalSettings.php' (see example below)
** Add to the 'extra namespaces' global variable
* Download & Install [[Extension:NamespaceManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php':
<source lang=php>
require('extensions/NamespaceManager/NamespaceManager.php'); # must already be present since
                                                             # extension NamespaceManager should be installed

define('NS_REPO', 100);                                       # choose a free ID >=100 
$wgExtraNamespaces[NS_REPO]   = 'Repository';                 # [[Repository:XYZ]]
$wgCanonicalNamespaceNames[NS_REPO] = 'Repository'; 
require('extensions/ExtensionManager/ExtensionManager_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

// Do a quick check and echo a useful message
// if the dependent extensions are not installed.
if (class_exists('StubManager') && class_exists('NamespaceManager') && defined('EXTENSION_SECUREPROPERTIES')
	&& defined('EXTENSION_PAGESERVER') && defined('NS_REPO') 
	)
{
$wgExtensionCredits['other'][] = array( 
	'name'    	=> 'RepositoryManager',
	'version' 	=> NamespaceManagers::getRevisionId('$Id$'),
	'author'  	=> 'Jean-Lou Dupont',
	'description' => "Provides maintenance of repositories. ", 
	'url' 		=> NamespaceManagers::getFullUrl(__FILE__),	
);

require('RepositoryManager.i18n.php');
NamespaceManagers::register( NS_REPO, 'RepositoryManager', dirname(__FILE__).'/RepositoryManager.php' );

}// end startup checks.
// help the sysop.
else
{
	if (!class_exists('StubManager'))
		echo "<b>RepositoryManager:</b> missing dependancy [[Extension:StubManager]].\n";	

	if (!class_exists('NamespaceManager'))
		echo "<b>RepositoryManager:</b> missing dependancy [[Extension:NamespaceManager]].\n";	

	if (!defined('EXTENSION_SECUREPROPERTIES'))
		echo "<b>RepositoryManager:</b> missing dependancy [[Extension:SecureProperties]].\n";	

	if (!defined('EXTENSION_PAGESERVER'))
		echo "<b>RepositoryManager:</b> missing dependancy [[Extension:PageServer]].\n";	

	if (!defined('NS_REPO'))
		echo "<b>RepositoryManager:</b> namespace NS_REPO not defined.\n";	

}
//</source>
