<?php
/*
 * UpdaterClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont
 * $Id$
 * 
 */

class UpdaterClass extends SpecialPage
{
	
	function UpdaterClass( )
	{
		SpecialPage::SpecialPage("Updater");
		self::loadMessages();	

		global $wgExtensionCredits;
		$wgExtensionCredits['other'][] = array( 
			'name'        => self::thisName, 
			'version'     => '$Id$',
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Updates a Mediawiki installation with http accessible files'
		);
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