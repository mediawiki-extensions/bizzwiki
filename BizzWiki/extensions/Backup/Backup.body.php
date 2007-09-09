<?php
// <source lang=php>

$wgExtensionCredits[backup::thisType][] = array( 
	'name'    => backup::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => "Provides the 'backup' hook.", 
);

class backup
{
	const thisType = 'other';
	const thisName = 'backup';
	
	//
	var $rc;
	var $op; // current operation
	var $executeDeferredInRcHook;
		
	/**
	 */
	public function __construct() 
	{
		$this->op = null;

		$this->executeDeferredInRcHook = false;
	}
	
	/**
		Handles article creation & update
		
		Creation and Update operations can not be discerned;
		they are handled both as 'edit'.
	 */	
	public function hArticleSaveComplete( &$article, &$user, &$text, &$summary, $minor, 
											$dontcare1, $dontcare2, &$flags )
	{
		$this->op = new backup_operation(backup_operation::action_edit,
										$article,
										true,	// include last revision text
										$this->rc->mAttribs['rc_id'],
										$this->rc->mAttribs['rc_timestamp']											
									 );
									 
		$this->doNotify();
		
		return true;
	}

	/**
		WARNING: If ArticleDelete hook fails, we might have some stranded resources
		e.g. temporary file
	 */
	public function hArticleDelete( &$article, &$user, $reason )
	{
		$this->op = new backup_operation(backup_operation::action_delete,
										$article,
										false,	// don't include last revision text
										null,	// we don't know the id just yet
										null	// nor the timestamp
								 		);
		// we can't complete the execution in this hook. 
		// We are missing some parameters.
		// $this->doNotify();
		
		return true;
	}
	/**
		Handles article delete.
	 */
	public function hArticleDeleteComplete( &$article, &$user, $reason )
	{
		$this->op->setIdTs(	$this->rc->mAttribs['rc_id'], 
							$this->rc->mAttribs['rc_timestamp'] );
		
		$this->doNotify();
		return true;
	}
	
	/**
		Handles article move.
		
		This hook is often called twice:
		- Once for the page
		- Once for the 'talk' page corresponding to 'page'
	 */
	public function hSpecialMovepageAfterMove( &$sp, &$oldTitle, &$newTitle )
	{
		$this->op = new backup_operation(backup_operation::action_move,
										$newTitle,
										true,	// include last revision text
										$this->rc->mAttribs['rc_id'],
										$this->rc->mAttribs['rc_timestamp']											
									 );
									 
		$this->op->setSourceTitle( $oldTitle );
		
		$this->doNotify();
		
		return true;		
	}
	
	/**
		File Upload	
	 */
	public function hFileUpload( &$img )
	{
		$this->op = new backup_operation(backup_operation::action_createfile,
										$article,
										false,	// do not include last revision text
										null,
										null										
									 );
		
		// We are missing some parameters that will only be available
		// when then 'RecentChange' event is triggered.
		$this->executeDeferredInRcHook = true;		
		
		return true;		
	}
	
	/**
	 */
	#public function hUploadComplete( &$img ) {	return true;	}
	
	/**
		TBD
	 */
	public function hAddNewAccount( &$user )
	{
	
		return true;		
	}
	
	/**
		Just send the 'page' details which contain the 'restrictions'
		aka 'protection' information.	
	 */
	public function hArticleProtectComplete( &$article, &$user, &$limit, &$reason )
	{
		$this->op = new backup_operation(backup_operation::action_protect,
										$article,
										false,	// do not include last revision text
										null,
										null										
									 );
									 
		$this->executeDeferredInRcHook = true;		
		
		return true;
	}

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%	
	
	/**
		Just grab the essential parameters we need to complete the transaction.
	 */
	public function hRecentChange_save( &$rc )
	{
		$this->rc = $rc;
			
		if ($this->executeDeferredInRcHook)
		{
			$this->op->setIdTs(	$this->rc->mAttribs['rc_id'], 
								$this->rc->mAttribs['rc_timestamp'] );
			
			$this->doNotify();
		}

		return true;		
	}

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%	

	/**
	 */
	public function doNotify()
	{
		wfRunHooks( 'backup', array( &$this->op ) );		
	}
	
} // end class

/**		************************************************************
		Follows is a class that defines an 'backup' export operation.
 */

class backup_operation
{
		// Constants
	const action_none       = 0;
	const action_create     = 1; // TBD
	
		// page related
	const action_edit       = 2;
	const action_delete     = 3;
	const action_move       = 4;
	const action_protect    = 5;
		
		// file related
	const action_createfile = 6;
	const action_deletefile = 7;
	
	// Commit Operation parameters
	var $includeRevision;
	var $deferralRequired;
	
	var $id;
	var $timestamp;
	
	var $action;
	var $ns;
	var $titre;

	var $sourceTitle;	// for move action

	var $history;		// current or full
	
	var $text;
	
	public function __construct( $action, &$object, $includeRevision, $id, $ts )
	{
		if ( $object instanceof Article )
			$title = $object->mTitle;
		else
			$title = $object;

		$this->ns = $title->getNamespace();
		$this->titre = $title->getText();

		$this->action = $action;
		$this->includeRevision = $includeRevision;
		$this->deferralRequired = $this->getDeferralState( );

		$this->id = $id;
		$this->timestamp = $ts;

		$this->sourceTitle = null;
	}
	public function setIdTs( $id, $ts ) { $this->id = $id; $this->timestamp = $ts; }
	public function setSourceTitle( &$t ) { $this->sourceTitle = $t; }
	
	/**
		Delete action requires deferral
	 */
	public function getDeferralState( )
	{
		if ($this->action == self::action_delete)	
			return true;

		if ($this->action == self::action_protect)	
			return true;
			
		return false;
	}
	
} // end class

//</source>
