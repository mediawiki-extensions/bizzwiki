<?php
/*<!--<wikitext>-->
{{Extension
|name        = 
|status      = beta
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ExtensionManager/ SVN]
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

== History ==

== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== Code ==
<!--</wikitext>--><source lang=php>*/

abstract class ExtensionRepository
{
	// relative to the installation i.e. $IP
	const repoClassesDir = '/Repositories';
	
	var $project;
	var $directory;
	var $baseURI;
	
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
	public static function newFromClass( &$name, &$repo, &$dir )
	{
		// is the class already loaded??
		if ( class_exists( $name ) )
			return true;

		$filename = __FILE__.self::repoClassesDir.'/'.$name.'.php';
		
		// silently try to load the class describing the repository
		@require( $filename );
		
		// check if we have succeeded (!)
		if ( class_exists( $name ) )
			return new $name( $dir );
			
		return null;
	}

	protected function formatRepoURI()
	{
		$project = htmlspecialchars( $this->project );

		$uri = $this->baseURI;
		$this->uri = str_replace( '$1', $project, $uri );
	}
	
	abstract public function exists();

	// Recursive function which preserves whole (relative) path information
	abstract public function getFileList();

	// Requires the full relative path
	abstract public function getFileCode();

} // end 'ExtensionRepository' class definition
//</source>