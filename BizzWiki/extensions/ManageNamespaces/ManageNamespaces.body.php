<?php
/*<!--<wikitext>-->
 <file>
  <name>ManageNamespaces.body.php</name>
  <version>$Id$</version>
  <package>Extension.ManageNamespaces</package>
 </file>
<!--</wikitext>-->*/
//<source lang=php>

class ManageNamespaces
{
	const thisType = 'parser';
	const thisName = 'ManageNamespaces';
	
	static $reqGroup = 'sysop';
	
	// Marker definition
	static $marker = '__MNS__$1__';
	
	// Registry Page
	static $rPage = 'MediaWiki:Registry/Namespaces';
	
	// map array containing the new
	// namespace mapping.
	var $nsMap;
	
	// name of global variable containing the
	// managed namespaces
	static $gName = 'bwManagedNamespaces';
	
	// filename of wikitext based special page
	static $spFilename = null;
	
	// filename containing the declaration of the managed namespaces
	static $mnName = null;

	// Sample page
	static $samplePageName;
	
	// update flag
	var $canUpdateFile;
	
	public function __construct() 
	{ 
		self::$spFilename = dirname(__FILE__).'/ManageNamespaces.specialpage.wikitext';
		self::$mnName = dirname(__FILE__).'/ManageNamespaces.namespaces.php';
		self::$samplePageName = dirname(__FILE__).'/ManageNamespaces.namespaces.sample';
		
		// help the user a bit by making sure
		// the file is writable when it comes to update it.
		@chmod( self::$mnName, 700 );
		
		$this->nsMap = array();
		
		// only set when no errors found.
		$this->canUpdateFile = false;
	}
	
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	/**
		$index: must be a numeric
		$name: must be a string
	 */
	public function mg_mns( &$parser, $index, $name )
	{
		// only output one error message.
		static $error = false;
		if ($error)
			return;
		
		// Make sure that this parser function is only used
		// on the allowed registry page
		if (!$this->checkRegistryPage( $parser->mTitle))
			{ return wfMsg('managenamespaces'.'-incorrect-page'); $error = true; }
		
		// Also make sure that the user has the appropriate right
		if (!$this->checkRight())
			{ return wfMsg('managenamespaces'.'-insufficient-right'); $error = true; }
		
		// at this point, just accumulate the requested changes	
		$this->nsMap[] = array( $index => $name );
		return $this->getMarker( count( $this->nsMap )-1 );
	}
	/**
		This hook injects the wikitext 'special page' like text
	 */
	public function hParserAfterTidy( &$parser, &$text )
	{
		if (empty( $this->nsMap ))
			return true;
		
		$begin = wfMsg( 'managenamespaces'.'-table-begin' );
		$end = wfMsg( 'managenamespaces'.'-table-end' );
				
		$lastIndex = count( $this->nsMap ) -1 ;
		
		foreach( $this->nsMap as $index => &$e )
		{
			$row = $this->getFormattedRow( $index, $e );
			// write table header on first element.
			if ( 0 == $index )
				$row = $begin.$row;
		
			// write table footer		
			if ( $lastIndex == $index )
				$row = $row.$end;
		
			$this->replaceMarker( $index, $row );
		}
	
		return true;
	}
	/**
		This hook saves the new namespace configuration to the file.
	 */
	public function hArticleSave( &$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags )
	{
		// just trap events related to the registry page in question here
		if ( !$this->checkRegistryPage( $article ) )
			return true;
		
		// .. and of course make sure the user has the required right
		if (!$this->checkRight())
			return true;
		
		if ($this->canUpdateFile())
			/*$r =*/ $this->updateFile();
			
		#$this->updateLog( $r );
		
		return true;
	}
	
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	protected function checkRegistryPage( &$object )
	{
		if ($object instanceof Title)
			return (($object->getFullText() == self::$rPage) ? true:false );
		return (( $object->mTitle->getFullText() == self::$rPage ) ? true:false);	
	}
	protected function checkRight()
	{
		global $wgUser;
		return in_array( self::$reqGroup, $wgUser->getEffectiveGroups());
			
	}
	protected function canUpdateFile() { return $this->canUpdateFile; }
	
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	/**
		A marker will be placed in the text being parsed.
		This marker serves to report error messages as well as
		result presentation i.e. table format of the namespaces managed.
	 */
	protected function getMarker( $index )
	{
		return str_replace( '$1', $index, self::$marker );
	}

	protected function getFormattedRow( $index, &$e )
	{
		
	}
	protected function replaceMarker( $index, &$text )
	{
		$p = str_replace( '$1', $index, self::$marker );
		$text  = str_replace( $p, $text, $text );
	}

	/**
		The 'immutable' list contains the namespaces that cannot be
		managed through this extension.
		
		The list in question is ($wgCanonicalNamespaceNames - $bwManagedNamespaces)
	 */
	protected function getImmutableNamespaceList()
	{
		global $wgCanonicalNamespaceNames, $bwManagedNamespaces;
		return array_diff($wgCanonicalNamespaceNames, $bwManagedNamespaces);	
	}
	
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	/**
	 */
	private function readFile( $fn )
	{
		return file_get_contents( $fn );
	}
	/**
	 */
	private function updateFile( $fn, &$contents )
	{
		return file_put_contents( $fn, $contents );	
	}

	#private function updateLog( &$result ){	}


} // end class
//</source>