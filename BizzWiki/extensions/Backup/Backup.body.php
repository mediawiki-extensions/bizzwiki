<?php
/*
<!--<wikitext>-->
 <file>
  <name>Backup.body.php</name>
  <version>$Id$</version>
  <package>Backup</package>
 </file>
<!--</wikitext>-->
*/
// <source lang=php>

$wgExtensionCredits[Backup::thisType][] = array( 
	'name'    => Backup::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'url'		=> 'http://www.mediawiki.org/wiki/Extension:Backup',	
	'description' => "Provides the 'backup' hook.", 
);

class Backup
{
	const thisType = 'other';
	const thisName = 'Backup';
	
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
		// complete the 'op' object with the missing data.
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
	/**
	
	 */
	public function hImageDoDeleteBegin( &$img_page )
	{
		$this->op = new backup_operation(backup_operation::action_imagedelete,
										$img_page,
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
		
		// Log entry case: useful for the following events:
		// - general log entry
		// - delete event (image / file)
		if ($this->rc->mAttribs['rc_type'] == RC_LOG /*defined in Defines.php*/)
		{
			$this->op = new backup_operation(backup_operation::action_log,
											$rc
											);
			
			$this->doNotify();
			
			// nothing else todo
			return true;
		}
		
		/*
			Used in the following cases:
			- FileUpload
			- Article Protect
			- Image delete
		 */
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
		This method finally fires the 'backup' event.
	 */
	public function doNotify()
	{
		wfRunHooks( 'backup', array( &$this->op ) );		
	}
	
} // end class

/**		******************************************************
		Follows is a class that defines an 'backup' operation.
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
	
		// log related
	const action_log		= 8;
	
		// image related
	const action_imagedelete = 9;
	
	static $deferred = array( action_protect, action_imagedelete, action_delete );
	
	// Commit Operation parameters
	var $includeRevision;
	var $deferralRequired;
	
	var $id;
	var $timestamp;
	
	var $action;
	var $ns;
	var $titre;
	var $rev_id;

	var $sourceTitle;	// for move action

	var $history;		// current or full
	
	var $text;
	
	public function __construct( $action, &$object, $includeRevision = false, $id=null, $ts=null )
	{
		$this->rev_id = null;
		
		$this->getNsTitleRevision( $object, $this->ns, $this->titre, $this->rev_id );
	
		$this->action = $action;
		$this->includeRevision = $includeRevision;
		$this->deferralRequired = $this->getDeferralState( );

		if ( $object instanceof RecentChange )
		{
			$this->id = $object->mAttribs['rc_id'];
			$this->timestamp = $object->mAttribs['rc_timestamp'];
		}
		else
		{
			$this->id = $id;
			$this->timestamp = $ts;
		}
		
		// for page move.
		$this->sourceTitle = null;
	}
	private function getNsTitleRevision( &$object, &$ns, &$titre, &$rev )
	{
		if ( $object instanceof RecentChange )
		{
			$ns = $object->mAttribs['rc_namespace'];
			$title = $object->mAttribs['rc_title'];	
			return true;
		}
		
		if ( $object instanceof Article )
		{
			$title = $object->mTitle;
			$rev = $object->mRevision;
		}
			
		// cases: page move
		if ( $object instanceof Title )
			$title = $object;

		$ns = $title->getNamespace();
		$titre = $title->getText();
		
		return true;
	}
	public function setIdTs( $id, $ts ) { $this->id = $id; $this->timestamp = $ts; }
	public function setSourceTitle( &$t ) { $this->sourceTitle = $t; }
	public function getDeferralState( ) { return in_array( $this->action, self::$deferred ); }
	
} // end class

//</source>
