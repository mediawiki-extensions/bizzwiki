<?php
/*<wikitext>
{| border=1
| <b>File</b> || RecentChangesPartnerTable.php
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

class RecentChangesPartnerTable extends PartnerObjectClass
{
	var $limit;
	
	static $paramsList = array( 'rcid'		=> 'rc_id',				// BIZZWIKI specific
								'type'		=> 'rc_type', 
								'ns'		=> 'rc_namespace',
								'pageid'	=> 'rc_cur_id',			// checked
								'user'		=> 'rc_user',			// ok
							#				=> 'rc_user_text',		// CHECKME
								'bot'		=> 'rc_bot',			// ok
								'minor'		=> 'rc_minor',			// ok
								'new'		=> 'rc_new',			// ok
								'title'		=> 'rc_title', 			// ok
								'revid'		=> 'rc_this_oldid',		// checked 
								'old_revid'	=> 'rc_last_oldid',		// checked
							#				=> 'rc_moved_to_ns',	// CHECKME
							#				=> 'rc_moved_to_title',	// CHECKME
								'patrolled'	=> 'rc_patrolled',		// BIZZWIKI specific
							#				=> 'rc_ip',				// CHECKME							
							#				=> 'rc_old_len',		// CHECKME							
							#				=> 'rc_new_len',		// CHECKME							
							#				=> 'rc_deleted',		// CHECKME							
							#				=> 'rc_logid',			// CHECKME							
							#				=> 'rc_logtype',		// CHECKME							
							#				=> 'rc_log_action',		// CHECKME							
							#				=> 'rc_params',			// CHECKME							
								'timestamp'	=> 'rc_timestamp', 		// ok
							#	''			=> 'rc_cur_time',		// NEED TO FILL
								'comment'	=> 'rc_comment',		// checked
							);
	
	

	
	public function __construct( )
	{
		parent::__construct( self::$paramsList, 'recentchanges_partner', 'rc_id', 'rc_timestamp', 'rc','rc_cur_time' );

		// limit of rc elements to fetch from partner at any one time.
		$this->limit 	= 500;		
	}


	protected function formatURL( $start, $end, $limit, $dir )
	{
		$dir = '&rcdir='.$dir;
		
		if ($limit !== '')
			$limit = '&rclimit='.$limit;
		if ($start !== '')
			$start = '&rcstart='.$start;
			
		return '/api.php?action=query&list=recentchanges&format=xml'.$start.$limit.$dir;
	}

} // end class
?>