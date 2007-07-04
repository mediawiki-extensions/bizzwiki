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

function wfSpecialSpecialPagesManagerUpdater( $par ) 
{
	$o = SpecialPagesManagerUpdater::singleton();
	$o->execute( $par );
}

class SpecialPagesManagerUpdater extends SpecialPage
{
	static $instance = null;
	
	function SpecialPagesManagerUpdater( )
	{
		SpecialPage::SpecialPage("SpecialPagesManagerUpdater", 'siteupdate' );
		self::loadMessages();
		
		if (self::$instance === null)
			self::$instance = $this;
			
		return self::$instance;
	}
	static function singleton() 
	{ 
		if (self::$instance === null)
			self::$instance = new SpecialPagesManagerUpdater();
			
		return self::$instance;
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

	public function execute( $par )
	{
		global $wgRequest;
	}

} // end class declaration
?>