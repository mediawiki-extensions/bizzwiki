<?php
/**
 * Internationalisation file for 'FetchPartnerUser' extension.
 *
 * $Id$
 * 
*/
global $msgFetchPartnerUser;		// required for StubManager
global $logFetchPartnerUser;		// required for StubManager
global $actFetchPartnerUser;		// required for StubManager

// required for StubManager. The format is important:  'log'.$classname
$logFetchPartnerUser = 'fetchuser';	

// required for StubManager. The format is important:  'act'.$classname
$actFetchPartnerUser = array(
							'fetchok',
							'fetchnc',
							'fetchfail',
						);

// the format is important here too: 'msg'.$classname
// NOTE: only internationalize work to be done is below here.
$msgFetchPartneruser['en'] = array(
'fetchuser'						=> "Fetch Partner's 'User Table' Log",
'fetchuser'.'logpage'			=> "Fetch Partner's 'User Table' Log",
'fetchuser'.'logpagetext'		=> 'This is a log of fetch operations',
'fetchuser'.'-fetchok-entry'	=> 'Partner User: Fetching successful',
'fetchuser'.'-fetchok-text'		=> "processed $1, filtered $2 and updated $3 entries. State=$4.",
'fetchuser'.'-fetchnc-entry'	=> 'Partner User: Fetching successful',
'fetchuser'.'-fetchnc-text'		=> 'no new entry.',
'fetchuser'.'-fetchfail-entry'	=> 'Partner User: Fetching unsuccessful',
'fetchuser'.'-fetchfail-text1'	=> 'error accessing URL.',
'fetchuser'.'-fetchfail-text2'	=> "error parsing document. $1 $2",
#'' => '',
);
?>