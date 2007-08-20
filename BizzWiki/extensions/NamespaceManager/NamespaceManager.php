<?php
/*<!--<wikitext>-->
{{Extension
|name        = NamespaceManager
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/NamespaceManager/ SVN]
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
Base class for 'Namespace Manager' extensions i.e. extensions that provide services under a specific namespace.

== Features ==


== Dependancy ==
None.

== Installation ==
To install independantly from BizzWiki:
* Dowload 'NamespaceManager.php' and place it in '/extensions/NamespaceManager/'
* Apply the following changes to 'LocalSettings.php':
<source lang=php>
require('extensions/NamespaceManager/NamespaceManager.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits['other'][] = array( 
	'name'    		=> 'NamespaceManager', 
	'version'		=> '$Id$',
	'author'		=> 'Jean-Lou Dupont', 
	'description'	=>  'Provides a base class for namespace manager extensions',
#	'url' 			=> StubManager::getFullUrl(__FILE__),			
);

/**
	All namespace managers should derive from this class.
 */
abstract class NamespaceManager extends Article
{
	// the namespace index in which the derived
	// class operates ... shortcut for convenience.
	var $ns;
	
	public function __construct( &$title )
	{
		parent::__construct( $title );
	}
	
	/**
		The view method will most probably need to be overriden
		Handler for the default action i.e. 'action=view'		
	 */
	public function view() 
	{
		echo __METHOD__.": must override this method.";			
	}	 
	/**
		Handler for 'action=submit'
	 */
	public function submit()
	{
		echo __METHOD__.": must override this method.";
	}	

	/**
		Handler for 'action=edit'	
	 */
	public function edit()
	{
		echo __METHOD__.": must override this method.";
	}	
	/**
		Catch-all
	 */
	public function __call()
	{
		global $wgOut;
		$wgOut->showErrorPage( 'nosuchaction', 'nosuchactiontext' );		
	}
	
} // end class declaration

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

class NamespaceManagers
{
	static $list = array();

	/**
		Only 1 handler can be registered by Namespace.
	 */		
	public static function register( $ns, $classe, $classfile )
	{
		self::$list[$ns] = array(
								'ns'	=> $ns,
								'class'	=> $classe,
								'file'	=> $classfile
							);
	}
	public static function getList()
	{
		return self::$list;
	}
	public static function setup()
	{
		global $wgExtensionFunctions;
#		$wgExtensionFunctions[] = __CLASS__.'::setup'; // PHP <v5.2.2 issues a warning on this one.
		$wgExtensionFunctions[] = create_function( '', 'return '.__CLASS__.'::init();' );
		
	}
	public static function init()
	{
		global $wgHooks;
		$wgHooks['ArticleFromTitle'][]	= 'NamespaceManagers::hArticleFromTitle';
		$wgHooks['CustomEditor'][]		= 'NamespaceManagers::hCustomEditor';
				
		global $wgAutoloadClasses;
		if (!empty( self::$list ))
			foreach( self::$list as $index => &$e )
				$wgAutoloadClasses[$e['class']] = $e['file'];
	}
	
	public static function hArticleFromTitle( &$title, &$article )
	{
		$ns = $title->getNamespace();
		// Let MW handle these ones.
		if (NS_MEDIA==$ns || NS_CATEGORY==$ns || NS_IMAGE==$ns)
			return true;
		
		// Look-up if we have a registered manager for the
		// current requested namespace.
		if (!array_key_exists( $ns, self::$nslist ))
			return true;
			
		// At this point, we have concluded we have a registered manager
		$classe = self::$list['class'];
		$article = new $classe( $title );
		$article->ns = $ns;
		
		return true;
	}
	
	/**
		We also need to trap this event as our namespace manager
		will most probably need to provide a special 'edit form'
	 */
	public function hCustomEditor( $article, $user )	
	{
		if (!( $article instanceof NamespaceManager ))
			return true;
			
		global $action;
		if ( 'submit' == $action )
			{ $article->submit(); return false; }
			
		if ( 'edit' == $action )
			{ $article->edit(); return false; }

		return true;	
	}
	
} // end class declaration

NamespaceManagers::setup();

//</source>
