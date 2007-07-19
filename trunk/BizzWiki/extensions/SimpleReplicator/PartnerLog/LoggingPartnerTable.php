<?php
/*<wikitext>
{| border=1
| <b>File</b> || LoggingPartnerTable.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>

== Implementation ==

== NOTES ==
 
== History ==

== Code ==
</wikitext>*/

class LoggingPartnerTable extends PartnerObjectClass
{
	var $limit;
	
	static $paramsList = array( 
								'logid'		=> 'log_id',
								'timestamp'	=> 'log_timestamp',			// ok
								''			=> 'log_type',
								''			=> 'log_action',
								''			=> 'log_timestamp',
								''			=> 'log_user',
								''			=> 'log_namespace',
								''			=> 'log_title',
								''			=> 'log_comment',
								''			=> 'log_params',
								''			=> 'log_id',
								''			=> 'log_deleted',
							);

	public function __construct( )
	{
		parent::__construct( self::$paramsList, 'logging_partner', 'log_id', 'log_timestamp', 'log', null);

		// limit of log elements to fetch from partner at any one time.
		$this->limit 	= 500;		
	}
	
	protected function formatURL( $start, $end, $limit, $dir )
	{
		$dir = '&rcdir='.$dir;
		
		if ($limit !== '')
			$limit = '&rclimit='.$limit;
		if ($start !== '')
			$start = '&rcstart='.$start;
			
		return '/api.php?action=query&list=recentchanges&format=xml&rcprop=user|comment|flags|timestamp|title|ids|sizes'.$start.$limit.$dir;
	}

} // end class
?>