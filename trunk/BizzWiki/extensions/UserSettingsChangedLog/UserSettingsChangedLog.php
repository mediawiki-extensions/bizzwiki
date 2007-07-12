<?php
/*<wikitext>
{| border=1
| <b>File</b> || UserSettingsChangedLog.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Provides logging of user settings changes.

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
							$IP.'/extensions/UserSettingsChangedLog/UserSettingsChangedLog.php',
							$IP.'/extensions/UserSettingsChangedLog/UserSettingsChangedLog.i18n.php',							
							array('UserSettingsChanged'),
							true
						 );
</source>

== History ==

== Code ==
</wikitext>*/
$wgExtensionCredits[UserSettingsChangedLog::thisType][] = array( 
	'name'    => UserSettingsChangedLog::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => 'Provides logging of user settings changed', 
);
require_once('UserSettingsChangedLog.i18n.php');

class UserSettingsChangedLog
{
	const thisType = 'other';
	const thisName = 'UserSettingsChangedLog';
	
	public function __construct()
	{
		# Add a new log type
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]							= 'usetchglog';
		$wgLogNames  ['usetchglog']				= 'usetchglog'.'logpage';
		$wgLogHeaders['usetchglog']				= 'usetchglog'.'logpagetext';
		$wgLogActions['usetchglog/usetchglog']  = 'usetchglog'.'logentry';
		$wgLogActions['usetchglog/saveok']     	= 'usetchglog'.'-saveok-entry';
		
		global $wgMessageCache;

		$msg = $GLOBALS[ 'msg'.__CLASS__ ];
		
		foreach( $msg as $key => $value )
			$wgMessageCache->addMessages( $msg[$key], $key );		
	}
	public function hUserSettingsChanged( &$user )
	{
		// we need some protection against multiple saves per transaction.
		// SpecialPreferences.php does multiple saves regularly...
		static $logDone = false;
		if ($logDone)
			return true;
		$logDone = true;
		
		$message = wfMsgForContent( 'usetchglog'.'-save-text', $user->mRealName );
		
		$log = new LogPage( 'usetchglog' );
		$log->addEntry( 'saveok', $user->getUserPage(), $message );
		
		return true;
	}	
}
?>