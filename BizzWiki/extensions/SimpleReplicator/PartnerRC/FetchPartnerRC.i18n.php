<?php
/**
 * Internationalisation file for 'FetchPartnerRC' extension.
 *
 * $Id$
 * 
*/
global $msgFetchPartnerRC;		// required for StubManager
global $logFetchPartnerRC;		// required for StubManager

// required for StubManager. The format is important:  'log'.$classname
$logFetchPartnerRC = 'ftchrclog';	

// the format is important here too: 'msg'.$classname
$msgFetchPartnerRC['en'] = array(
'ftchrclog'						=> "Fetch Partner's 'Recent Changes' Log",
'ftchrclog'.'logpage'			=> "Fetch Partner's 'Recent Changes' Log",
'ftchrclog'.'logpagetext'		=> 'This is a log of fetch operations',
'ftchrclog'.'-fetchok-entry'	=> 'Fetching successful',
'ftchrclog'.'-fetchok-text'		=> "processed $1, filtered $2 and updated $3 entries. State=$4.",
'ftchrclog'.'-fetchnc-text'		=> 'no new entry.',
'ftchrclog'.'-fetchfail-entry'	=> 'Fetching unsuccessful',
'ftchrclog'.'-fetchfail-text1'	=> 'error accessing URL.',
'ftchrclog'.'-fetchfail-text2'	=> "error parsing document. $1 $2",
#'' => '',
);

?>