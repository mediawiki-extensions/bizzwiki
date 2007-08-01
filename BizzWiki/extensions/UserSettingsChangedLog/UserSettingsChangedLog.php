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
* Patched '/includes/User.php' file (get from BizzWiki SVN)

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Download this extension's file(s) and place them in the extension's directory
* Download patched '/includes/User.php' file from BizzWiki distribution and put in '$IP./includes' directory
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'UserSettingsChangedLog', 
							$IP.'/extensions/UserSettingsChangedLog/UserSettingsChangedLog.php',
							$IP.'/extensions/UserSettingsChangedLog/UserSettingsChangedLog.i18n.php',							
							array('UserSettingsChanged'),
							true
						 );
</source>

== History ==
* Changed format of log entry to include [[User:username]]
* Fixed multiple entries in the log when the user changes a preference setting
* Fixed to not add log entries upon account creation

== Code ==
</wikitext>*/
$wgExtensionCredits[UserSettingsChangedLog::thisType][] = array( 
	'name'    		=> UserSettingsChangedLog::thisName,
	'version' 		=> StubManager::getRevisionId('$Id$'),
	'author'  		=> 'Jean-Lou Dupont',
	'description'	=> 'Provides logging of user settings changed', 
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
		// case 1: new account creation
		// Just bail out.
		global $wgUser;
		if ( $wgUser->getID() == 0 )
			return true;

		// Case 2:
		// we need some protection against multiple saves per transaction.
		// SpecialPreferences.php does multiple saves regularly...
		static $firstTimePassed = false;
		
		if ($firstTimePassed === false)
		{
			$firstTimePassed = true;
			return true;
		}
		
		$title = $user->getUserPage();
		$message = wfMsgForContent( 'usetchglog'.'-save-text', $user->mName );
		
		$log = new LogPage( 'usetchglog' );
		$log->addEntry( 'saveok', $title, $message );

		return true;		
	}
}
?>