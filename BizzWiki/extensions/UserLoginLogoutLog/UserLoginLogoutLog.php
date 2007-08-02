<?php
/*<wikitext>
{{Extension
|name        = UserLoginLogoutLog
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/UserLoginLogoutLog/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}

== Purpose==
Provides logging of user login/logout activities.

== Dependancy ==
* [[Extension:StubManager]] Extension

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Download this extension's file(s) and place them in the extension's directory
* Download patched '/includes/User.php' file from BizzWiki distribution and put in '$IP./includes' directory
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
StubManager::createStub(	'UserLoginLogoutLog', 
							$IP.'/extensions/UserLoginLogoutLog/UserLoginLogoutLog.php',
							$IP.'/extensions/UserLoginLogoutLog/UserLoginLogoutLog.i18n.php',							
							array(	'UserLoginForm', 'UserLoginComplete', 
									'UserLogout', 'UserLogoutComplete' ),
							true
						 );
</source>

== History ==

== Code ==
</wikitext>*/
$wgExtensionCredits[UserLoginLogoutLog::thisType][] = array( 
	'name'    		=> UserLoginLogoutLog::thisName,
	'version' 		=> StubManager::getRevisionId('$Id$'),
	'author'  		=> 'Jean-Lou Dupont',
	'description'	=> 'Provides logging of user login/logout activities', 
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);
require_once('UserLoginLogoutLog.i18n.php');

class UserLoginLogoutLog
{
	const thisType = 'other';
	const thisName = 'UserLoginLogoutLog';
	
	public function __construct()
	{
		# Add a new log type
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		$wgLogTypes[]							= 'usrloglog';
		$wgLogNames  ['usrloglog']				= 'usrloglog'.'logpage';
		$wgLogHeaders['usrloglog']				= 'usrloglog'.'logpagetext';
		$wgLogActions['usrloglog/usetchglog']	= 'usrloglog'.'logentry';
		$wgLogActions['usrloglog/saveok']     	= 'usrloglog'.'-saveok-entry';
		
		global $wgMessageCache;

		$msg = $GLOBALS[ 'msg'.__CLASS__ ];
		
		foreach( $msg as $key => $value )
			$wgMessageCache->addMessages( $msg[$key], $key );		
	}

	public function hUserLoginForm( &$tmpl )
	{}

	public function hUserLoginComplete( &$user )
	{}

	public function hUserLogout( &$user )
	{}

	public function hUserLogoutComplete( &$user )
	{}

}
?>