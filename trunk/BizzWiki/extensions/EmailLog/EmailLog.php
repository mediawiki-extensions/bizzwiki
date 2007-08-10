<?php
/*<!--<wikitext>-->
{{Extension
|name        = EmailLog
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/EmailLog/ SVN]
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
Provides logging of user-to-user emailing activities.

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/EmailLog/EmailLog_stub.php');
</source>

== History ==

== Code ==
<!--</wikitext>--><source lang=php>*/
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

//</source>