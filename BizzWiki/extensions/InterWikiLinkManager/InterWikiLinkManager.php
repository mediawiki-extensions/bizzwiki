<?php
/*<!--<wikitext>-->
{{Extension
|name        = InterWikiLinkManager
|status      = stable
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/InterWikiLinkManager/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose ==   
This Mediawiki extension enables a user with the appropriate rights to manage the InterWiki Links of the database.

== Features ==
* Can be used independantly of BizzWiki environment 
* Rights policing
* Logging
** Summary field contains logging info - visible in 'RecentChanges'
* New Namespace 'NS_INTERWIKI'

== USAGE NOTES ==
* Use "Interwiki:Main Page" to manage the interwiki links
* Use the magic word <code>{{#iwl: prefix | URI | local flag | transclusion flag }}</code>
* Appropriate rights management should be in place (e.g. Hierarchical Namespace Permissions extension)

== Example ==
An example of 'Interwiki:Main Page' using the magic word '#iwl' [[Extension:InterWikiLinkManager/Example|here]].

== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/InterWikiLinkManager/InterWikiLinkManager_stub.php');
</source>

== History ==
* Removed dependency on ExtensionClass
* Added 'stubbing' capability
* Fixed missing 'h' in hook 'SpecialVersionExtensionTypes' handler method
* Added namespace trigger

== TODO ==
* Add more validation

== See also ==
This extension is part of the BizzWiki package [[Extension:BizzWiki]].

[[Extension:Special page to work with the interwiki table]] provides similar functionality, but as a Special:Interwiki page.

[[category:interwiki extensions]]

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[InterWikiLinkManager::thisType][] = array( 
	'name'        => InterWikiLinkManager::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Manages the InterWiki links table. Namespace for extension is ',
	'url' 		=> StubManager::getFullUrl(__FILE__)		
);

class InterWikiLinkManager
{
	// constants.
	const thisName = 'InterWikiLinkManager';
	const thisType = 'other';

	const rRead    = "read";
	const rEdit    = "edit";
	const mPage    = "Main Page";

	// preload wikitext
	// ================
	
	const mgword = 'iwl';
	
	const header = "
{| border='1'
! Prefix || URI || Local || Trans";
	const footer = "
|}";
	const sRow = "
|-";
	const sCol = "
| ";

	// Link Table	
	var $iwl;     // the table read from the database
	var $new_iwl; // the desired table elements
	  
	function __construct(  )
	{
		$this->iwl     = array();
		$this->new_iwl = array();
	}
	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	// setup of this hook occurs in 'ExtensionClass' base class.
	{
		global $wgExtensionCredits;

		// first check if the proper rights management class is in place.
		if (defined('NS_INTERWIKI'))
			$hresult = 'defined.';
		else
			$hresult = '<b>not defined!</b>';

		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if (@isset($el['name']))		
				if ($el['name']==self::thisName)
					$el['description'].=$hresult;
				
		return true; // continue hook-chain.
	}
	
	public function mg_iwl( &$parser, $prefix, $uri, $local, $trans, $dotableline = true )
	// magic word handler function
	{
		if ( $r = $this->checkElement( $prefix, $uri, $local, $trans, $errCode ) )
		{
			$el = $this->new_iwl[ $prefix ] = array(	'uri'    => $uri, 
														'local'  => $local, 
														'trans'  => $trans 	);
		}

		// was there an error?
		if ( !$r )
			return $this->getErrMessage( $errCode );
		
		if ( $dotableline )
			return $this->formatLine( $prefix, $el );
			
	}	
	
	public function hArticleSave( &$article, &$user, &$text, &$summary, $minor, $dontcare1, $dontcare2, &$flags )
	{
		// check if we are in the right namespace
		$ns = $article->mTitle->getNamespace();
		if ($ns != NS_INTERWIKI) return true;

		// Paranoia: this should have already been checked.
		// does the user have the right to edit pages in this namespace?
		if (! $article->mTitle->userCan(self::rEdit) ) return true;  

		// Are we dealing with the page which contains the links to manage?
		if ( $article->mTitle->getText() != self::mPage ) return true;

		// Invoke the parser in order to retrieve the interwiki link data
		// composed through the magic word 'iwl'
		global $wgParser, $wgUser;
		$popts = new ParserOptions( $wgUser );
		$parserOutput = $wgParser->parse( $text, $article->mTitle, $popts, true, true, $article->mRevision );

		// Write the counts of deletes, inserts and updates.
		$summary = $this->updateIWL().$summary;
		
		return true; // continue hook-chain.
	}

	public function hEditFormPreloadText( &$text, &$title )
	// This hook is called to preload text upon initial page creation.
	// If we are in the NS_INTERWIKI namespace and no article is found ('initial creation')
	// then let's get the database entries.
	//
	// NOTE that the 'edit' permission is assumed to be checked prior to entering this hook.
	//
	{
		// Are we in the right namespace at all??
		$ns = $title->getNamespace();
		if ($ns != NS_INTERWIKI) return true; // continue hook chain.

		// Paranoia: Is the user allowed committing??
		// We shouldn't even get here if the 'edit' permission gets
		// verified adequately.
		if (! $title->userCan(self::rEdit) ) return true;		

		// start by reading the table from the database
		$this->getIWLtable();

		$text .= $this->getHeader();					// HEADER
		
		foreach( $this->iwl as $prefix => &$el )
			$text .= $this->formatMagicWordLine( $prefix, $el );
	
		$text .= $this->getFooter();					// FOOTER
	
		// stop hook chain.
		return false;
	}
	
	private function getIWLtable()
	// reads the 'interwiki' table into a local variable
	{
		$db =& wfGetDB(DB_SLAVE);
		$tbl = $db->tableName('interwiki');

		$result = $db->query("SELECT iw_prefix,iw_url,iw_local,iw_trans FROM  $tbl");
		
		while ( $row = mysql_fetch_array($result) ) 
			$this->iwl[ $row[0] ] = array(	'uri'   => $row[1], 
											'local' => $row[2], 
											'trans' => $row[3] );
		$db->freeResult( $result );
		
		ksort( $this->iwl );
	}
	
	private function getHeader() { return self::header; }
	private function getFooter() { return self::footer; }
	
	private function formatMagicWordLine( $prefix, &$el )
	{
		return '
{{#'.self::mgword.':'.
	$prefix.'|'.
	$el['uri']   .'|'.
	$el['local'] .'|'.
	$el['trans'] .'}}';
	}
	
	private function formatLine( $prefix, &$el )
	{
		$text = '';
		$text .= self::sRow;
		
		$text .= self::sCol;	$text .= $prefix;
		$text .= self::sCol;	$text .= $el['uri'];
		$text .= self::sCol;	$text .= $el['local'];
		$text .= self::sCol;	$text .= $el['trans'];

		return $text;				
	}
	
	private function updateIWL()
	{
		// The update process is fairly straightforward:
		// 0) Get the current list of entries from the database
		// 1) Compute the list of entries to delete
		// 2) Compute the list of entries to insert
		// 3) Compute the list of entries to update
	
		$this->getIWLtable();

		$dlist = $this->computeDeleteList();  $dc = count( $dlist );
		$ilist = $this->computeInsertList();  $ic = count( $ilist );
		$ulist = $this->computeUpdateList();  $uc = count( $ulist );

		$this->execute( $dlist, $ilist, $ulist );

		return "(d=$dc,i=$ic,u=$uc)";			
	}

	private function computeDeleteList()
	{
		// if it is in the database but not in the wanted list
		$dlist = null;
		foreach ( $this->iwl as $prefix => &$el )
			if ( ! in_array( $prefix, array_keys( $this->new_iwl ) ) )
				$dlist[] = $prefix;

		return $dlist;
	}
	private function computeInsertList()
	{
		// if it is not in the database but in the wanted list
		$ilist = null;
		foreach ( $this->new_iwl as $prefix => &$el )
			if ( ! in_array( $prefix, array_keys( $this->iwl ) ) )
				$ilist[] = $prefix;

		return $ilist;
	}
	private function computeUpdateList()
	{
		// if it is in the database but updated in the wanted list 
		$ulist = null;
		foreach ( $this->new_iwl as $prefix => &$el )
			if ( in_array( $prefix, array_keys( $this->iwl ) ) )
				if (($el['uri']   != $this->iwl[$prefix]['uri']  ) || 
					($el['local'] != $this->iwl[$prefix]['local']) ||
					($el['trans'] != $this->iwl[$prefix]['trans']) )
						$ulist[] = $prefix;

		return $ulist;
	}

	private function execute( &$dlist, &$ilist, &$ulist )
	// update the interwiki database table.
	{
		$db =& wfGetDB(DB_MASTER);
		$tbl = $db->tableName('interwiki');

		foreach ( $ilist as $prefix )
		{
			$uri   = $this->new_iwl[$prefix]['uri']; 
			$local = $this->new_iwl[$prefix]['local']; 
			$trans = $this->new_iwl[$prefix]['trans']; 
			$db->query("INSERT INTO $tbl (iw_prefix,iw_url,iw_local,iw_trans) VALUES('$prefix','$uri',$local,$trans )");
		}
												   
		foreach ( $ulist as $prefix ) 
		{
			$uri   = $this->new_iwl[$prefix]['uri']; 
			$local = $this->new_iwl[$prefix]['local']; 
			$trans = $this->new_iwl[$prefix]['trans']; 

			$db->query("UPDATE $tbl SET iw_url='$uri',iw_local=$local,iw_trans=$trans WHERE iw_prefix='$prefix'");
		}
		
		foreach ( $dlist as $prefix )
			$db->query("DELETE FROM $tbl WHERE iw_prefix = '$prefix'");
			
		$db->commit();			
	}
	
// TODO =================================================================================

	private function checkElement( &$prefix, &$uri, &$local, &$trans, &$errCode )
	{
		// no validation implemented at this moment.
		
		// everything is OK.
		return true;		
	}
	private function getErrMessage( $errCode )
	{
		// not much checking implemented at the moment...
		return '';	
	}

} // END CLASS DEFINITION
//</source>