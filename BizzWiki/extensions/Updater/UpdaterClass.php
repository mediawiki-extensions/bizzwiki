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
	const thisName = 'Updater';
	
	function Updater( )
	{
		SpecialPage::SpecialPage("Updater");
		self::loadMessages();	

	}

	function execute( $par ) 
	{
		global $wgRequest, $wgOut;
                
                $this->setHeaders();

                # Get request data from, e.g.
                $param = $wgRequest->getText('param');
                
        }

		function loadMessages() 
		{
			static $messagesLoaded = false;
			global $wgMessageCache;
			if ( $messagesLoaded ) return;
			$messagesLoaded = true;
			
			require( dirname( __FILE__ ) . '/Updater.i18n.php' );
#			global $wgUpdaterMessages;
			foreach ( $wgUpdaterMessages as $lang => $langMessages ) 
			        $wgMessageCache->addMessages( $langMessages, $lang );

        }

} // END CLASS DEFINITION
?>