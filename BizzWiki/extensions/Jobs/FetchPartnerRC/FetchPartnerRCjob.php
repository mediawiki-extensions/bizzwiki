<?php
/*<wikitext>
{| border=1
| <b>File</b> || FetchPartnerRCjob.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension fetches the 'recentchanges' table from the partner replication node.

== Features ==


== Dependancies ==
* [[Extension:ExtensionClass|ExtensionClass]]
* JobQueue.php
** Patched  from MW 1.10  *OR*
** MW 1.11

== Installation ==

== History ==

== Code ==
</wikitext>*/

class FetchPartnerRCjob extends Job
{
	function __construct( $title, $params, $id = 0 ) 
	{
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct( 'fetchRC', Title::newMainPage()/* don't care */, $params, $id );
	}

	function run() 
	{

		return true;
	}
	
}
?>