<?php
/*<!--<wikitext>-->
{{Extension
|name        = SecurePHP
|status      = beta
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/SecurePHP/ SVN]
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
Provides secure execution of PHP code embedded in 'runphp' tagged section.

== Features ==
* Security: page must either
** Be protected on 'edit'
** Current user editing the page must have the 'coding' right
** Or, lastly, the last contributor to the page has the 'coding' right

== Usage ==
<nowiki><runphp> php code here </runphp></nowiki>

== Security Note ==
* It is advisable to use 'cascading protection'
* When page protection is not relied on to provide protection 
and consequently only the last contributor's right acts as protection measure, it is advised to use
considerable care when using templates on the same page.

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/SecurePHP/SecurePHP_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[SecurePHP::thisType][] = array( 
	'name'    	=> SecurePHP::thisName,
	'version' 	=> StubManager::getRevisionId('$Id$'),
	'author'  	=> 'Jean-Lou Dupont',
	'description' => "Provides secure PHP code execution", 
	'url' 		=> StubManager::getFullUrl(__FILE__),	
);

#require('SecurePHP.i18n.php');

class SecurePHP
{
	const thisType = 'other';
	const thisName = 'SecurePHP';
	
	public function __construct() 
	{}
	
	public function tag_runphp( &$code, &$params, &$parser )
	{
		if (!self::checkExecuteRight( $parser->mTitle ))
			return 'SecurePHP: '.wfMsg('badaccess');
			
		return self::executeCode( $code );
	}
	
	/**
		1- IF the page is protected for 'edit' THEN allow execution
		2- IF the page's last contributor had the 'coding' right THEN allow execution
		3- ELSE deny execution
	 */
	private static function checkExecuteRight( &$title )
	{
		if ($title->isProtected('edit'))
			return true;
		
		global $wgUser;
		if ($wgUser->isAllowed('coding'))
			return true;
		
		// Last resort; check the last contributor.
		$rev    = Revision::newFromTitle( $title );
		
		$user = User::newFromId( $rev->mUser );
		$user->load();
		
		if ($user->isAllowed( 'coding' ))
			return true;
		
		return false;
	}
	
	/**
		Actually execute the code provided.
		
		Optionally, executes a callback function is some
		arguments are passed to the function.
	 */
	private static function executeCode( &$code, &$argv = null)
	{
		# start capturing the user code's output
		ob_start();
		
		# can't pass arguments directly with 'eval'
		# must load the code in the PHP interpreter and
		# get a callback function name returned.
		// NOTE: 'eval' does not mind being passed 
		// a 'null' parameter
		$callback = eval( $code );
		
		# look for arguments.
		if ( count($argv)>0 )
			call_user_func( $callback, $argv );
		
		$output = ob_get_contents();
		
		ob_end_clean();
		
		return $output;
	}
	
} // end class
//</source>
