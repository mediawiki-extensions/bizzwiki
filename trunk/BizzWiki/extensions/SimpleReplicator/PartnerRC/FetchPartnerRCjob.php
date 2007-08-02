<?php
/*<wikitext>
{| border=1
| <b>File</b> || FetchPartnerRCjob.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>

== History ==

== Code ==
</wikitext>*/
require_once('RecentChangesPartnerTable.php');

class FetchPartnerRCjob extends PartnerJob
{
	function __construct( ) 
	{
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct(	'fetchRC', Title::newMainPage()/* don't care */, $parameters, $id, 
								'RecentChangesPartnerTable', 'ftchrclog' );
	}

} // end class declaration
?>