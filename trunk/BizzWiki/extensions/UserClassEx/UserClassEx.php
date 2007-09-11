<?php
/*
<!--<wikitext>-->
 <file>
  <name>UserClassEx.body.php</name>
  <version>$Id$</version>
  <package>Extension.UserClassEx</package>
 </file>
<!--</wikitext>-->
*/
// <source lang=php>

$wgExtensionFunctions[] = create_function('','return UserClassEx::setup();');

require($IP.'/includes/Exception.php');
require($IP.'/includes/User.php');

class UserClassEx extends User
{
	const thisType = 'other';
	const thisName = 'UserClassEx';

	/**
		isAllowed
	 */
	function isAllowed($action='', $ns = null /* BizzWiki addition */, $title = null /* BizzWiki addition */ ) 
	{
		echo __METHOD__.'---';
		if ( $action === '' )
			// In the spirit of DWIM
			return true;

		$result = null;
		wfRunHooks('UserIsAllowed', array( &$this, $ns, $title, &$action, &$result ) );
		if ( $result !== null )
			return $result;

		return in_array( $action, $this->getRights() );
	}
	/**
		SaveSettings
	 */
	function saveSettings()
	{
		echo __METHOD__.'---';
		parent::saveSettings();
		wfRunHooks( 'UserSettingsChanged', array( &$this ) );			
	}
		 
	/**
		Setup method called during extension initialization phase.
	 */
	public static function Setup()
	{
		global $wgExtensionCredits;
		$wgExtensionCredits[UserClassEx::thisType][] = array( 
			'name'    		=> UserClassEx::thisName,
			'version'		=> UserClassEx::getRevisionId('$Id$'),
			'author'		=> 'Jean-Lou Dupont',
			'url'			=> 'http://www.mediawiki.org/wiki/Extension:UserClassEx',	
			'description' 	=> "Enhances MediaWiki with the hooks 'UserIsAllowed' and 'UserSaveSettings'.", 
		);
		
		// replace the global wgUser variable
		global $wgUser;
		$wgUser = new UserClassEx;
	}
	/**
		__construct
	 */
	public function __construct() { return parent::__construct(); }

	/**
		getRevisionId
	 */
	static function getRevisionId( $svnId=null )
	{	
		// fixed annoying warning about undefined offset.
		if ( $svnId === null || $svnId == ('$'.'Id'.'$' /* fool SVN */) )
			return null;
			
		// e.g. $Id$
		$data = explode( ' ', $svnId );
		return $data[2];
	}
	
} // end class

//</source>
