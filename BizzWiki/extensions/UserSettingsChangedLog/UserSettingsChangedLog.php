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
$wgExtensionCredits['other'][] = array( 
	'name'    => 'UserSettingsChangedLog',
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => 'Provides logging of user settings changed', 
);
require_once('UserSettingsChangedLog.i18n.php');

class UserSettingsChangedLog
{
	public function __construct()
	{
		# Add a new log type
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]							= 'usetchglog';
		$wgLogNames  ['usetchglog']				= 'usetchglog'.'logpage';
		$wgLogHeaders['usetchglog']				= 'usetchglog'.'logpagetext';
		$wgLogActions['usetchglog/usetchglog']  = 'usetchglog'.'logentry';
		$wgLogActions['usetchglog/save']     	= 'usetchglog'.'-saveok-entry';
		
		global $wgMessageCache;

		$msg = $GLOBALS[ 'msg'.__CLASS__ ];
		
		foreach( $msg as $key => $value )
			$wgMessageCache->addMessages( $msg[$key], $key );		
	}
	public function hUserSettingsChanged( &$user )
	{
		$message = wfMsgForContent( 'usetchglog'.'-save-text', $user->mRealName );
		
		$log = new LogPage( 'usetchglog' );
		$log->addEntry( 'saveok', $user->getUserPage(), $message );
		
		return true;
	}	
}
?>