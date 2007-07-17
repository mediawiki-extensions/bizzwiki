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

require_once( dirname(dirname(__FILE__)).'/PartnerTable.php');

class RecentChangesPartnerTable extends PartnerTable
{
	public function __construct( $table = 'recentchanges_partner', $index = 'rc_id' )
	{
		return parent::__construct( $table, $index );
	}

} // end class
?>