<?php
/*
<!--<wikitext>-->
 <file>
  <name>BackupS3.body.php</name>
  <version>$Id$</version>
  <package>Extension.BackupS3</package>
 </file>
<!--</wikitext>-->
*/
// <source lang=php>

$wgExtensionCredits[BackupS3::thisType][] = array( 
	'name'    		=> BackupS3::thisName,
	'version'		=> StubManager::getRevisionId('$Id$'),
	'author'		=> 'Jean-Lou Dupont',
	'url'			=> 'http://www.mediawiki.org/wiki/Extension:BackupS3',	
	'description' 	=> "Provides replication to Amazon S3.", 
);

require('BackupS3.job.php');

class BackupS3
{
	const thisType = 'other';
	const thisName = 'BackupS3';
	
	/**
	 */
	public function __construct() 
	{
	}
	/**
		This hook is used to provide useful information to the sysop
		in the 'Special:Version' page.
	 */
	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	{
		global $wgExtensionCredits;

		// TODO
		$msg = $this->getDebugMessage();
		
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if (isset($el['name']))		
				if ($el['name']==self::thisName)
					$el['description'] .= $msg;
				
		return true; // continue hook-chain.
	}
	/**
	 */
	private function getDebugMessage()
	{
		
	}
	/**
		Main hook: this method is called when the event 'Backup'
		is fired from [[Extension:Backup]].
	 */
	public function hBackup( &$op )
	{


		return true;
	}
	
} // end class

//</source>
