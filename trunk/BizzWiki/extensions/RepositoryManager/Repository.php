<?php
/*<!--<wikitext>-->
This file is part of the [[Extension:RepositoryManager]] extension.

<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

class RepositoryClasses
{
	static $list = array();	
}

abstract class Repository
{
	var $project;
	var $directory;
	var $baseURI;
	var $uri;	// fully formatted uri
	var $fileList;	
	
	// Error codes
	const codeOK = 0;
	const codeFetchURIfailed = 1;
	const codeDirectoryEmpty = 2;
	const codeInvalidDirectoryList = 3;
	
	public function __construct( $baseURI, &$project, &$directory )
	{
		$this->project = $project;
		$this->directory = $directory;
		$this->baseURI = $baseURI;
		
		$this->formatRepoURI();
	}
	
	/**
		Class Factory
	 */
	public static function newFromClass( &$name, &$repo, &$project, &$dir )
	{
		if ( class_exists( $name ) )
			return new $name( $project, $dir );

		// Try autoloading the class
		$obj = new @$name( $project, $dir );
		if (is_object( $obj ))
			return $obj;
			
		return null;
	}

	protected function formatRepoURI()
	{
		$project = htmlspecialchars( $this->project );

		$uri = $this->baseURI;
		$this->uri = str_replace( '$1', $project, $uri ).$this->directory;
	}
	
	abstract public function exists();

	// Recursive function which preserves whole (relative) path information
	abstract public function getFileList( $dir );

	// Verifies if the specified uri corresponds to a directory
	abstract public function isDir( &$uri );

	// Requires the full relative path
	abstract public function getFileCode( &$file, &$code );

} // end 'ExtensionRepository' class definition
//</source>