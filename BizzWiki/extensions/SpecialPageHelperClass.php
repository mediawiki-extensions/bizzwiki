<?php
/*<!--<wikitext>-->
{{Extension
|name        = SpecialPageHelperClass
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose==


== Features ==


== Dependancy ==
None.

== Installation ==
To install independantly from BizzWiki:
* Download the single file <code>SpecialPageHelperClass.php</code> from the SVN repository or 
* Copy file to the desired directory e.g. '/extensions'
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/SpecialPageHelperClass.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits['other'][] = array( 
	'name'    	=> 'SpecialPageHelperClass',
	'version' 	=> '$Id$',
	'author'  	=> 'Jean-Lou Dupont',
	'description' => "Provides an helper subclass for special pages.", 
	'url' 		=> 'http://mediawiki.org/wiki/Extension:SpecialPageHelperClass',
);

class SpecialPageHelperClass extends SpecialPage
{
	var $page;
	var $right;
	var $submit;
	var $filename;
	
	public function __construct( $t, &$filename, $right = null ) 
	{
		parent::__construct( $t );
		
		// inits.
		$this->page = null;
		$this->filename = $filename;
		$this->submit = false;
		$this->right = $right;
		
		$this->init();
	}
	protected function init()
	{
		global $wgMessageCache;
		if (!empty( self::$msg ))
			foreach ( self::$msg as $lang => $langMessages )
				$wgMessageCache->addMessages( $langMessages, $lang );
	}
	public function execute()
	{
		global $wgUser, $wgRequest;
		
		//do permission checks first.
		if ( $wgUser->isAnon() or $wgUser->isBlocked() ) 
		{
			$wgOut->errorpage( "movenologin", "movenologintext" );
			return;
		}

		if ( !$wgUser->isAllowed( $this->right ) ) 
		{
			$this->displayRestrictionError();
			return;
		}

		$this->setHeaders();

		$this->submit = $wgRequest->wasPosted() &&
						$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );
						
		// switch based on request type.
		if ($this->submit)
			$this->doSubmit();
		else
		{
			$page = $this->loadPage( $this->filename );
			$this->doShow( '', $page );
		}
	}
	
	protected function loadPage( &$filename )
	{
		return @file_get_contents( $filename );
	}
	
} // end class
//</source>
