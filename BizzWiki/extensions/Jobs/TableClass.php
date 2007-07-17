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
	
	public function __construct( $tableName, $indexName, $timestampName=null )
	{
		$this->tableName = $tableName;
		$this->indexName = $indexName;
		$this->timestampName = $timestampName;
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
	 */
	public function getFirstHole()
	{
		$index = $this->indexName;
		$table = $this->tableName;

		// try limit case first.
		$dbr = wfGetDB(DB_SLAVE);		
		$sql="SELECT $index FROM $table WHERE ($index=1);";
		$res = $dbr->query( $sql, __METHOD__ );
		$first_row = $dbr->fetchObject( $res );

		if (!isset( $first_row->$index ))
			return 1;
		
		// next, try the generic case.
		
		$sql = <<<EOT
SELECT MIN( a.incid ) AS hole_id
FROM (
SELECT $index +1 AS incid
FROM $table
ORDER BY $index ASC
)a
WHERE a.incid NOT
IN (
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
			$select = array_merge( $select, array( $ts ) );
		
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
			
		$ts = null;
		if (isset( $row->$ts ))
			$ts = $row->$ts;
		
		return $before;		
	}
	public function checkExistTable()
	{
		$dbr = wfGetDB(DB_SLAVE);
		return $dbr->tableExists($this->tableName);
	}
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
		$dbw = wfGetDB( DB_MASTER );

		foreach( $lst as $index => &$e )
			$dbw->replace( $this->tableName, null, $e, __METHOD__ );

		$dbw->commit();		
		wfDebug( __METHOD__.": end \n" );		
	} // end insert
	
} // end class
?>