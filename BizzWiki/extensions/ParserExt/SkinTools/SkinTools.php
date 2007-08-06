<?php
/*<wikitext>(($disable$))
{{Extension
|name        = SkinTools
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ParserExt/SkinTools/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
== Purpose==
Provides skin level functions.

== Features ==
* (($#clearactions$))
* (($#removeactions|action1|action ...$))

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub2(	array(	'class' 		=> 'SkinTools', 
									'classfilename'	=> $IP.'/extensions/ParserExt/SkinTools/SkinTools.php',
									'hooks'			=> array( 'SkinTemplateTabs' ),
									'mgs'			=> array( 'clearactions', 'removeactions' ),
								)
						);

</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
</wikitext>*/

global $wgExtensionCredits;
$wgExtensionCredits[SkinTools::thisType][] = array( 
	'name'        => SkinTools::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Provides skin level functions',
	'url' 		=> StubManager::getFullUrl(__FILE__),						
);

class SkinTools
{
	const thisName = 'SkinTools';
	const thisType = 'other';

	var $actions;
	var $actionsToRemove;

	// Our class defines magic words: tell it to our helper class.
	public function __construct()
	{	
		$this->actions  = null;
		$this->actionsToRemove = null;
	}
	public function mg_clearactions( &$parser )
	{
		$this->actions = false;
	}
	/**
		List of actions to remove from the current page.
	 */
	public function mg_removeactions( &$parser )
	{
		$params = StubManager::processArgList( func_get_args(), true );
		foreach( $params as $actionToRemove )
			$this->actionsToRemove[] = $actionToRemove;
	}
	/**
		For modifying the 'action' toolbar on a page.
	 */
	public function hSkinTemplateTabs( &$st , &$content_actions )
	{
		// check if we are asked to remove all actions
		// from the page.
		if ( $this->actions === false )
			{ $content_actions = null; return true; }
		
		if (!empty( $this->actionsToRemove ))
			foreach( $this->actionsToRemove as $action )
				unset( $content_actions[$action] );
		
		return true;
	}
	
} // end class