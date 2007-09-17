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
		// just trap events related to the registry page in question here
		if ( !$this->checkRegistryPage( $parser->mTitle ) )
			return true;
		
		// .. and of course make sure the user has the required right
		if (!$this->checkRight())
			return true;


		if ($this->canUpdateFile())// && !empty($this->nsMap))
			$this->updateFile( $msg, $contents );
		else
		{
			$text .= wfMsg( 'managenamespaces'.'-file-not-updated' );
			return true;
		}	

		$text .= $msg;

		return true;	
	}
	/**
		This hook saves the new namespace configuration to the file.
	 */
/*	 
	public function hArticleSave( &$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags )
	{
		// just trap events related to the registry page in question here
		if ( !$this->checkRegistryPage( $article ) )
			return true;
		
		// .. and of course make sure the user has the required right
		if (!$this->checkRight())
			return true;
		
		if ($this->canUpdateFile())// && !empty($this->nsMap))
			$this->updateFile( $msg, $contents );
		else
		{
			$summary .= wfMsg( 'managenamespaces'.'-file-not-updated' );
			return true;
		}	

		$summary .= $msg;
		
		return true;
	}
*/
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

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
	private function updateFile( &$msg, &$contents )
	{
		// read the 'template' file
		$template = $this->readFile( self::$templatePageName );
		if ($template === false)
		{ $msg = wfMsg( 'managenamespaces'.'-template-file-read-error' ); return false; }
		
		$contents = wfMsg( 'managenamespaces'.'-open-code' );
		foreach( $this->nsMap as $index => &$name )
			$contents .= wfMsg( 'managenamespaces'.'-entry-code', $index, $name );
		$contents .= wfMsg( 'managenamespaces'.'-close-code' );
		
		$code = $this->fillTemplate( $template, $contents );
		
		$len = strlen( $code );
		$put_len = file_put_contents( self::$mnName , $code, LOCK_EX );	
		if ( $put_len !== $len )
			{ $msg = wfMsg( 'managenamespaces'.'-file-write-error' ).self::$mnName; return false; }

		$msg = wfMsg('managenamespaces'.'-file-update-success');
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
require('ManageNamespaces.i18n.php');
//</source>