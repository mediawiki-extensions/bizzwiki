<?php
/**
 * Internationalisation file for WatchLog extension.
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

global $msgWatchLog;		// required for StubManager
global $logWatchLog;		// required for StubManager

// required for StubManager. The format is important:  'log'.$classname
$logWatchLog = 'watchlog';	

// the format is important here too: 'msg'.$classname
$msgWatchLog['en'] = array(
	'watchlog'						=> 'Watch Log',
	'watchlog'.'logpage'			=> 'Watch Log',
	'watchlog'.'logpagetext'		=> 'This is a log of pages watched/unwatched',
	'watchlog'.'-watchok-entry'		=> 'Page successfully added to watch list',
	'watchlog'.'-unwatchok-entry'	=> 'Page successfully removed from watch list',	
	'watchlog'.'-watch-text'		=> '[[$1]]',
	'watchlog'.'-unwatch-text'		=> '[[$1]]',	
	#'' => '',
);

?>