<?php

//<source lang=php>
/**
	This class handles the file 'ExtensionList'.
	It supports 'atomic' operations for effecting updates
	on the file.
	
	Notes:
	- file existence
	- 

		// Put 'ExtensionList' off-line
		// update ExtensionList.php
		// Restore 'ExtensionList' 
	
 */
class ExtensionList
{
	static $filename = '_List.php';
	static $fileContents = null;
	
	static $liste = array();
	
	protected static function getFilename()
	{
		return dirname(__FILE__).self::$filename;	
	}
	
	public static function getList()
	{ return self::$liste; }
	
	public static function add()
	{
		
	}
	
	public static function remove( &$name )
	{
		
	}
	
	/**
		Enables/disables the specified extension
	 */
	public static function setState( &$name )
	{}

	/**
		Gets the current status of the extension in the list
	 */
	public static function getState( &$name )
	{}

	static function readFromFilesystem()
	{
		self::$fileContents = file_get_contents( self::getFilename() );
	}
	static function writeToFilesystem()
	{
		$header = wfMsg('extensionmanager'.'list-header');
		
	}
	protected static function formatEntry()
	{
		
	}
	/**
		Parses the file list
		
		Each entry in ExtensionList.php looks like:
		
		//{{ [[Extension:XYZ]]
		include('XYZ');
		//}}
		
	 */
	// Subpattern #1: [[Extension: s1 ]]
	// Subpattern #2: include...	
	static $pattern = '/\/\/\{\{(?:.?)\[\[Extension\:(.*)\]\](.*)\/\/\{\}/siU';
	
	static function parse()
	{
		if (empty( self::$fileContents ))
			return;
		
		unset( self::$liste );
		self::$liste = array();
		
		$r = preg_match_all( $pattern, self::$fileContents, $matches );
		
		foreach( $matches[0] as $index => &$fullMatch )
		{
			$name 		= $matches[1][$index];
			$statement	= $matches[2][$index];
			
			self::$liste[] = array(	'name' => $name,
									'stat' => $statement
									);
		}
	}

} // end class declaration
//</source>