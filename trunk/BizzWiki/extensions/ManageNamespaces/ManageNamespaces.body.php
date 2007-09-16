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
	static $iNs;
	
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
		
		self::$iNs = $this->getImmutableNamespaceList();
		
		// Messages.
		global $wgMessageCache;

		$msg = $GLOBALS[ 'msg'.__CLASS__ ];
		
		foreach( $msg as $key => $value )
			$wgMessageCache->addMessages( $msg[$key], $key );		
	}

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	/**
		$index: must be a numeric
		$name: must be a string
	 */
	public function mg_mns( &$parser, $index, $name, $separator = '||' )
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
		
		// Perform validations
		if (!$this->validateIndex( $index, $msg ))
			{ $index = $msg; $this->canUpdateFile = false; }
			
		if (!$this->validateName( $name, $msg ))
			{ $name = $msg;  $this->canUpdateFile = false; }
		
		// at this point, just accumulate the requested changes	
		$this->nsMap[] = array( $index => $name );
		
		return $index.$separator.$name;
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
			$r = $this->updateFile( $msg );
		else
		{
			$text .= wfMsg( 'managenamespaces'.'-file-not-updated' );
			return true;
		}	

		$text .= $msg;
		
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
	
	protected function validateIndex( $index, &$msg )
	{
		$r = (!isset( self::$iNs[$index] ));
		if (!$r)
			$msg = wfMsgForContent( 'managenamespaces'.'-invalid-index', $index );
		return $r;
	}
	protected function validateName( $name, &$msg )
	{
		$r = (! in_array( $name, self::$iNs ));
		if (!$r)
			$msg = wfMsgForContent( 'managenamespaces'.'-invalid-name', $name );
		
		return $r;
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
		
		if (!is_array($bwManagedNamespaces))
			return $wgCanonicalNamespaceNames;

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
		echo __METHOD__;
		#return file_put_contents( $fn, $contents );	
	}

	#private function updateLog( &$result ){	}


} // end class
require('ManageNamespaces.i18n.php');
//</source>