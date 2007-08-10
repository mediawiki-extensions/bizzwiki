<?php
/*<!--<wikitext>-->
{{Extension
|name        = WatchLog
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/WatchLog/ SVN]
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
Provides logging of users' page watch/unwatch activities.

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/WatchLog/WatchLog_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/
$wgExtensionCredits[WatchLog::thisType][] = array( 
	'name'    => WatchLog::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => 'Provides logging of user-to-user emailing activities', 
);
require_once('WatchLog.i18n.php');

class WatchLog
{
	const thisType = 'other';
	const thisName = 'WatchLog';
	
	public function __construct()
	{
		# Add a new log type
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]                        = 'watchlog';
		$wgLogNames  ['watchlog']            = 'watchlog'.'logpage';
		$wgLogHeaders['watchlog']            = 'watchlog'.'logpagetext';
		$wgLogActions['watchlog/sentok']     = 'watchlog'.'-sentok-entry';
		
		global $wgMessageCache;

		$msg = $GLOBALS[ 'msg'.__CLASS__ ];
		
		foreach( $msg as $key => $value )
			$wgMessageCache->addMessages( $msg[$key], $key );
	}
	public function hWatchArticleComplete( &$user, &$article )
	{
		$message = wfMsgForContent( 'watchlog-watch-text', $article->mTitle->getPrefixedText() );
		
		$log = new LogPage( 'watchlog' );
		$log->addEntry( 'watchok', $user->getUserPage(), $message );
		
		return true;
	}
	public function hUnwatchArticleComplete( &$user, &$article )
	{
		$message = wfMsgForContent( 'watchlog-unwatch-text', $article->mTitle->getPrefixedText() );
		
		$log = new LogPage( 'watchlog' );
		$log->addEntry( 'unwatchok', $user->getUserPage(), $message );
		
		return true;
	}
}
//</source>