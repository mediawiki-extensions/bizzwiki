<?php
/*<wikitext>
{{Extension
|name        = SecureProperties
|status      = stable
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/SecureProperties/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
== Purpose==
Enables getting/setting global object properties securily (operations are only allowed on protected pages).

== Usage ==
* Property 'get': <nowiki>{{#pg:global object name|property}}</nowiki>
* Property 'set': <nowiki>{{#ps:global object name|property|value}}</nowiki>
* Method call: <nowiki>{{#pf:global object name|method name}}</nowiki>
* Global variable 'get': <nowiki>{{#gg:global object name}}</nowiki>
* Global variable 'set': <nowiki>{{#gs:global object name|value}}</nowiki>

== Notes ==
Of course, those functions can be called in the context of 'ParserPhase2':
* Property 'get': <nowiki>(($#pg|global object name|property$))</nowiki>
* Property 'set': <nowiki>(($#ps|global object name|property|value$))</nowiki>
* Method call: <nowiki>(($#pf|global object name|method name$))</nowiki>
* Global variable 'get': <nowiki>(($#gg:global object name$))</nowiki>
* Global variable 'set': <nowiki>(($#gs:global object name|value$))</nowiki>

== Examples ==
Current user name: {{#pg:wgUser|mName}}

Current user id: {{#pg:wgUser|mId}}

== Features ==
* Security: the 'magic words' of the extension can only be used on protected pages
* Namespace exemption: configured namespaces are exempted from the 'protection' requirement

== Dependancy ==
* [[Extension:StubManager]] extension

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require_once('extensions/StubManager.php');
StubManager::createStub(	'SecurePropertiesClass', 
							$IP.'/extensions/SecureProperties/SecureProperties.php',
							null,	// no i18n
							null, 	// no hooks
							false,	// no need for logging support
							null,	// tags
							array( 'pg', 'ps', 'pf', 'gg', 'gs' ),
							null,	// no magic words
							null	// no namespace triggering
						 );
</source>

== History ==
* added '#gg' and '#gs' magic words
* Removed dependency on ExtensionClass

== Todo ==
* Fix for 'exempt' namespaces option even considering StubManager

== Code ==
</wikitext>*/

$wgExtensionCredits[ SecureProperties::thisType ][] = array( 
	'name'        => SecureProperties::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Enables global object property get/set on protected pages',
	'url' 		=> StubManager::getFullUrl(__FILE__),
);

class SecureProperties
{
	// constants.
	const thisName = 'SecureProperties';
	const thisType = 'other';
		
	const actionGet = 0;
	const actionSet = 1;
	const actionGGet = 2;
	const actionGSet = 3;
	const actionFnc = 4;	
	
	const gobject   = 0;
	const gvariable = 1;
	
	// Namespace exemption functionality
	static $enableExemptNamespaces = true;
	static $exemptNamespaces;
	
	function __construct()
	{
		// default exempt namespaces from the BizzWiki platform.
		// won't affect installs of the extension outside the BizzWiki platform.
		if (defined('NS_BIZZWIKI'))   self::$exemptNamespaces[] = NS_BIZZWIKI;
		if (defined('NS_FILESYSTEM')) self::$exemptNamespaces[] = NS_FILESYSTEM;
	}

	public function mg_pg( )
	// {{#pg:object|property}}
	// (($#pg|object|property$))
	{
		$args = func_get_args();
		return $this->process( $args, self::actionGet );
	}

	public function mg_ps( )
	// {{#ps:object|property name|value}}
	// (($#ps|object|property|value$))	
	{
		$args = func_get_args();
		return $this->process( $args, self::actionSet );
	}
	public function mg_pf( )
	// {{#pf:object|function name}}
	// (($#pf|object|function name$))	
	{
		$args = func_get_args();
		return $this->process( $args, self::actionFnc );
	}
	public function mg_gg( )
	// {{#gg:global variable}}
	// (($#gg|global variable$))
	{
		$args = func_get_args();
		return $this->process( $args, self::actionGGet, self::gvariable );
	}
	public function mg_gs( )
	// {{#gs:global variable}}
	// (($#gs|global variable|value$))
	{
		$args = func_get_args();
		return $this->process( $args, self::actionGSet, self::gvariable  );
	}

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	private function process( &$args, $action = self::actionGet, $type = self::gobject )
	{
		$parser = @$args[0];
		
		if ( !$this->isAllowed( $parser->mTitle ) ) 
			return "<b>SecureProperties:</b> ".wfMsg('badaccess');

		$object   =             @$args[1];
		$property = $fnc      = @$args[2];
		$value    = $param1   = @$args[3];
		$param2               = @$args[4];
		$param3               = @$args[5];
				
		if ($type == self::gobject)
			if ( !is_object( $obj = $GLOBALS[$object] ) ) 
				return "<b>SecureProperties:</b> ".wfMsg('error')." <i>$object</i>";

		if ($type == self::gvariable)
			if ( !isset( $GLOBALS[$object] ) ) 
				return "<b>SecureProperties:</b> ".wfMsg('error')." <i>$object</i>";

		switch( $action )
		{
			case self::actionGet:
				return $obj->$property;
			case self::actionSet:
				$obj->$property = $value;					
				return null;
			case self::actionFnc:
				return $obj->$fnc();
			case self::actionGGet:
				return $GLOBALS[ $object ];
			case self::actionGSet:
				$GLOBALS[ $object ] = $property;
				return null;					
		}
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

} // end class
