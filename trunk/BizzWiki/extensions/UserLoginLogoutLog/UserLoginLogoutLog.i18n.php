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
	'usrloglog'						=> 'User Log-in/Log-out Log',
	'usrloglog'.'logpage'			=> 'User Log-in/Log-out Log',
	'usrloglog'.'logpagetext'		=> "This is a log of user log-in/log-out events",
	'usrloglog'.'-loginok-entry'	=> 'Log-in successful',
	'usrloglog'.'-loginok-text'		=> "[[User:$1]]",
	'usrloglog'.'-loginerr-entry'	=> 'Log-in unsuccessful',
	'usrloglog'.'-loginerr-text'	=> " $1 message: $2 ",
	'usrloglog'.'-logoutok-entry'	=> 'Log-out successful',
	'usrloglog'.'-logoutok-text'	=> "[[User:$1]]",
	
	#'' => '',
);

?>