<?php
/**
 * Internationalisation file for 'FetchPartnerRC' extension.
 *
 * $Id: EmailLog.i18n.php 350 2007-07-12 00:26:12Z jeanlou.dupont $
 * 
*/
// Format for global variables is:
// 'var-type'.$classname
//  where 'var-type' supported are:
//   'log', 'msg'
//
// Everything is anchored on the classname.

global $msgFetchPartnerRC;		// required for StubManager
global $logFetchPartnerRC;		// required for StubManager

// required for StubManager. The format is important:  'log'.$classname
$logFetchPartnerRC = 'ftchrclog';	//9chars max.

// the format is important here too: 'msg'.$classname
$msgEmailLog['en'] = array(
	'ftchrclog'						=> "Fetch Partner 'Recent Changes' Log",
	'ftchrclog'.'logpage'			=> "Fetch Partner 'Recent Changes' Log",
	'ftchrclog'.'logpagetext'		=> 'This is a log of ',
	'ftchrclog'.'-fetchok-entry'	=> 'Fetching successful',
	'ftchrclog'.'-fetch-text'		=> 'retrieved $1 entries',
	#'' => '',
);

?>