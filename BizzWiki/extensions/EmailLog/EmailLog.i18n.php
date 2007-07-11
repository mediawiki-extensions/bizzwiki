<?php
/**
 * Internationalisation file for EmailLog extension.
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

global $msgEmailLog;		// required for StubManager
global $logEmailLog;		// required for StubManager

// required for StubManager. The format is important:  'log'.$classname
$logEmailLog = 'emaillog';	

// the format is important here too: 'msg'.$classname
$msgEmailLog['en'] = array(
	'emaillog'					=> 'Email Log',
	'emailloglogpage'			=> 'Email Log',
	'emailloglogpagetext'		=> 'This is a log of <i>user-to-user emails</i> sent',
	'emaillog-sentok-entry'		=> 'Email successfully sent',
	'emaillog-sent-text'		=> 'from user $1 to user $2',
#	'emailloglogentry'              => '',
	#'' => '',
);

?>