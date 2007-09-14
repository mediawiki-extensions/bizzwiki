<?php
/*<!--<wikitext>-->
 <file>
  <name>ManageNamespaces.body.php</name>
  <version>$Id$</version>
  <package>Extension.ManageNamespaces</package>
 </file>
<!--</wikitext>-->*/
//<source lang=php>

class ManageNamespaces extends SpecialPage
{
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
		
		parent::__construct("ManageNamespaces", self::$spFilename ); 
		
		self::$mnName = dirname(__FILE__).'/ManageNamespaces.namespaces.php';
		
		// help the user a bit by making sure
		// the file is writable when it comes to update it.
		@chmod( self::$mnName, 700 );
	}
	protected function init()
	{
		
	}
	
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	private function readFile()
	{
		
	}

	private function writeFile()
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

		$wgOut->setPagetitle( wfMsg( "desysoptitle" ) );
		$text = wfMsg( "desysopok", $this->mUser );
		$text .= "\n\n";
		$wgOut->addWikiText( $text );
		$this->showForm();
	}

	function showFail( $msg = 'set_rights_fail' ) 
	{
		global $wgOut, $wgUser;

		$wgOut->setPagetitle( wfMsg( "desysoptitle" ) );
		$this->showForm( wfMsg( $msg, $this->mUser ) );
	}

} // end class
//</source>