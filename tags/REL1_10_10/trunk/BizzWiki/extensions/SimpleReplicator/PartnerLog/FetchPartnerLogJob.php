<?php
/*<wikitext>
{| border=1
| <b>File</b> || FetchPartnerLogJob.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>

== History ==

== Code ==
</wikitext>*/
require_once('LoggingPartnerTable.php');

class FetchPartnerLogJob extends PartnerJob
{
	function __construct( ) 
	{
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct( 'fetchLog', Title::newMainPage()/* don't care */, $parameters, $id,
							'LoggingPartnerTable', 'fetchlog' 
							);
	}
	
} // end class declaration
?>