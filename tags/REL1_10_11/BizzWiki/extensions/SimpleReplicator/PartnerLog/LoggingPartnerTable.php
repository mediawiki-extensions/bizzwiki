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
	const tablePrefix = 'log';
	
	static $paramsList = array( 
								'logid'		=> 'log_id',			// ok
								'timestamp'	=> 'log_timestamp',		// ok
								'type'		=> 'log_type',			// ok
								'action'	=> 'log_action',		// ok
								'user'		=> 'log_user',			// CHECKME
								'ns'		=> 'log_namespace',		// ok
								'title'		=> 'log_title',			// ok
								'comment'	=> 'log_comment',		// ok
								#''			=> 'log_params',
								#''			=> 'log_deleted',
							);

	public function __construct( )
	{
		parent::__construct(self::tablePrefix, 
							self::$paramsList, 
							'logging_partner', 
							'log_id', 
							'log_timestamp', 
							'item', 
							null
							);

		// limit of log elements to fetch from partner at any one time.
		$this->limit 	= 500;		
	}
	
	protected function formatURL( $start, $end, $limit, $dir )
	{
		$dir = '&ledir='.$dir;
		
		if ($limit !== '')
			$limit = '&lelimit='.$limit;
		if ($start !== '')
			$start = '&lestart='.$start;

		return '/api.php?action=query&list=logevents&format=xml&leprop=user|type|comment|details|timestamp|title|ids'.$start.$limit.$dir;
	}

} // end class
?>