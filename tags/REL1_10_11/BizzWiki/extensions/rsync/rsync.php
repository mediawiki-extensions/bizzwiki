<?php
/*<!--<wikitext>-->
{{Extension
|name        = rsync
|status      = experimental
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/rsync/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
== WARNING ==
This extension is work in progress.

<!--@@
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose==
Provides a file based export of all the page changes. The directory containing the files can be used
along with 'rsync' to provide backup & restore functionality.

== Features ==
* Page
** Creation   (done)
** Update     (done)
** Delete     (done)
** Move       (done)
** Protection (done)
* File
** Upload
** Re-upload
** Delete
** Move       (not allowed - hence, nothing to implement)
* User
** Account creation
** Account options update

== Theory Of Operation ==
Page change events are trapped and the resulting new/updated pages are written to a 
specified filesystem directory. Trapping is done through the 'RecentChange_save' hook.

=== New page ===
File generated: <code>RC-id-new.xml</code>

=== Edit page ===
File generated: <code>RC-id-edit.xml</code>

=== Delete page ===
File generated: <code>RC-id-delete.xml</code>

=== Move page ===
File generated: <code>RC-id-move.xml</code>

=== Filename ===
Format: <code> RC-id-type.xml</code>
* id: unique identifier generated in the context of the 'RecentChanges' table
* type: 'new', 'edit' or 'log'
** new --> new page
** edit --> edit on a page
** delete --> page deletion
** log --> log entry

== Implementation ==
=== New Page ===
* Do complete export

=== Edit Page ===
* Do complete export

=== Move Page ===
A move transaction is the page with the new title enclosed in an xml file WITH
a new section 'source_title'.
* Complete export with 'source_title' section

=== Delete Page ===
* Export but don't 'writeRevision'

=== Page Restrictions ===
Needed to superclass 'WikiExporter' and 'XmlDumpWriter' classes.
* Export but don't 'writeRevision'
* Added 'restrictions' section to the XML dump

== Usage Notes ==
Make sure that the dump directory is writable.

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download [[Extension:StubManager]] extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require_once('/extensions/StubManager.php');
require('/extensions/rsync/rsync_stub.php');
</source>

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[rsync::thisType][] = array( 
	'name'    => rsync::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => "Provides page changes in an export file format.", 
);

class rsync
{
	const thisType = 'other';
	const thisName = 'rsync';
	
	const defaultDir = '_backup';
	
	var $directory; 
	var $found;

	//
	var $rc;
	var $op; // current operation
	var $executeDeferredInRcHook;
		
	/**
	 */
	public function __construct() 
	{
		$this->found = false;
		
		$this->op = null;
		
		// assume the default directory location
		$this->directory = self::defaultDir;
		
		// format the directory path.
		global $IP;
		$this->dir = $IP.'/'.$this->directory;
		
		rsync_operation::$dir = $this->dir;
		
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
		if (!$this->found)
			return true;
			
		$this->op = new rsync_operation(rsync_operation::action_edit,
										$article,
										WikiExporter::CURRENT,
										true,	// include last revision text
										$this->rc->mAttribs['rc_id'],
										$this->rc->mAttribs['rc_timestamp']											
									 );
									 
		rsync_operations::add( $this->op );
		rsync_operations::execute();
		
		return true;
	}

	/**
		WARNING: If ArticleDelete hook fails, we might have some stranded resources
		e.g. temporary file
	 */
	public function hArticleDelete( &$article, &$user, $reason )
	{
		$this->op = new rsync_operation(rsync_operation::action_delete,
										$article,
										WikiExporter::CURRENT,
										false,	// don't include last revision text
										null,	// we don't know the id just yet
										null	// nor the timestamp
								 		);
		rsync_operations::add( $this->op );
		rsync_operations::execute();
		
		return true;
	}
	/**
		Handles article delete.
	 */
	public function hArticleDeleteComplete( &$article, &$user, $reason )
	{
		$this->op->setIdTs(	$this->rc->mAttribs['rc_id'], 
							$this->rc->mAttribs['rc_timestamp'] );
		
		rsync_operations::executeDeferred();		
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
		if (!$this->found)
			return true;
			
		$this->op = new rsync_operation(rsync_operation::action_move,
										$newTitle,
										WikiExporter::CURRENT,
										true,	// include last revision text
										$this->rc->mAttribs['rc_id'],
										$this->rc->mAttribs['rc_timestamp']											
									 );
									 
		rsync_operations::add( $this->op );
		$this->op->setSourceTitle( $oldTitle );
		
		rsync_operations::execute();

		
		return true;		
	}
	
	/**
	
	 */
	public function hFileUpload( &$img )
	{
		
	}
	
	/**
		File Upload
	 */
	public function hUploadComplete( &$img )
	{
		// make a copy of the uploaded file to the rsync directory.
		
		// what about the meta data of the file???	
		
		return true;		
	}
	
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
		$this->op = new rsync_operation(rsync_operation::action_protect,
										$article,
										WikiExporter::CURRENT,
										false,	// do not include last revision text
										null,
										null										
									 );
									 
		rsync_operations::add( $this->op );
		rsync_operations::execute();
		
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
			
		$this->found = true;

		if ($this->executeDeferredInRcHook)
		{
			$this->op->setIdTs(	$this->rc->mAttribs['rc_id'], 
								$this->rc->mAttribs['rc_timestamp'] );
			
			rsync_operations::executeDeferred();			
		}

		return true;		
	}

} // end class

class rsync_operations
{
	static $liste;
	static $dListe;
	static $id = null;
	static $timestamp = null;
	
	public static function add( &$op ) { self::$liste[] = $op;	}
	public static function addDeferred( &$op ) { self::$dListe[] = $op; }
	
	public static function execute()
	{
		if (!empty( self::$liste ))	
			foreach( self::$liste as &$op )
			{
				$d = $op->getDeferralState();
				$op->execute();
				
				if ($d)	
					self::addDeferred( $op );
			}
	}

	public static function executeDeferred()
	{
		if (!empty( self::$dListe ))	
			foreach( self::$dListe as &$op )
				$op->executeDeferred();
	}
		
} // end class

/**		************************************************************
		Follows is a class that defines an 'rsync' export operation.
 */

class rsync_operation
{
	//
	static $dir;
	
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
	const action_editfile   = 8;
	
	// Commit Operation parameters
	var $includeRevision;
	var $deferralRequired;
	
	var $id;
	var $timestamp;
	
	var $action;
	var $ns;
	var $titre;

	var $sourceTitle;	// for move action

	var $isFilenameTemp;	
	var $filename;
	var $history;		// current or full
	
	var $text;
	
	public function __construct( $action, &$object, $history, $includeRevision, $id, $ts )
	{
		if ( $object instanceof Article )
			$title = $object->mTitle;
		else
			$title = $object;

		$this->ns = $title->getNamespace();
		$this->titre = $title->getText();

		$this->action = $action;
		$this->history = $history;
		$this->includeRevision = $includeRevision;
		$this->deferralRequired = $this->getDeferralState( );

		$this->id = $id;
		$this->timestamp = $ts;

		// will get filled later.		
		$this->filename = null;		// gets filled during 'updateList'
		$this->isFilenameTemp = null;
		
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
		
	/**
		Page-rcid-action-namespace.xml
	 */
	protected function generateFilename( )
	{
		if ($this->id === null)
		{
			$this->isFilenameTemp = true;
			$this->filename = tempnam( self::$dir, 'rsync_' );
			return;
		}
			
		$this->filename = self::$dir."/Page-".$this->id.'-'.$this->action.'-'.$this->ns.'.xml';
	}
	
	public function execute()
	{
		$this->generateFilename();

/*		
		switch( $this->action )	
		{
			case self::action_edit:
			
			case self::action_delete:
				
		}
*/
		$this->export();		
	}
	/**
		Deferred execution consists in renaming the 
		temporary export file.
	 */
	public function executeDeferred()
	{
		$tempName = $this->filename;
		
		// generate a permanent filename
		$this->generateFilename();
		
		self::rename( $tempName /*source*/, $this->filename /*target*/ );
	}

	protected function rename( &$source, &$target )
	{
		$r = rename( $source, $target );
		if ($r === false)
			throw new MWException();
	}
	/**
		This function uses MediaWiki's 'WikiExporter' class.
	 */
	private function export( )
	{
		$dump = new DumpFileOutput( $this->filename );

		$db = wfGetDB( DB_SLAVE );
		$exporter = new WikiExporterEx( $db, $this->history );
		
		// used for 'move' operation
		if (!empty( $this->sourceTitle ))
			$exporter->setSourceTitle( $this->sourceTitle );
		
		$exporter->setOutputSink( $dump );
		$exporter->includeRevision( $this->includeRevision );

		$exporter->openStream();
		
		$title = Title::makeTitle( $this->ns, $this->titre );
		if( is_null( $title ) ) return;

		$exporter->setPageTitle( $title );
		
		$exporter->pageByTitle( $title );
		
		$exporter->closeStream();
	}
	
} // end class















/**
	Class definition can be found in includes/Export.php
 */
class XmlDumpWriterEx extends XmlDumpWriter
{
	var $pageTitle;
	var $sourceTitle;
	var $includeRevision;
	
	function openPage( $row )
	{
		$out = parent::openPage( $row );

		if ($this->pageTitle instanceof Title)
		{
			$this->pageTitle->loadRestrictions();
			if (!empty( $this->pageTitle->mRestrictions ))
				$out .= $this->getRestrictionsSection(	$this->pageTitle->mRestrictions, 
														$this->pageTitle->mRestrictionsExpiry,
														$this->pageTitle->mCascadeRestriction
														 );
		}
		
		if (is_a( $this->sourceTitle, 'Title'))
			$out .= $this->getSourceTitleSection();
			
		return $out;
	}
	function getRestrictionsSection( &$restrictions, $expiry, $cascading )
	{
		$result = "<restrictions>\n";
		foreach( $restrictions as $restrictionType => &$levels )
			foreach( $levels as $level)
				$result .= "    <restriction type='".$restrictionType."' level='".$level.
							"' expiry='".$expiry."' cascading='".$cascading."' />\n";

		$result .= "</restrictions>\n";
		
		return $result;
	}
	function getSourceTitleSection()
	{
		return "<source_title>".Namespace::getCanonicalName( $this->sourceTitle->getNamespace() ).
				":".$this->sourceTitle->getText()."</source_title>\n";	
	}
	function writeRevision( &$row )
	{
		if (!$this->includeRevision)
			return null;
		return parent::writeRevision( $row );
	}
} // end class

class WikiExporterEx extends WikiExporter
{
	public function __construct( &$db, $history = WikiExporter::CURRENT,
			$buffer = WikiExporter::BUFFER, $text = WikiExporter::TEXT )	
	{
		parent::__construct( $db, $history, $buffer, $text );	
		$this->writer = new XmlDumpWriterEx();
		$this->writer->includeRevision = true;
	}			
	public function setPageTitle( &$title )	
	{
		$this->writer->pageTitle = $title;	
	}
	/**
		The source title is used in 'move' operations.
	 */
	public function setSourceTitle( &$title )
	{
		$this->writer->sourceTitle = $title;	
	}
	/**
		It is helpful not to include the full revision text sometimes.
	 */
	public function includeRevision( &$enable )
	{
		$this->writer->includeRevision = $enable;
	}
} // end class

//</source>
