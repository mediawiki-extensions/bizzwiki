<?php
/*<wikitext>
{| border=1
| <b>File</b> || ClockTick.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension handles the reception of a 'ping' action. In turn, the extension triggers the 'ClockTickEvent' hook.
The basic purpose of this extension is to provide a regular time-base; this can be useful for scheduling regular jobs
e.g. replication.

== Features ==
* Only responds to 'localhost' requests (security measure).

== Dependancy ==

== Installation ==
To install independantly from BizzWiki:
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ClockTick/ClockTick.php');
</source>

== History ==

== Code ==
</wikitext>*/

class ClockTick
{
	const actionPing = 'ping';
	
	// timebase in 'seconds'
	static $timebase = 60;
	
	function __construct()
	{
		global $wgHooks;
		
		$wgHooks['ArticleFromTitle'][]	= array( &$this, 'hArticleFromTitle' );
		$wgHooks['UnknownAction'][]		= array( &$this, 'hUnknownAction' );
	}
	
	// ArticleFromTitle Hook
	//  Provides a bare-bones Article object when a 'ping' request is detected.
	//  This enables the process flow to continue to the action handler which will
	//  dispatch the custom action 'ping'.
	public function hArticleFromTitle( &$title, &$article )
	{
		global $action;
		if ( ($action == self::actionPing) && 
				($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
			)
		{
			// make sure we've got a job in the queue for the
			// replication function.
			wfRunHooks('ClockTickEvent', self::$timebase );
			
			// build a stub Article to ensure MW does not complain
			$article = new Article( $title );
			return false; // stop the hook chain.
		}
		
		// continue the hook-chain.
		return true;
	}
	
	// UnknownAction Hook
	//  Provides a 'pass-through' in the process flow for performing 'jobs' from the queue.
	//  This allows the Replicator to perform its duties on a regular basis.
	public function hUnknownAction( $action, $article )
	{
		// make sure the request is targeted at our function
		if ($action != self::actionPing )
			return true;  // continue the hook chain.

		// make sure we do not waste too much resource for nothing.
		global $wgOut;
		$wgOut->disable();

		// Send our own skinny response
		global $wgInputEncoding;
		
		header( "Content-type: 'text/x-wiki'; charset=".$wgInputEncoding );
		header( "Cache-Control: private, must-revalidate, max-age=0" );
		
		// signal we 'handled' this action
		return false; 
	}

} // end class

// references to this object are kept in 'wgHooks';
// the garbage collector won't clean this object before
// the end of the transaction.
new ClockTick;
?>