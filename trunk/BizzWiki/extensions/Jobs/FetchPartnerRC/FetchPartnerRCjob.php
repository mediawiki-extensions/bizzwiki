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

class FetchPartnerRCjob extends Job
{
	var $url;
	
	function __construct( $title, $params, $id = 0 ) 
	{
		// ( $command, $title, $params = false, $id = 0 )
		parent::__construct( 'fetchRC', Title::newMainPage()/* don't care */, $params, $id );
		
		$this->url = FetchPartnerRC::$partner_url;
	}

	function run() 
	{

		return true;
	}
	
	// http_get from partner
	// check code -> error --> log
	
	
	
	// filter 'fetchRC' logs from the partner!
	
	// make sure we only fetch from the point where we had stopped previously
	// use rc_id identifier / rc_timestamp for this purpose.
	  
	
} // end class declaration
?>