<?php
/*<wikitext>
{| border=1
| <b>File</b> || FetchPartnerUserJob.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>

== History ==

== Code ==
</wikitext>*/
require_once('UserPartnerTable.php');

class FetchPartnerUserJob extends PartnerJob
{
	function __construct( $title=null, $parameters=null, $id = 0 ) 
	{
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct( 'fetchuser', Title::newMainPage()/* don't care */, $parameters, $id,
								'UserPartnerTable', 'fetchuser'
							 );
	}
	
} // end class declaration
?>