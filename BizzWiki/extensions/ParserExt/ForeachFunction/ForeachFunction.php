<?php
/*<!--<wikitext>-->
{{Extension
|name        = ForeachFunction
|status      = beta
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ParserExt/ForeachFunction/ SVN]
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
== Purpose==
This extension provides 'looping' functionality (e.g. 'foreach') for iterating through arrays.

== Feature ==
* Security: only pages with protection on 'edit' can use the keywords provided by this extension

== Usage ==
=== Simple Array ===
* <code>{{#foreachx:global object name|property|pattern}}</code>
** The global object's property will be retrieved; the property should be an 'array'
* <code>{{#foreachx:global object name|method|pattern}}</code>
** The global object's method will be called: an array is expected as return value
* <code>{{#foreachx:global array variable|key|pattern}}</code>
** The global array variable will be referenced using 'key' as key
* <code>{{#foreachx:global array variable||pattern}}</code>
** The global array variable will be referenced (as a whole)
=== Array of Arrays ===
* <code>{{#foreachy:global object name|property|pattern}}</code>
** The global object's property will be retrieved; the property should be an 'array'
* <code>{{#foreachy:global object name|method|pattern}}</code>
** The global object's method will be called: an array is expected as return value
* <code>{{#foreachy:global array variable|key|pattern}}</code>
** The global array variable will be referenced using 'key' as key
* <code>{{#foreachy:global array variable||pattern}}</code>
** The global array variable will be referenced (as a whole)

== Dependancies ==
* [[Extension:StubManager]]
* [[Extension:ParserPhase2]] extension

== Installation ==
To install independantly from BizzWiki:
* Download & install [[Extension:StubManager]] extension
* Download & install [[Extension:ParserPhase2]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ForeachFunction/ForeachFunction_stub.php');
</source>
== See Also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki Platform]].

== History ==

== Code ==
<!--</wikitext>--><source lang=php>*/

$wgExtensionCredits[ForeachFunction::thisType][] = array( 
	'name'        => ForeachFunction::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Looping functions for global objects using parser functions',
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);

class ForeachFunction
{
	// constants.
	const thisName = 'ForeachFunction';
	const thisType = 'other';

	// Namespace exemption functionality
	static $enableExemptNamespaces = true;
	static $exemptNamespaces;
		
	function __construct( )
	{
		// default exempt namespaces from the BizzWiki platform.
		// won't affect installs of the extension outside the BizzWiki platform.
		if (defined('NS_BIZZWIKI'))   self::$exemptNamespaces[] = NS_BIZZWIKI;
		if (defined('NS_FILESYSTEM')) self::$exemptNamespaces[] = NS_FILESYSTEM;
	}
	public function mg_foreachx( &$parser, &$object, &$property, &$pattern, &$param1 = null, &$param2 = null )
	// {{#foreachx:global variable name||pattern}}	
	// {{#foreachx:global object name|property name|pattern}}
	// {{#foreachx:global object name|method name  |pattern}}	
	// Works on 'array' exclusively.
	{
		if ( !$this->isAllowed( $parser->mTitle ) ) 
			return "<b>ForeachFunctions:</b> ".wfMsg('badaccess');
		
		$a = self::getArray( $object, $property, $param1, $param2 );
		
		if (empty( $a )) return;
		
		$result = '';
		$index = 0;
		foreach( $a as $key => $value )
		{
			$result .= self::replaceVars( $pattern,  $key, $value, $index );
			$index++;
		}
		return $result;
	}
	public function mg_foreachy( &$parser, &$object, &$property, &$pattern, &$param1 = null, &$param2 = null )
	// {{#foreachy:global variable name||pattern}}	
	// {{#foreachy:global object name|property name|pattern}}
	// {{#foreachy:global object name|method name  |pattern}}	
	// Works on 'array' exclusively.
	{
		if ( !$this->isAllowed( $parser->mTitle ) ) 
			return "<b>ForeachFunctions:</b> ".wfMsg('badaccess');
		
		$a = self::getArray( $object, $property, $param1, $param2 );
		
		if (empty( $a )) return;
		
		$result = '';
		foreach( $a as $index => $b )
			if (!empty( $b ))
				foreach( $b as $key => $value )
					$result .= self::replaceVars( $pattern,  $key, $value, $index );

		return $result;
	}


	public function mg_forx( &$parser, &$object, &$prop, &$pattern, &$start, &$stop )
	// {{#forx:global object name|property name|pattern|start index|stop index}}
	// {{#forx:global object name|method name  |pattern|start index|stop index}}	
	// Works on 'array' exclusively.
	{
		if ( !$this->isAllowed( $parser->mTitle ) ) 
			return "<b>ForeachFunctions:</b> ".wfMsg('badaccess');
		
		$a = self::getArray( $object, $prop );
		
		if (empty( $a )) return;
		
		$result = '';
		for ( $index= $start; $index < $stop; $index++ )
		{
			$key = $index;
			$value = $a[ $key ];
			$result .= self::replaceVars( $pattern,  $key, $value, $index );
		}
			
		return $result;
	}

	private static function getArray( &$p1, &$p2, &$param1 = null, &$param2 = null )
	{
		$o = null;
		if (isset( $GLOBALS[$p1] ))
			$o = $GLOBALS[$p1];

		if (is_array( $o ))
			if (!empty( $p2 ))
				return $o[$p2];
			else
				return $o;

		// array = object->property
		if (is_object( $o))
			if (is_array( $o->$p2 )) 
				return $o->$p2;

		// array = object->property()
		if (is_object($o))
			if (is_callable( array($o, $p2) ))
				return $o->$p2( $param1, $param2 );

		return null;		
	}
	public static function replaceVars( &$pattern, &$key, &$value, &$index )
	{
		// find $key$ , $value$, $index$ variables in the pattern
		$r  = @str_replace( '$key$',   $key, $pattern );			
		$r2 = @str_replace( '$value$', $value, $r );
		$r3 = @str_replace( '$index$', $index, $r2 );		
		
		return $r3;
	}

	private function isAllowed( &$title )
	{ 
		if (self::$enableExemptNamespaces)
		{
			$ns = $title->getNamespace();
			if ( !empty(self::$exemptNamespaces) )
				if ( in_array( $ns, self::$exemptNamespaces) )
					return true;	
		}
		
		// check protection status
		if ( $title->isProtected( 'edit' ) ) return true;
		
		return false;
	}

} // end class.
//</source>