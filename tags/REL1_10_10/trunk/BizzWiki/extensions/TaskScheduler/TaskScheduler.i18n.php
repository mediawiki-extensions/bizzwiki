<?php
/*<wikitext>
{| border=1
| <b>File</b> || TaskScheduler.i18n.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
</wikitext>*/

// Format for global variables is:
// 'var-type'.$classname
//  where 'var-type' supported are:
//   'log', 'msg'
//
// Everything is anchored on the classname.

global $msgTaskScheduler;		// required for StubManager
global $logTaskScheduler;		// required for StubManager
global $actTaskScheduler;		// required for StubManager

// required for StubManager. The format is important:  'log'.$classname
$logTaskScheduler = 'schlog';	

// the format is important here too: 'msg'.$classname
$msgTaskScheduler['en'] = array(
	'schlog'					=> 'Task Scheduler Log',
	'schlog'.'logpage'			=> 'Task Scheduler Log',
	'schlog'.'logpagetext'		=> 'This is a log of events for the Task Scheduler',
	'schlog'.'-runok-entry'		=> 'Task Scheduler: Success',
	'schlog'.'-runok-text1'		=> 'task class {$1} returned code {$2}.',

	'schlog'.'-runfail-entry'	=> 'Task Scheduler: Failure',	
	'schlog'.'-runfail-text1'	=> 'inexistant task class {$1}.',	
	'schlog'.'-runfail-text2'	=> 'error executing task class {$1}.',		

	'schlog'.'-start-entry'		=> 'Task Scheduler: Starting',
	'schlog'.'-start-text'		=> 'task class {$1}.',
	#'' => '',
);

$actTaskScheduler= array(
							'runok',
							'runfail',
							'start',
						);
?>