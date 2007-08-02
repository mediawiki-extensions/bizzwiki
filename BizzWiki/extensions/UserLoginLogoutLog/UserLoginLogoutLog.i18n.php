<?php
/**
 * Internationalisation file for UserLoginLogoutLog extension.
 *
 * $Id$
 * 
*/
// Format for global variables is:
// 'var-type'.$classname
//  where 'var-type' supported are:
//   'log', 'msg'
//
// Everything is anchored on the classname.

global $msgUserLoginLogoutLog;		// required for StubManager
global $logUserLoginLogoutLog;		// required for StubManager

// required for StubManager. The format is important:  'log'.$classname
$logUserLoginLogoutLog = 'usrloglog';	

// the format is important here too: 'msg'.$classname
$msgUserLoginLogoutLog['en'] = array(
	'usrloglog'					=> 'User Settings Changed Log',
	'usrloglog'.'logpage'			=> 'User Settings Changed Log',
	'usrloglog'.'logpagetext'		=> "This is a log of changes to a user's settings",
	'usrloglog'.'-saveok-entry'	=> 'Settings saved ok',
	'usrloglog'.'-save-text'		=> " user name [[User:$1]]",
	#'' => '',
);

?>