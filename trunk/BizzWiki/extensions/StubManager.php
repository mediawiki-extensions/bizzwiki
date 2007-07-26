<?php
/*<wikitext>
{| border=1
| <b>File</b> || StubManager.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
This extension is meant to address 'rare events' handling through class object 'stubs'. For infrequent events 
(of course this is relative!), use this extension to instantiate a 'stub object' for the required hooks.

== Features ==
* Handles 'hook' registration
* Handles 'parser functions' registration
* Handles 'parser magic word' registration
* Handles 'parser tag' registration
* Handles extensions which implement logging functionality

== Usage ==
To create a stub, use: 
<code>StubManager::createStub( 'class name', 'full path filename', array of hooks );</code>
in <code>LocalSettings.php</code> after the require line <code>require( ...'StubManager.php' );</code>

== Dependancy ==

== Installation ==
To install independantly from BizzWiki:
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager/StubManager.php');
</source>

== Notes ==
The extension that are not candidate for this stubbing facility including those handling 'magic words' of the parser.

== History ==
* Added one more parameter to '__call' method to accomodate hooks such as ArticleSave.
* Added registration functionality for:
** 'tag' handlers (XML style section)
** 'mg' (i.e. parser functions)
** 'MW' (i.e. parser Magic Words)

== Code ==
</wikitext>*/
$wgExtensionCredits[StubManager::thisType][] = array( 
	'name'    => StubManager::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => 'Provides stubbing facility for extensions handling rare events. Extensions registered: ', 
);

class StubManager
{
	static $stubList;
	const thisType = 'other';
	const thisName = 'StubManager';
	static $logTable;
	
	/*
		$class: 		class of object to create when 'destubbing'
		$filename:		filename where class definition resides
		$i18nfilename:	filename where internationalisation messages reside
		$hooks:			array of hooks
		$logging:		if logging support is required
	*/
	public static function createStub(	$class, $filename, $i18nfilename = null, 
										$hooks, 
										$logging = false,
										$tags = null,		// parser 'tag' e.g. <php>
										$mgs  = null,		// parser function e.g. {{#addscriptcss}}
										$mws  = null		// parser magic word e.g. {{CURRENTTIME}}
									)
	{
		// need to wait for the proper timing
		// to initialize things around.
		self::setupInit();

		global $wgAutoloadClasses;
		$wgAutoloadClasses[$class] = $filename;
		
		self::$stubList[] = array(	'class'			=> $class, 
									'object' 		=> new Stub( $class, $hooks ),
									'classfilename' => $filename,
									'i18nfilename'	=> $i18nfilename,
									'hooks'			=> $hooks,
									'logging'		=> $logging,
									'tags'			=> $tags,
									'mgs'			=> $mgs,
									'mws'			=> $mws
									);
	}
	private static function setupInit()
	{
		static $initHooked = false;
		if ($initHooked)
			return;
		$initHooked = true;
		
		global $wgExtensionFunctions;
#		$wgExtensionFunctions[] = __CLASS__.'::setup'; // PHP <v5.2.2 issues a warning on this one.
		$wgExtensionFunctions[] = create_function( '', 'return '.__CLASS__.'::setup();' );
	}
	public static function setup()
	{
		self::setupMessages();
		self::setupLogging();
		self::setupCreditsHook();
	}
	private static function setupLogging( )
	{
		global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;

		foreach( self::$stubList as $index => $e )
		{
			if ( !$e['logging'] )
				continue;
				
			$class = $e['class'];
			$log = $GLOBALS[ 'log'.$class ];
		
			$wgLogTypes  []     = $log;
			$wgLogNames  [$log] = $log.'logpage';
			$wgLogHeaders[$log] = $log.'logpagetext';

			$actions = null;
			if (isset( $GLOBALS[ 'act'.$class ]))
				$actions = $GLOBALS[ 'act'.$class ];
			if (!empty( $actions ))
				foreach( $actions as $action )
					$wgLogActions[$log.'/'.$action] = $log.'-'.$action.'-entry'; 
		}		
	}
	private static function setupMessages( )
	{
		global $wgMessageCache;
		
		foreach( self::$stubList as $index => $e )
		{
			$i18nfilename = $e['i18nfilename'];
			if (!empty($i18nfilename))		
				require_once( $i18nfilename );
			else
				continue;
			
			$msg = $GLOBALS[ 'msg'.$e['class'] ];
	
			if (!empty( $msg ))
				foreach( $msg as $key => $value )
					$wgMessageCache->addMessages( $msg[$key], $key );		
		}
	}
	private static function setupCreditsHook()
	{
		static $updateCreditsHooked = false;
		if ($updateCreditsHooked)
			return;
		$updateCreditsHooked = true;
		
		global $wgHooks;
		$wgHooks['SpecialVersionExtensionTypes'][] = 'StubManager::hUpdateExtensionCredits';
	}
	public function hUpdateExtensionCredits( &$sp, &$ext )
	{
		global $wgExtensionCredits;
		
		$result = null;
		
		if (!empty( self::$stubList ))
			foreach( self::$stubList as $index => $obj )
				$result .= $obj['class'].' ';
				
		$result=trim($result);
		
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if ($el['name']==self::thisName)
				$el['description'] .= $result.'.';
		
		return true;
	}
	static function getRevisionData( &$id, &$date, $d = null )
	{
		$date = null;
		
		// e.g. $Id$
		if ($d===null)
			$data = explode( ' ', self::id );
		else
			$data = explode( ' ', $d );
		$id   = $data[2];
		$date = $data[3];
		return $id;
	}
	static function getRevisionId( $data=null )
	{	return self::getRevisionData( $id, $date, $data );	}
	
} // end class

class Stub
{
	static $hook_prefix	= 'h';
	static $tag_prefix	= 'tag_';
	static $mw_prefix	= 'MW_';
	static $mg_prefix	= 'mg_';

	static $done = false;
	
	var $classe;
	var $obj;
	
	var $hooks;
	var $tags;
	var $mgs;
	var $mws;
	
	public function __construct( &$class, &$hooks, &$tags = null, &$mgs = null, &$mws = null )
	{
		$this->setupHooks( $hooks );
		$this->setupTags( $tags );
		$this->setupMGs( $mgs );
		$this->setupMWs( $mws );
							
		// don't create the object just yet!
		$this->classe = $class;
		$this->obj = null;
	}

	private function setupHooks( &$hooks )
	{
		if (empty( $hooks ))
			return;
			
		global $wgHooks;
		foreach( $hooks as $hook )
		{
			$wgHooks[ $hook ][] = array( &$this, self::$hook_prefix.$hook );
			$this->hooks[] = $hook;
		}
	}
	private function setupTags( &$tags )
	{
		if (empty( $tags ))
			return;
			
		global $wgParser;
		foreach($tags as $index => $key)
		{
			$wgParser->setHook( "$key", array( $this, self::$tag_prefix.$key ) );
			$this->tags[] = $tag;
		}
	}
	private function setupMGs( &$mgs )
	{
		if (empty( $mgs ))
			return;
			
		global $wgParser;
		foreach($mgs as $index => $key)
		{
			$wgParser->setFunctionHook( "$key", array( $this, self::$mg_prefix.$key ) );			
			$this->mgs[] = $key;
		}
				
		$this->setupLanguageGetMagicHook();				
	}
	private function setupMWs( &$mws )
	{
		if (empty( $mws ))
			return;
			
		global $wgParser;
		foreach($mws as $index => $key)
		{
			$wgParser->setFunctionHook( "$key", array( $this, self::$mw_prefix.$key ) );	
			$this->mws[] = $key;
		}
		
		$this->setupLanguageGetMagicHook();
	}
	private function setupLanguageGetMagicHook()
	{
		static $done = false;
		if ($done) return;
		$done = true;

		global $wgHooks;				
		$wgHooks['LanguageGetMagic'            ][] = array( $this, 'hookLanguageGetMagic'             );
		$wgHooks['MagicWordMagicWords'         ][] = array( $this, 'hookMagicWordMagicWords'          );
		$wgHooks['MagicWordwgVariableIDs'      ][] = array( $this, 'hookMagicWordwgVariableIDs'       );
		$wgHooks['ParserGetVariableValueSwitch'][] = array( $this, 'hookParserGetVariableValueSwitch' );			
	}
	public function hookLanguageGetMagic( &$magicwords, $langCode )
	{
		// parser functions.
		if (!empty( $this->mgs ))		
			foreach($this->mgs as $index => $key )
				$magicwords [$key] = array( 0, $key );

		// magic words.
		if (!empty( $this->mws ))				
			foreach($this->mws as $index => $key )
				$magicwords [ defined($key) ? constant($key):$key ] = array( 0, $key );

		return true;
	}
	public function hookMagicWordMagicWords( &$mw )
	{
		if (!empty( $this->mws ))		
			foreach ( $this->mws as $index => $key )
				$mw[] = $key;

		return true;
	} 
	public function hookMagicWordwgVariableIDs( &$mw )
	{
		if (!empty( $this->mws ))
			foreach ( $this->mws as $index => $key )
				$mw[] = constant( $key  );

		return true;
	} 
	public function hookParserGetVariableValueSwitch( &$parser, &$varCache, &$id, &$ret )
	{
		if (empty( $this->mws )) 
			return true;

		// when called through {{magic word here}}
		// will call the method "MW_magic_word"
		if ( in_array( $id, $this->mws ) )
		{
			$method= self::$mw_prefix.$id;	
			$this->$method( $parser, $varCache, $ret );	
		}
		return true;
	}
	
	// intercept all methods called
	// instantiate the necessary object... only once.
	function __call( $method, $args )
	{
		if ( $this->obj === null )
			$obj = $this->obj = new $this->classe;  // un-stub
		else
			$obj = $this->obj;
		
		switch ( count($args) )
		{
			case 0:
				return $obj->$method( );
			case 1:
				return $obj->$method( $args[0] );
			case 2:
				return $obj->$method( $args[0], $args[1] );
			case 3:
				return $obj->$method( $args[0], $args[1], $args[2] );
			case 4:
				return $obj->$method( $args[0], $args[1], $args[2], $args[3] );
			case 5:
				return $obj->$method( $args[0], $args[1], $args[2], $args[3], $args[4] );
			case 6:
				return $obj->$method( $args[0], $args[1], $args[2], $args[3], $args[4], $args[5] );
			case 7:
				return $obj->$method( $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6] );
			case 8:
				return $obj->$method( $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7] );			
		}
		
		throw new MWException( __CLASS__.": too many arguments to method called in ".__METHOD__ );
	}

} // end class Stub
?>