<?php
/*<wikitext>
{| border=1
| <b>File</b> || ForeachFunction.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension provides 'looping' functionality (e.g. 'foreach') for iterating through arrays.

== Features ==

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
* [[Extension:StubManager]] extension (v>=306)
* ParserPhase2 extension

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/ForeachFunction/ForeachFunction.php');
</source>

== History ==

== Code ==
</wikitext>*/
global $wgExtensionCredits;
$wgExtensionCredits[ForeachFunctionClass::thisType][] = array( 
	'name'			=> ForeachFunctionClass::thisName, 
	'version'		=> StubManager::getRevisionId( '$Id$' ),
	'author'		=> 'Jean-Lou Dupont', 
	'description'	=> 'Looping functions for global objects using parser functions',
	'url' 			=> StubManager::getFullUrl(__FILE__),			
);

class ForeachFunctionClass
{
	// constants.
	const thisName = 'ForeachFunctionClass';
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
?>