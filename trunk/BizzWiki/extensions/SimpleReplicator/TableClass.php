<?php
/*<wikitext>
{| border=1
| <b>File</b> || TableClass.php
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

abstract class TableClass
{
	var $tableName;
	var $indexName;
	var $timestampName;
	var $currentTimestampName;
	var $table_prefix;			// e.g. RecentChanges == 'rc', Logging == 'log' etc.
		
	// status codes.
	const statusEmpty	= 0;	// not done yet.
	const statusOK		= 1;	// OK.
	const statusRetry	= 2;	// Will retry once.
	const statusFail	= 3;	// not found on partner.

	
	public function __construct( $table_prefix, $tableName, $indexName, $timestampName=null, $currentTimestampName )
	{
		$this->tableName = 				$tableName;
		$this->indexName = 				$indexName;
		$this->timestampName = 			$timestampName;
		$this->currentTimestampName =	$currentTimestampName;
		$this->table_prefix = 			$table_prefix;		
	}

	/**
		Function which returns the first 'hole' found in the table
		in question.
		A 'hole' is defined in terms of:
		- ???_id missing. Applicable to:
			'recentchanges' table
			'revision' table
			'page' table
			etc.
		Futhermore, a 'hole' is defined by the '*_status' field:
			- statusEmpty -> valid hole
			- statusOK    -> hole filled ;-)
			- statusRetry -> last chance to fill the hole
			- statusFail  -> finished retrying - do not touch.
	 */
	public function getFirstHole()
	{
		$index  = $this->indexName;
		$table  = $this->tableName;
		$status = $this->table_prefix.'_status';

		// conditions to declare a valid 'hole'
		$statEmpty = self::statusEmpty;
		$statRetry = self::statusRetry;		

		$dbr = wfGetDB(DB_SLAVE);		
		
		$sql = <<<EOT
SELECT MIN( a.incid ) AS hole_id
FROM 
(
 SELECT $index +1 AS incid
 FROM $table
 WHERE ($status = '{$statEmpty}' OR $status = '{$statRetry}')
 ORDER BY $index ASC
) a
WHERE a.incid NOT IN 
(
 SELECT $index
 FROM $table
 ORDER BY $index ASC
);
EOT;

		$res = $dbr->query( $sql, __METHOD__ );
		$row = $dbr->fetchObject( $res );
		
		$hole = null;
		if (isset( $row->hole_id ))
			$hole = $row->hole_id;
			
		return $hole;
	}
	public function getIdTsBeforeFirstHole( $holeid, &$ts, $getTs = false )
	{
		// protect against limit case (first hole == 1)
		if ($holeid <= 0)
			return null;
			
		$dbr = wfGetDB( DB_SLAVE ); 
		$index = $this->indexName;
		$ts = $this->timestampName;
		
		$select = array( $index );
		if ($getTs )
			$select[] = $ts;
		
		$row = $dbr->selectRow( $this->tableName,
								$select,					// select
								array( "$index < $holeid"), // 'WHERE'
								__METHOD__,					// debug info.
								array(
									'ORDER BY'  => $index.' DESC',
									'LIMIT' => 1,
									)
						      );

		$before = null;
		if (isset( $row->$index ))
			$before = $row->$index;
			
		if (isset( $row->$ts ))
			$ts = $row->$ts;
		else
			$ts = null;
		
		return $before;		
	}
	public function checkExistTable()
	{
		$dbr = wfGetDB(DB_SLAVE);
		return $dbr->tableExists($this->tableName);
	}
	/**
		Returns the index & timestamp (if any) of the 
		'last entry' in the table.
	 */
	public function getLastId( &$timestamp )
	{
		$dbr = wfGetDB( DB_SLAVE ); 
		$index = $this->indexName;
		$ts = $this->timestampName;

		if (isset( $this->timestampName ))
			$select = array( $index, $ts );
		else
			$select = array( $index );
		
		$row = $dbr->selectRow( $this->tableName,
								$select,				// select
								null,					// 'WHERE'
								__METHOD__,				// debug info.
								array(
									'ORDER BY'  => $index.' DESC',
									'LIMIT' => 1,
									)
						      );

		$last = null;
		$timestamp = null;
		if (isset( $row->$index ))
			$last = $row->$index;
		if (isset( $row->$ts ))
			$timestamp = $row->$ts;
		return $last;		
	}

	function updateList( &$lst )
	{
		if (empty( $lst ))
			return 0;

		$dbw = wfGetDB( DB_MASTER );

		$affected_rows = 0;

		foreach( $lst as $index => &$e )
		{
			$dbw->replace( $this->tableName, null, $e, __METHOD__ );
			$affected_rows += $dbw->affectedRows();
		}
		$dbw->commit();		
		wfDebug( __METHOD__.": end \n" );
		
		return $affected_rows;	
	} // end insert
	
} // end class
?>