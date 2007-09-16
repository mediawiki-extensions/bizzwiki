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
	
	public function __construct() 
	{ 
		self::$spFilename = dirname(__FILE__).'/ManageNamespaces.specialpage.wikitext';
		self::$mnName = dirname(__FILE__).'/ManageNamespaces.namespaces.php';
		
		// help the user a bit by making sure
		// the file is writable when it comes to update it.
		@chmod( self::$mnName, 700 );
		
		$this->nsMap = array();
	}
	
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	/**
		$index: must be a numeric
		$name: must be a string
	 */
	public function mg_mns( &$parser, $index, $name )
	{
		// Make sure that this parser function is only used
		// on the allowed registry page
		if (!$this->checkRegistryPage( $parser->mTitle))
			return wfMsg('managenamespaces'.'-incorrect-page');
		
		// Also make sure that the user has the appropriate right
		if (!$this->checkRight())
			return wfMsg('managenamespaces'.'-insufficient-right');
		
		// at this point, just accumulate the requested changes	
		$this->nsMap[] = array( $index => $name );
		return $this->getMarker( count( $this->nsMap )-1 );
	}
	protected function getMarker( $index )
	{
		return str_replace( '$1', $index, self::$marker );
	}
	/**
		This hook injects the wikitext 'special page' like text
	 */
	public function hParserAfterTidy( &$parser, &$text )
	{
		$begin = wfMsg();
		$end = wfMsg();
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
		
		$r = $this->updateFile();
		$this->updateLog( $r );
		
		return true;
	}
	protected function checkRegistryPage( &$object )
	{
		if ($object instanceof Title)
			return (($object->getFullText() == self::$rPage) ? true:false );
		return (( $object->mTitle->getFullText() == self::$rPage ) ? true:false);	
	}
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
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

	private function readFile()
	{
		
	}

	private function updateFile()
	{
		
	}
	private function updateLog( &$result )
	{
		
	}

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	public function doSubmit()
	{
		
	}
	public function doShow( &$msg, &$page )
	{
		
	}

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function showSuccess() 
	{
		global $wgOut, $wgUser;

		$wgOut->setPagetitle( wfMsg( "managenamespaces-" ) );
		$text = wfMsg( "managenamespaces-", $this->mUser );
		$text .= "\n\n";
		$wgOut->addWikiText( $text );
		$this->showForm();
	}

	function showFail( $msg = 'managenamespaces-' ) 
	{
		global $wgOut, $wgUser;

		$wgOut->setPagetitle( wfMsg( "managenamespaces-" ) );
		$this->showForm( wfMsg( $msg, $this->mUser ) );
	}

} // end class
//</source>