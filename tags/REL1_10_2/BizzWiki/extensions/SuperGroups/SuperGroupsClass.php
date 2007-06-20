<?php
/*
 * SuperGroupsClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 *
 */
class SuperGroupsClass extends ExtensionClass
{
	const thisName = 'SuperGroups';
	const thisType = 'other';  

	const nssSize  = 256;  // namespace set size
	const nssOffset= 100;  // offset as additional precaution
	                       // against collision with standard MW namespaces.

	static $tableName = 'supergroups';
	static $errId;
		
	// This user's supergroup id.
	var $sgid;
	
	// We need to be sure we are getting initialized one of the first:
	// that's why we are setting 'true' for $initFirst parameter	
	public static function &singleton( )
	{ return parent::singleton( null , null, self::mw_style, 1, true); }
	
	public function SuperGroupsClass( $mgwords, $passingStyle, $depth, $initFirst) 
	{		return parent::__construct( $mgwords, $passingStyle, $depth, $initFirst );	}
	
	public function hUpdateExtensionCredits( &$sp, &$extensionTypes )
	// setup of this hook occurs in 'ExtensionClass' base class.
	{
		global $wgExtensionCredits;

		// check first for the existence of the required database table.
		$r = $this->checkTable();

		if ( $r ) $result1 = '<b><i>supergroups</i> database table found</b>';
		else      $result1 = '<b><i>supergroups</i> database table NOT found</b>';
		
		// then let's check if we had errors in the $sgExtraNamespace array
		if (!self::$errId) $result2 = '<b><i>$sgExtraNamespaces</i> array appears OK</b>';
		else               $result2 = '<b><i>$sgExtraNamespaces</i> array has errors</b>';
		
		$result = $result1." and ".$result2.". User's supergroup id=".$this->sgid;
		
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if ($el['name']==self::thisName)
				$el['description'].=$result;
		
		return true; // continue hook-chain.
	}

	public function setup()
	{
		parent::setup();
		
		self::$errId = null; // assume 'sgExtraNamespaces' array doesn't exist
		$this->sgid  = 0;     // assume default.
		
		$r = $this->checkTable();
		if ($r == false) return;
		
		// setup 'supergroup' id based on looking up
		// user in the 'supergroups' database table
		global $wgUser;
		$this->sgid = $this->getSgId( $wgUser );
		
		// namespace boundary checks
		// only allowed 256 namespaces per SuperGroup
		$r = $this->checkBoundaries();
		
		// if we got a 'null', then not much to do!
		// (i.e. there is no 'extra' namespaces asked for)
		if ($r === null) return;
		
		// if we got 'false', then an error with the
		// namespace ids was declared.
		if ( $r == false )
		{	self::$errId = true; return; }
		
		// if we got a 'true' response, then let's setup!
		$this->setupExtraNamespaceSet();
		
	}
	public function checkTable()
	{
		$dbr = wfGetDB(DB_SLAVE);
		return $dbr->tableExists(self::$tableName);
	}
	private function checkBoundaries()
	{
		global $sgExtraNamespaces;
		
		if ( !isset($sgExtraNamespaces) || empty($sgExtraNamespaces) )
			return null;
		
		// each 'id' must be between >=0 and <=255
		// to be valid.
		foreach( $sgExtraNamespaces as $id => &$el )
			if ( ( $id < 0) || ( $id > 255) )
				return false;
				
		return true;
	}

	private function setupExtraNamespaceSet()
	{
		global $sgExtraNamespaces;
		global $wgExtraNamespaces, $wgCanonicalNamespaceNames;
		
		// for each extra namespace we need to
		// to use 'supergroup' identifier to derive the 'base'
		// namespace set offset.
		foreach( $sgExtraNamespaces as $id => &$el )
		{
			$nid = ($this->sgid*self::nssSize) + $id + self::nssOffset;
			define( $el['id'], $nid );
			$wgExtraNamespaces[$nid] = $el['name'];
			$wgCanonicalNamespaceNames[$nid] = $el['name']; 
		}
	}

	public function getSgId( &$user )
	{
		$uid = $user->getID();
		
		// if we get a '0', then the user isn't logged
		if ($uid == 0) return 0;
		
		$dbr = wfGetDB( DB_SLAVE ); 
		
		$row = $dbr->selectRow( self::$tableName,
								array('sgr_group'),
								array('sgr_user' => $uid),
								'SuperGroups::getSgId'      );

		if (!empty($row))
			return $row->sgr_group;
	
		return 0;
	}

} // end class definition.
?>