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


== Features ==


== Dependancy ==
* StubManager Extension

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Download this extension and place it in the extension directory under 'EmailLog' directory
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub( 'EmailLog', $IP.'/extensions/EmailLog/EmailLog.php', array( 'EmailUserComplete' ) );
</source>

== History ==

== Code ==
</wikitext>*/
$wgExtensionCredits['other'][] = array( 
	'name'    => 'EmailLog',
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => 'Provides logging of user-to-user emailing activities', 
);

class EmailLog
{
	
	public function hEmailUserComplete( $to, $from, $subject, $text )
	{
		
		return true;
	}	
}
?>