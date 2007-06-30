<?php
/*
 * UpdaterClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont
 * $Id$
 * 
 */

class Updater extends SpecialPage
{
	function Updater( )
	{
		SpecialPage::SpecialPage("Updater");
		self::loadMessages();	
	}

	function getTitle()
	// Overriding this function gets our page listed under Special:Specialpages
	// even though the actual page resides in a different namespace.
	{ 
		$ns = Namespace::getCanonicalName( NS_BIZZWIKI );
		return Title::newFromText( $ns.':Updater' ); 
	}

	function loadMessages() 
	{
		static $messagesLoaded = false;
		global $wgMessageCache;
		if ( $messagesLoaded ) return;
		$messagesLoaded = true;
		
		require( dirname( __FILE__ ) . '/Updater.i18n.php' );

		foreach ( $wgUpdaterMessages as $lang => $langMessages ) 
		        $wgMessageCache->addMessages( $langMessages, $lang );
	}

} // END CLASS DEFINITION
?>