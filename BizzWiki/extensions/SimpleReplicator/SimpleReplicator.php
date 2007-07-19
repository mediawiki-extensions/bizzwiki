<?php
/*<wikitext>
{| border=1
| <b>File</b> || SimpleReplicator.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==


== Features ==


== Dependancy ==
* StubManager Extension

== History ==

== Code ==
</wikitext>*/

require('PartnerMachine.php');
require('PartnerObject.php');

// Stub required for logging & job functionality.
$wgAutoloadClasses['FetchPartnerRCjob'] = dirname(__FILE__).'/PartnerRC/FetchPartnerRCjob.php';

StubManager::createStub(	'FetchPartnerRC', 
							dirname(__FILE__).'/PartnerRC/FetchPartnerRC.php',
							dirname(__FILE__).'/PartnerRC/FetchPartnerRC.i18n.php',
							array('SpecialVersionExtensionTypes'),
							true // logging included
						 );


StubManager::createStub(	'FetchPartnerLog', 
							dirname(__FILE__).'/PartnerLog/FetchPartnerLog.php',
							dirname(__FILE__).'/PartnerLog/FetchPartnerLog.i18n.php',
							array('SpecialVersionExtensionTypes'),
							true // logging included
						 );


?>