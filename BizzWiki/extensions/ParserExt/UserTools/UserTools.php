<?php
/*(($disable$))<!--<wikitext>-->
{{Extension
|name        = UserTools
|status      = beta
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ParserExt/UserTools/ SVN]
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
Provides a secure magic word 'usergetoption' to retrieve user options.

== Usage ==
This extension is really meant to be used with [[Extension:ParserPhase2]].
* E.g. <code>(($#cusergetoption|email|default$))</code>
** Get the current user's email option
* E.g. <code>(($#usergetoption|user id or name|email|default$))</code>
** Get the specified user's email option IFF the current has the 'userdetails' right

== Features ==
* Options are categorized as either 'RESTRICTED' or 'UNRESTRICTED' for privacy reasons
** User must have the 'userdetails' right to access 'RESTRICTED' options

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/UserTools/UserTools_stub.php');
</source>

== History ==

== Todo ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/
$wgExtensionCredits[UserTools::thisType][] = array( 
	'name'        => UserTools::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => ' ',
	'url' 		=> StubManager::getFullUrl(__FILE__),						
);
class UserTools
{
	const thisName = 'UserTools';
	const thisType = 'other';

	const RESTRICTED   = 0;
	const UNRESTRICTED = 1;

	static $options = array(
								// require special treatment
								'email'			=> self::RESTRICTED,
								'realname'		=> self::RESTRICTED,
								'authtimestamp'	=> self::RESTRICTED,
								'datepref'		=> self::UNRESTRICTED,

								// can be retrieved through 'User::getOption'
								'language'		=> self::UNRESTRICTED,
								'variant'		=> self::UNRESTRICTED,
								'disablemail'	=> self::UNRESTRICTED,
								'nickname'		=> self::UNRESTRICTED,
								'quickbar'		=> self::UNRESTRICTED,
								'skin'			=> self::UNRESTRICTED,																
								'math'			=> self::UNRESTRICTED,
								'rows'			=> self::UNRESTRICTED,
								'cols'			=> self::UNRESTRICTED,								
								'stubthreshold'	=> self::UNRESTRICTED,																
								'timecorrection'=> self::UNRESTRICTED,
								'searchlimit'	=> self::UNRESTRICTED,
								'contextlines'	=> self::UNRESTRICTED,
								'contextchars'	=> self::UNRESTRICTED,																								
								'imagesize'		=> self::UNRESTRICTED,																								
								'thumbsize'		=> self::UNRESTRICTED,
								'rclimit'		=> self::UNRESTRICTED,
								'rcdays'		=> self::UNRESTRICTED,
								'wllimit'		=> self::UNRESTRICTED,
								'underline'		=> self::UNRESTRICTED,
								'watchlistdays'	=> self::UNRESTRICTED,
							);

	public function __construct() {}
	
	/**
	
	 */
	public function mg_cusergetoption( &$parser, $whichOption, $default = null )
	{
		global $wgUser;
		
		// if the option is marked 'restricted', make sure
		// the current user has the right to access the requested 'option'
		if ($this->isRestricted( $option ))
			if (!$wgUser->isAllowed('userdetails'))	
				return null;

		return $this->getOption( $wgUser, $whichOption, $default );
	}
	/**
	 */
	public function mg_usergetoption( &$parser, $user, $whichOption, $default = null )
	{
		global $wgUser;
		
		// if the option is marked 'restricted', make sure
		// the current user has the right to access the requested 'option'
		if ($this->isRestricted( $option ))
			if (!$wgUser->isAllowed('userdetails'))	
				return null;

		if (is_numeric( $user ))
		{
			$userObj = User::newFromId( $user );
			if (!is_object( $userObj ))
				return null;
			if ($userObj->getID() == 0)
				$userObj = User::newFromName( $user, true /* validate */);
		}
		else
			$userObj = User::newFromName( $user, true /* validate */);

		if (!is_object( $userObj ))
			return null;
		
		return $this->getOption( $userObj, $whichOption, $default );
	}

	/**
		Returns 'true' (restricted) if the option is not found.
	 */
	private function isRestricted( &$option )
	{
		if (isset( self::$options[ $option ] ))
			$r = self::$options[ $option ];
		else
			return true;
			
		return ($r == self::RESTRICTED) ? true:false;
	}
	public function getOption( &$user, &$option, $default = null )
	{
		switch( $option )
		{
			case 'email':
				return $user->getEmail();
			case 'realname':
				return $user->getRealName();
			case 'authtimestamp':
				return $user->getEmailAuthenticationtimestamp();
			case 'datepref':
				return $user->getDatePreference();
			default:
				return $user->getOption( $option, $default );
		}

		return null; // calms PHP			
	}
	
} // end class
//</source>