<?php
/*<!--<wikitext>-->
{{Extension
|name        = UserSettingsChangedLog
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/UserSettingsChangedLog/ SVN]
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
Provides logging of user settings changes.

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]
* Patched '/includes/User.php' file (get from BizzWiki SVN)

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Download patched '/includes/User.php' file from BizzWiki distribution and put in '$IP./includes' directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/UserSettingsChangedLog/UserSettingsChangedLog_stub.php');
</source>

== History ==
* Changed format of log entry to include [[User:username]]
* Fixed multiple entries in the log when the user changes a preference setting
* Fixed to not add log entries upon account creation

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[UserSettingsChangedLog::thisType][] = array( 
	'name'    		=> UserSettingsChangedLog::thisName,
	'version' 		=> StubManager::getRevisionId('$Id$'),
	'author'  		=> 'Jean-Lou Dupont',
	'description'	=> 'Provides logging of user settings changed', 
	'url' 		=> StubManager::getFullUrl(__FILE__),			
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
//</source>