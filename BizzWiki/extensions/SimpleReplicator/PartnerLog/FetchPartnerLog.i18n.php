<?php
/**
 * Internationalisation file for 'FetchPartnerLog' extension.
 *
 * $Id$
 * 
*/
global $msgFetchPartnerLog;		// required for StubManager
global $logFetchPartnerLog;		// required for StubManager
global $actFetchPartnerLog;		// required for StubManager

// required for StubManager. The format is important:  'log'.$classname
$logFetchPartnerLog = 'fetchlog';	

// required for StubManager. The format is important:  'act'.$classname
$actFetchPartnerLog = array(
							'fetchok',
							'fetchnc',
							'fetchfail',
						);

// the format is important here too: 'msg'.$classname
// NOTE: only internationalize work to be done is below here.
$msgFetchPartnerLog['en'] = array(
'fetchlog'						=> "Fetch Partner's 'Logging' table Log",
'fetchlog'.'logpage'			=> "Fetch Partner's 'Logging' table Log",
'fetchlog'.'logpagetext'		=> 'This is a log of fetch operations',
'fetchlog'.'-fetchok-entry'		=> 'Partner Log: Fetching successful',
'fetchlog'.'-fetchok-text'		=> "processed $1, filtered $2 and updated $3 entries. State=$4.",
'fetchlog'.'-fetchnc-text'		=> 'no new entry.',
'fetchlog'.'-fetchfail-entry'	=> 'Partner Log: Fetching unsuccessful',
'fetchlog'.'-fetchfail-text1'	=> 'error accessing URL.',
'fetchlog'.'-fetchfail-text2'	=> 'error parsing document. $1 $2',
#'' => '',
);

?>