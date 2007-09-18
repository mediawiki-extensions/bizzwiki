<?php
/*<!--<wikitext>-->
 <file>
  <name>ManageNamespaces.body.php</name>
  <version>$Id$</version>
  <package>Extension.ManageNamespaces</package>
 </file>
<!--</wikitext>-->*/
//<source lang=php>

require_once('ManageNamespaces.i18n.php');

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

	// Template page
	static $templatePageName;
	
	// update flag
	var $canUpdateFile;
	
	public function __construct() 
	{ 
		self::$spFilename = dirname(__FILE__).'/ManageNamespaces.specialpage.wikitext';
		self::$mnName = dirname(__FILE__).'/ManageNamespaces.namespaces.php';
		self::$templatePageName = dirname(__FILE__).'/ManageNamespaces.namespaces.template';
		
		// help the user a bit by making sure
		// the file is writable when it comes to update it.
		@chmod( self::$mnName, 700 );
		
		$this->nsMap = array();
		
		$this->canUpdateFile = true;
		
		self::$iNs = $this->getImmutableNamespaceList();
		
		// Log related
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]                     = 'mngns';
		$wgLogNames  ['mngns']            = 'mngnslogpage';
		$wgLogHeaders['mngns']            = 'mngnslogpagetext';
		$wgLogActions['mngns/updtok']	  = 'mngns'.'-updtok-entry';
		$wgLogActions['mngns/updtfail1']  = 'mngns'.'-updtfail1-entry';		
		$wgLogActions['mngns/updtfail2']  = 'mngns'.'-updtfail2-entry';				
		$wgLogActions['mngns/updtfail3']  = 'mngns'.'-updtfail3-entry';
		
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
		// relative to the Immutable Namespaces
		if (!$this->validateIndex( $index, $msg ))
			{ $index = $msg; $this->canUpdateFile = false; }
			
		if (!$this->validateName( $name, $msg ))
			{ $name = $msg;  $this->canUpdateFile = false; }

		// Perform validations
		// relative to the defined ones on this page
		if (!$this->validateIndexDefined( $index, $msg ))
			{ $index = $msg; $this->canUpdateFile = false; }
			
		if (!$this->validateNameDefined( $name, $msg ))
			{ $name = $msg;  $this->canUpdateFile = false; }
		
		// at this point, just accumulate the requested changes	
		$this->nsMap[$index] = $name;
		
		return $index.$separator.$name;
	}
	/**
	 */
	public function hParserAfterTidy( &$parser, &$text )
	{
		// just perform the update upon page view.
		// Can't anyhow do this on page save.
		global $action;
		if ($action !== 'view' )
			return true;
			
		// just trap events related to the registry page in question here
		if ( !$this->checkRegistryPage( $parser->mTitle ) )
			return true;
		
		// .. and of course make sure the user has the required right
		if (!$this->checkRight())
			return true;

		if (!$this->canUpdateFile())
			$action = 'updtfail3';
		else
			$this->updateFile( $action, $contents );

		$this->updateLog( $action );

		return true;	
	}

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	protected function updateLog( $action )
	{
		global $wgUser;
		$log = new LogPage( 'mngns' );
		$log->addEntry( $action, $wgUser->getUserPage(), '' );
	}
	protected function canUpdateFile() 
	{ 
		return $this->canUpdateFile; 
	}
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
	protected function validateIndexDefined( $index, &$msg )
	{
		$r = (!isset( $this->nsMap[$index] ));
		if (!$r)
			$msg = wfMsgForContent( 'managenamespaces'.'-invalid-index-2', $index );
		return $r;
	}
	protected function validateNameDefined( $name, &$msg )
	{
		$r = (! in_array( $name, $this->nsMap ));
		if (!$r)
			$msg = wfMsgForContent( 'managenamespaces'.'-invalid-name-2', $name );
		
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
	private function updateFile( &$action, &$contents )
	{
		// read the 'template' file
		$template = $this->readFile( self::$templatePageName );
		if ($template === false)
		{ 
			$action = 'updtfail1'; 
			return false; 
		}
		
		$contents = wfMsg( 'managenamespaces'.'-open-code' );
		foreach( $this->nsMap as $index => &$name )
			$contents .= wfMsg( 'managenamespaces'.'-entry-code', $index, $name );
		$contents .= wfMsg( 'managenamespaces'.'-close-code' );
		
		$code = $this->fillTemplate( $template, $contents );
		
		$len = strlen( $code );
		$put_len = file_put_contents( self::$mnName , $code, LOCK_EX );	
		if ( $put_len !== $len )
		{ 
			$action = 'updtfail2';
			return false; 
		}
		
		$action = 'updtok';

		return true;
	}
	/**
	 */
	private function readFile( $fn )
	{
		return file_get_contents( $fn );
	}

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	protected function fillTemplate( &$template, &$code )
	{
		return str_replace('$contents$', $code, $template );
	}

} // end class

//</source>