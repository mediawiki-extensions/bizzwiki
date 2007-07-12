<?php
/*<wikitext>
{| border=1
| <b>File</b> || SimpleReplicatorClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
== Code ==
</wikitext>*/

new SimpleReplicator;

class SimpleReplicator
{
	const actionPing = 'ping';
	
	function __construct()
	{
		global $wgHooks;
		
		$wgHooks['ArticleFromTitle'][] = array( &$this, 'hArticleFromTitle' );
		$wgHooks['UnknownAction'][] = array( &$this, 'hUnknownAction' );
	}
	
	// ArticleFromTitle Hook
	//  Provides a bare-bones Article object when a 'ping' request is detected.
	//  This enables the process flow to continue to the action handler which will
	//  dispatch the custom action 'ping'.
	public function hArticleFromTitle( &$title, &$article )
	{
		global $action;
		if ( $action == self::actionPing )
		{
			// make sure we've got a job in the queue for the
			// replication function.
			$this->primeJobQueue();
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
	# TODO

		// signal we 'handled' this action
		return false; 
	}

	private function primeJobQueue()
	{
		// create 'getPartnerRC' job.
		
		
		// insert it in the job queue
		
		
		// we are done here; let the normal process flow
		// pop the job queue in 'Wiki::doJobs()'.
		return;
	}

} // end class
?>