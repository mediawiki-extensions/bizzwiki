<?php
/*<!--<wikitext>-->
 <file>
  <name>ManageNamespaces.body.php</name>
  <version>$Id$</version>
  <package>Extension.ManageNamespaces</package>
 </file>
<!--</wikitext>-->*/
//<source lang=php>

class ManageNamespaces extends SpecialPageHelperClass
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
		self::$msgFile = dirname(__FILE__).'/ManageNamespaces.i18n.php';
				
		parent::__construct(	"ManageNamespaces", // special page name
								self::$spFilename,	// filename of page template
								self::$msgFile,		// filename of i18n messages
								'sysop' 			// required right
							); 
		
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