<?php
/*<wikitext>
{| border=1
| <b>File</b> || SpecialPagesManagerUpdater.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/

class SpecialPagesManagerUpdater extends SpecialPage
{
	function SpecialPagesManagerUpdater( )
	{
		SpecialPage::SpecialPage("SpecialPagesManagerUpdater", 'siteupdate' );
		self::loadMessages();
	}
	function loadMessages()
	{
		static $messagesLoaded = false;
		if ( $messagesLoaded ) return;
		$messagesLoaded = true;

		global $wgSpecialPagesManagerUpdaterMessages, $wgMessageCache;

		require( dirname( __FILE__ ) . '/SpecialPagesManagerUpdater.i18n.php' );

		foreach ( $wgSpecialPagesManagerUpdaterMessages as $lang => $langMessages ) 
		        $wgMessageCache->addMessages( $langMessages, $lang );	
	}
}

?>