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

	static $parsed = false;	
	static $liste = array();

	static $entryPattern = "//{{ [[Extension:%1]]\n%2\n//}}\n";
	
	public static function getList()
	{ return self::$liste; }
	
	/**
		This function would typically be called
		at the beginning of the operation.
	 */
	public static function read()
	{
		self::readFromFilesystem();
		self::parse();
	}
	/**
		This function would typically be called
		once the operation is finished.
	 */
	public static function commit()
	{
		self::lock();
		$r = self::writeToFilesystem();
		self::unlock();
		
		return $r;
	}
	
	public static function add( &$name )
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


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


	protected static function getFilename()
	{
		return dirname(__FILE__).'/'.self::$filename;	
	}
	protected static function readFromFilesystem()
	{
		self::$fileContents = @file_get_contents( self::getFilename() );
		self::$parsed = false;
	}
	protected static function lock()
	{}
	protected static function unlock()
	{}
	protected static function writeToFilesystem()
	{
		$header = wfMsg('extensionmanager'.'list-header');
		
		$content = $header;
		
		if (!empty( self::$liste ))		
			foreach( self::$liste as &$e )		
				$content .= self::formatEntry( $e['name'], $e['stat'] );
		
		$count = @file_put_contents( self::getFilename(), $content );
		
		// make sure the write operation was successful.
		$result = ( $count === strlen( $content ) ) ? true:false;
		
		return $result;
	}
	/**
		Format an entry for the list in the filesystem.		
	*/
							
	protected static function formatEntry( &$name, &$stat )
	{
		$pattern = self::$entryPattern;
		
		$entry1 = str_replace( '%1', $name, $pattern );
		$entry  = str_replace( '%2', $stat, $entry1 );
		
		return $entry;
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
	#static $pattern = '/\/\/\{\{(?:.*)\[\[Extension\:(.*)\]\](.*)\/\/\{\}/simU';
	static $pattern = '/\/\/\{\{(?:.*)\[\[Extension\:(.*)\]\](.*)\/\/}}/simU';
	
	protected static function parse()
	{
		if (empty( self::$fileContents ))
			return;
		
		self::$liste = array();
		
		$r = preg_match_all( self::$pattern, self::$fileContents, $matches );
		
		if (($r!==false) && ($r>1))
		{
			foreach( $matches[0] as $index => &$fullMatch )
			{
				$name 		= trim( $matches[1][$index] );
				$statement	= trim( $matches[2][$index] );
				
				self::$liste[] = array(	'name' => $name,
										'stat' => $statement
										);
			}
			self::$parsed = true;
		}
	}

} // end class declaration
//</source>