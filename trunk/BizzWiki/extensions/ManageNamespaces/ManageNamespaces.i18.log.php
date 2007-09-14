<?php
/*
<!--<wikitext>-->
This file only contains the messages pertaining to the log functionality of the extension.

 <file>
  <name>ManageNamespaces.i18.log.php</name>
  <version>$Id$</version>
  <package>Extension.ManageNamespaces</package>
 </file>
<!--</wikitext>-->
*/
// <source lang=php>

class ManageNamespacesLogMessages
{
	public static function init()
	{
		static $loaded = false;
		if ($loaded) return;
		$loaded = true;

		global $wgExtensionFunctions;
		$wgExtensionFunctions[] = create_function('','return ManageNamespacesLogMessages::loadMessages();');
	}
	public static function loadMessages()
	{
		global $wgMessageCache;
		foreach ( self::$msg as $lang => $langMessages )
			$wgMessageCache->addMessages( $langMessages, $lang );
			
		self::setupLogging();
	}
	private static function setupLogging( )
	{
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;

		$wgLogTypes  []     		= self::$log;
		$wgLogNames  [self::$log]	= self::$log.'logpage';
		$wgLogHeaders[self::$log]	= self::$log.'logpagetext';

		foreach( self::$actions as $action )
			$wgLogActions[self::$log.'/'.$action] = self::$log.'-'.$action.'-entry'; 
	}

static $log = 'mngns';
static $actions = array( '', '' );

static $msg = array(
'en' => array(
'' => '',
'' => '',
),
// add more translations here
#'fr' => array(
#'' => '',
#'' => '',
#);
);

} // end class

// Initialization -- do not touch.
ManageNamespacesLogMessages::init();
//</source>