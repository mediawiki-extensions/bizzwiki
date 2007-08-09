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
<!--@@
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose==


== Features ==
* Page
** Creation
** Update
** Delete
** Move
* User
** Account creation
** Account options update
* File
** Upload
** Re-upload
** Delete
** Move (???)

== Theory Of Operation ==
Page change events are trapped and the resulting new/updated pages are written to a specified filesystem directory.


== Dependancy ==
* [[Extension:StubManager|StubManager extension]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/rsync/rsync.php');
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
	'description' => " ", 
);

class rsync
{
	const thisType = 'other';
	const thisName = 'rsync';
	
	static $directory = '';
	
	public __construct() {}
	
	/**
		Handles article creation & update
	 */	
	public function hArticleSaveComplete( &$article, &$user, &$text, &$summary, $minor, $dontcare1, $dontcare2, &$flags )
	{
		
	}
	
	/**
		Handles article delete.
	 */
	public function hArticleDeleteComplete( &$article, &$user, $reason )
	{
		
	}
	
	/**
		Handles article move.
		
		This hook is often called twice:
		- Once for the page
		- Once for the 'talk' page corresponding to 'page'
	 */
	public function hSpecialMovepageAfterMove( &$sp, &$oldTitle, &$newTitle )
	{
		// send a 'delete' 
		
		// send a 'update' 
	}
	
	/**
		TBD
	 */
	public function hAddNewAccount( &$user )
	{
		
	}
	
	/**
		File Upload
	 */
	public function hUploadComplete( &$img )
	{
		// make a copy of the uploaded file to the rsync directory.
		
		// what about the meta data of the file???	
	}
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%	
	
	private function doArticleUpdate()
	{
		
	}
	private function doArticleDelete()
	{
		
	}
	
} // end class
//</source>
