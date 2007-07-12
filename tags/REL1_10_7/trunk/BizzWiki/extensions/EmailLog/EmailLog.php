<?php
/*<wikitext>
{| border=1
| <b>File</b> || EmailLog.php
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
$wgExtensionCredits[EmailLog::thisType][] = array( 
	'name'    => EmailLog::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => 'Provides logging of user-to-user emailing activities', 
);
require_once('EmailLog.i18n.php');

class EmailLog
{
	const thisType = 'other';
	const thisName = 'EmailLog';
	
	public function __construct()
	{
		# Add a new log type
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]                        = 'emaillog';
		$wgLogNames  ['emaillog']            = 'emailloglogpage';
		$wgLogHeaders['emaillog']            = 'emailloglogpagetext';
		$wgLogActions['emaillog/sentok']     = 'emaillog-sentok-entry';
		
		global $wgMessageCache;

		$msg = $GLOBALS[ 'msg'.__CLASS__ ];
		
		foreach( $msg as $key => $value )
			$wgMessageCache->addMessages( $msg[$key], $key );		
	}
	public function hEmailUserComplete( $to, $from, $subject, $text )
	{
		global $wgUser;
		
		$toname = $to->name;
		$fromname = $from->name;
		
		$message = wfMsgForContent( 'emaillog-sent-text', $fromname, $toname );
		
		$log = new LogPage( 'emaillog' );
		$log->addEntry( 'sentok', $wgUser->getUserPage(), $message );
		
		return true;
	}	
}
?>