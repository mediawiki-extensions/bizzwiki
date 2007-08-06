<?php
/*<wikitext>(($disable$))
{{Extension
|name        = SkinTools
|status      = beta
|type        = parser
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
* Clear all actions from the page: (($#clearactions$))
* Remove a list of actions from the page: (($#removeactions|action1|action ...$))
* Add an action on the current page: (($#addaction|action|action text$))

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension (make sure to have the latest version)
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub2(	array(	'class' 		=> 'SkinTools', 
									'classfilename'	=> $IP.'/extensions/ParserExt/SkinTools/SkinTools.php',
									'hooks'			=> array( 'SkinTemplateTabs' ),
									'mgs'			=> array( 'clearactions', 'removeactions', 'addaction' ),
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
	var $actionsToAdd;

	// Our class defines magic words: tell it to our helper class.
	public function __construct()
	{	
		$this->actions  = null;
		$this->actionsToRemove = null;
		$this->actionsToAdd = null;		
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
		if (isset( $params ))
			foreach( $params as $actionToRemove )
				$this->actionsToRemove[] = $actionToRemove;
	}

	public function mg_addaction( &$parser, $action, $actionText )
	{
		$params = StubManager::processArgList( func_get_args(), true );		
		if (empty( $action ) || empty( $actionText))
			return 'SkinTools: invalid parameters';

		$this->actionsToAdd[] = array( 'action' => $action, 'actionText' => $actionText );
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

		if (!empty( $this->actionsToAdd ))
			foreach( $this->actionsToAdd as $actionDetails )
				$content_actions[ $actionDetails['action'] ] = array(
					'text' => $actionDetails['actionText'],
					'href' => $st->mTitle->getLocalUrl( 'action='.$actionDetails['action'] )
				);
		
		return true;
	}
	
} // end class