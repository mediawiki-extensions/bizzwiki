<?php
/*<wikitext>
{| border=1
| <b>File</b> || WatchLog.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Provides logging of user-to-user emailing activities.

== Dependancy ==
* StubManager Extension

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Download this extension's file(s) and place them in the extension's directory
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'EmailLog', 
							$IP.'/extensions/EmailLog/EmailLog.php',
							$IP.'/extensions/EmailLog/EmailLog.i18n.php',							
							array('EmailUserComplete'),
							true
						 );
</source>

== History ==

== Code ==
</wikitext>*/
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
			
		$this->skip = false;
	}
	public function hWatchArticle( &$user, &$article )
	{
		return true;
	}
	public function hWatchArticleComplete( &$user, &$article )
	{
		if ($this->skip) return true;
		$this->skip = true;
		
		$message = wfMsgForContent( 'watchlog-watch-text', $article->mTitle->getPrefixedText() );
		
		$log = new LogPage( 'watchlog' );
		$log->addEntry( 'watchok', $user->getUserPage(), $message );
		
		return true;
	}
	public function hUnwatchArticleComplete( &$user, &$article )
	{
		$message = wfMsgForContent( 'watchlog-unwatch-text', $article->mTitle->getPrefixedText() );
		
		#$log = new LogPage( 'watchlog' );
		#$log->addEntry( 'unwatchok', $user->getUserPage(), $message );
		
		return true;
	}
}
?>