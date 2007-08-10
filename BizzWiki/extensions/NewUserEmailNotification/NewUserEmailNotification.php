<?php
/*<!--<wikitext>-->
{{Extension
|name        = NewUserEmailNotification
|status      = beta
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/NewUserEmailNotification/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
== Purpose==
Provides email notification of new user account creation.

== Features ==
* Uses '$wgSitename' as 'to' contact name
* Uses '$wgEmergencyContact' as 'to' contact address

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/NewUserEmailNotification/NewUserEmailNotification_stub.php');
</source>

== History ==
* Fixed incorrect path to 'UserMailer'

== TODO ==
* add more error checking

== See Also ==
This extension is part of [[Extension:BizzWiki|BizzWiki platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/
$wgExtensionCredits[NewUserEmailNotification::thisType][] = array( 
	'name'    		=> NewUserEmailNotification::thisName,
	'version' 		=> StubManager::getRevisionId('$Id$'),
	'author'  		=> 'Jean-Lou Dupont',
	'description'	=> 'Provides email notification of new user account creation', 
	'url'			=> StubManager::getFullUrl(__FILE__),				
);
require_once( 'NewUserEmailNotification.i18n.php');
		
class NewUserEmailNotification
{
	const thisType = 'hook';
	const thisName = 'NewUserEmailNotification';
	
	public function __construct()
	{
		global $wgMessageCache;
		global $IP;
		
		require_once( $IP.'/includes/UserMailer.php' );

		$msg = $GLOBALS[ 'msg'.__CLASS__ ];
		
		foreach( $msg as $key => $value )
			$wgMessageCache->addMessages( $msg[$key], $key );		
	}
	public function hAddNewAccount( &$user )
	{
		// Compatibility with old versions which didn't pass the parameter		
		global $wgUser;		
		if( is_null( $user ) )
			$user = $wgUser;

		// hopefully, this global is set!
		global $wgEmergencyContact;
		if (!isset($wgEmergencyContact))
			return true;

		// Use the site name as 'name'
		global $wgSitename;

		$this->sendMail( $user, $wgEmergencyContact, $wgSitename );
		
		return true;
	}

	private function sendMail( $from_user, $to_address, $to_name ) 
	{
		global $wgSitename;
		
		$subject = wfMsg('newuseremailnotification-subject', $wgSitename);
		$body    = wfMsg('newuseremailnotification-body', $wgSitename, $from_user->mName, $from_user->mRealName   );
		
		$to = 		new MailAddress( $to_address, $to_name );
		$sender =	new MailAddress( $from_user );
		$error =	userMailer( $to, $sender, $subject, $body );
	}

} // end class definition.
//</source>