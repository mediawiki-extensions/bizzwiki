<?php
/*<wikitext>
{| border=1
| <b>File</b> || PartnerTable.php
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

abstract class PartnerTable
{
	var $tableName;
	var $indexName;
	
	public function __construct( $tableName, $indexName )
	{
		$this->tableName = $tableName;
		$this->indexName = $indexName;
	}

	/**
		Function which returns the first 'hole' find in the table
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
		
		$sql = <<<EOT
SELECT MIN( a.incid ) AS hole_id
FROM (
SELECT $index +1 AS incid
FROM $table
)a
WHERE a.incid NOT
IN (
SELECT $index
FROM $table);
EOT;
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query( $sql, __METHOD__ );

		$row = $dbr->fetchObject( $res );
		
		$hole = null;
		if (isset( $row->hole_id ))
			$hole = $row->hole_id;
			
		return $hole;
	}
	public function getIdBeforeFirstHole( $holeid )
	{
		$dbr = wfGetDB( DB_SLAVE ); 
		$index = $this->indexName;
		
		$row = $dbr->selectRow( $this->tableName,
								array(	$index ),			// select
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
			
		return $before;		
	}
	public function checkExistTable()
	{
		$dbr = wfGetDB(DB_SLAVE);
		return $dbr->tableExists($this->tableName);
	}
	public function getLastId( )
	{
		$dbr = wfGetDB( DB_SLAVE ); 
		$index = $this->indexName;
		
		$row = $dbr->selectRow( $this->tableName,
								array(	$index ),			// select
								null,						// 'WHERE'
								__METHOD__,					// debug info.
								array(
									'ORDER BY'  => $index.' DESC',
									'LIMIT' => 1,
									)
						      );

		$last = null;
		if (isset( $row->$index ))
			$last = $row->$index;
			
		return $last;		
	}
	
} // end class
?>