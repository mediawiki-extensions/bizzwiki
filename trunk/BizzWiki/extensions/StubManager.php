<?php
/*<wikitext>
{{Extension
|name        = StubManager
|status      = stable
|type        = other
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
 
== Purpose==
This extension is meant to address 'rare events' handling through class object 'stubs'. For infrequent events 
(of course this is relative!), use this extension to instantiate a 'stub object' for the required hooks.
The net effect is lower transaction times thereby speeding up MediaWiki based sites.

== Features ==
* Handles 'hook' registration
* Handles 'parser function' registration
* Handles 'parser magic word' registration
* Handles 'parser tag' registration
* Handles extensions which implement logging functionality
* Handles 'namespace triggering': reduces even further the load time per transaction

== Audience ==
This extension is meant for 'extension writers'.

== Usage ==
To create a stub, use: 
<pre>
StubManager::createStub(  'class name', 
                          'full path filename of class file',
                          'full path filename of i18n file',						  
                          array of hooks,
						  $logging, // true if the extension requires logging support
                          array of tags,
                          array of parser function magic words,
                          array of parser magic words,
						  array of namespaces that trigger the extension
                        );
</pre>
in <code>LocalSettings.php</code> after the require line <code>require( ...'StubManager.php' );</code> 

== Examples ==
See [[Extension:EmailLog|Email Log extension]].

== Dependancy ==
None.

== Installation ==
To install independantly from BizzWiki:
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
</source>

== History ==
* Added one more parameter to '__call' method to accomodate hooks such as ArticleSave.
* Added registration functionality for:
** 'tag' handlers (XML style section)
** 'mg' (i.e. parser functions)
** 'MW' (i.e. parser Magic Words)
* fixed annoying warning about undefined offset.
* added namespace(s) trigger
* Added functionality to remove 'forgotten' hook_prefix in stub definition
** E.g. instead of listing 'ArticleSaveComplete', the list contained 'hArticleSaveComplete'

== See also ==
This extension is part of the [[Extension:BizzWiki|BizzWiki platform]].

== Code ==
Get it here [[Extension:StubManager/code]]. Please check SVN link provided above for the latest version.

== Live Code ==
[[Extension:StubManager/code]]
</wikitext>*/
// <source lang=php>
$wgExtensionCredits[StubManager::thisType][] = array( 
	'name'    => StubManager::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  		=> 'Jean-Lou Dupont',
	'description'	=> 'Provides stubbing facility for extensions handling rare events. Extensions registered: ', 
	'url'			=> StubManager::getFullUrl(__FILE__),				
);

class StubManager
{
	static $stubList;
	const thisType = 'other';
	const thisName = 'StubManager';
	static $logTable;
	
	static $paramsList = array(	'class',		// mandatory
								'classfilename',// mandatory
								'i18nfilename',
								'hooks',
								'logging',
								'tags',
								'mgs',
								'mws',
								'nss'
								);
	
	/**
	
	 */
	public static function createStub2( $params )
	{
		if (!is_array( $params ))
			{ echo __METHOD__.' $params not an array.'; return; }

		// need to make sure we've got the mandatory parameters covered.		
		if (!isset( $params['class'] ))
			{ echo __METHOD__.' missing "class" parameter.'; return; }		

		if (!isset( $params['classfilename'] ))
			{ echo __METHOD__.' missing "classfilename" parameter.'; return; }		

		// pick up all the parameters that StubManager knows about directly;
		// the others will be passed to the 'Stub' class.
		foreach( self::$paramsList as $paramKey )
			if (isset( $params[$paramKey] ))
			{
				$liste[$paramKey] = $params[$paramKey];
				unset( $params[$paramKey] );
			}
			else
				$liste[$paramKey] = null;				
		
	
		// create a stub object.
		$cListe['object'] = new Stub( $liste['class'], 
								$liste['hooks'], 
								$liste['tags'],
								$liste['mgs'], 
								$liste['mws'], 
								$liste['nss'],
								$params			// pass along the remaining parameters
								);
 
		// merge with the other parameters.
		$dListe = array_merge( $liste, $cListe );

		#var_dump( $dListe );
		
		self::$stubList[] = $dListe;
		
		// need to wait for the proper timing
		// to initialize things around.
		self::setupInit();

		global $wgAutoloadClasses;
		$wgAutoloadClasses[$liste['class']] = $liste['classfilename']; 
	}
	
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
										$mws  = null,		// parser magic word e.g. {{CURRENTTIME}}
										$nss  = null		// namespaces as trigger
									)
	{
		// need to wait for the proper timing
		// to initialize things around.
		self::setupInit();

		global $wgAutoloadClasses;
		$wgAutoloadClasses[$class] = $filename;
		
		self::$stubList[] = array(	'class'			=> $class, 
									'object' 		=> new Stub( $class, $hooks, $tags, $mgs, $mws, $nss ),
									'classfilename' => $filename,
									'i18nfilename'	=> $i18nfilename,
									'hooks'			=> $hooks,
									'logging'		=> $logging,
									'tags'			=> $tags,
									'mgs'			=> $mgs,
									'mws'			=> $mws,
									);
	}
	/**
		Create callback that will initialise all the stubs.
	 */
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
		self::callSetupMethods();
	}
	private static function callSetupMethods()
	{
		foreach( self::$stubList as $index => $e )
		{
			$obj = $e['object'];
			$obj->setup();
		}		
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
		
		// load all the extensions so they get a change to show their credits
		foreach( self::$stubList as $index => $e )
			#echo $e['classfilename'].' ';
			require_once( $e['classfilename'] );
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
			if (@isset($el['name']))
				if ($el['name']==self::thisName)
					$el['description'] .= $result.'.';
		
		return true;
	}
	static function getRevisionId( $svnId=null )
	{	
		// fixed annoying warning about undefined offset.
		if ( $svnId === null || $svnId == ('$'.'Id'.'$' /* fool SVN */) )
			return null;
			
		// e.g. $Id$
		$data = explode( ' ', $svnId );
		return $data[2];
	}

	static function getFullUrl( $filename )
	{ return 'http://www.bizzwiki.org/index.php?title=Filesystem:'.self::getRelativePath( $filename );	}

	static function getRelativePath( $filename )
	{
		global $IP;
		$relPath = str_replace( $IP, '', $filename ); 
		return str_replace( '\\', '/', $relPath );    // at least windows & *nix agree on this!
	}

	public static function processArgList( $liste, $getridoffirstparam=false )
	/*
	 * The resulting list contains:
	 * - The parameters extracted by 'key=value' whereby (key => value) entries in the list
	 * - The parameters extracted by 'index' whereby ( index = > value) entries in the list
	 */
	{
		if ($getridoffirstparam)   
			array_shift( $liste );
			
		// the parser sometimes includes a boggie
		// null parameter. get rid of it.
		if (count($liste) >0 )
			if (empty( $liste[count($liste)-1] ))
				unset( $liste[count($liste)-1] );
		
		$result = array();
		foreach ($liste as $index => $el )
		{
			$t = explode("=", $el);
			if (!isset($t[1])) 
				continue;
			$result[ "{$t[0]}" ] = $t[1];
			unset( $list[$index] );
		}
		if (empty($result)) 
			return $liste;
		return array_merge( $result, $liste );	
	}
	public static function getParam( &$alist, $key, $index, $default )
	/*
	 *  Gets a parameter by 'key' if present
	 *  or fallback on getting the value by 'index' and
	 *  ultimately fallback on default if both previous attempts fail.
	 */
	{
		if (array_key_exists($key, $alist) )
			return $alist[$key];
		elseif (array_key_exists($index, $alist) && $index!==null )
			return $alist[$index];
		else
			return $default;
	}
	public static function initParams( &$alist, &$templateElements, $removeNotInTemplate = true )
	{
		// v1.92 feature.
		if ($removeNotInTemplate)
			foreach( $templateElements as $index => &$el )
				if ( !isset($alist[ $el['key'] ]) )
					unset( $alist[$el['key']] );
		
		foreach( $templateElements as $index => &$el )
			$alist[$el['key']] = self::getParam( $alist, $el['key'], $el['index'], $el['default'] );
	}
	public function formatParams( &$alist , &$template )
	// look at yuiPanel extension for usage example.
	// $alist = { 'key' => 'value' ... }
	{
		foreach ( $alist as $key => $value )
			// format the entry.
			self::formatParam( $key, $value, $template );
	}
	private static function formatParam( &$key, &$value, &$template )
	{
		$format = self::getFormat( $key, $template );
		if ($format !==null )
		{
			switch ($format)
			{
				case 'bool':   $value = (bool) $value; break; 
				case 'int':    $value = (int) $value; break;
				default:
				case 'string': $value = (string) $value; break;					
			}			
		}
	}
	public static function getFormat( &$key, &$template )
	{
		$format = null;
		foreach( $template as $index => &$el )
			if ( $el['key'] == $key )
				$format  = $el['format'];
			
		return $format;
	}
	public static function checkPageEditRestriction( &$title )
	// v1.1 feature
	// where $title is a Mediawiki Title class object instance
	{
		$proceed = false;
  
		$state = $title->getRestrictions('edit');
		foreach ($state as $index => $group )
			if ( $group == 'sysop' )
				$proceed = true;

		return $proceed;		
	} 
	public static function getArticle( $article_title )
	{
		$title = Title::newFromText( $article_title );
		  
		// Can't load page if title is invalid.
		if ($title == null)	return null;
		$article = new Article($title);

		return $article;	
	}
	
	static function isSysop( $user = null ) // v1.5 feature
	{
		if ($user == null)
		{
			global $wgUser;
			$user = $wgUser;
		}	
		return in_array( 'sysop', $user->getGroups() );
	}
	
} // end class



// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%



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
	var $nss;

	public function __construct( &$class, 
								&$hooks, 
								&$tags = null, 
								&$mgs = null, 
								&$mws = null, 
								$nss = null,
								$params = null )
	{
		$this->setupHooks( $hooks );
		$this->tags = $tags;
		$this->mgs  = $mgs;
		$this->mws  = $mws;
		$this->nss  = $nss;
		$this->params  = $params;
		
		if ( !empty( $mgs) || !empty( $mws) )
			$this->setupLanguageGetMagicHook();

		// don't create the object just yet!
		$this->classe = $class;
		$this->obj = null;
	}
	/**
		called in the same timing as $wgExtensionFunctions array is processed
	 */
	public function setup()
	{
		$this->setupTags( $this->tags );
		$this->setupMGs( $this->mgs );
		$this->setupMWs( $this->mws );
	}

	private function setupHooks( &$hooks )
	{
		if (empty( $hooks ))
			return;
	
		// get rid of the hook prefix if the user forgot about it.
		foreach( $hooks as &$hook )
			if ( strncmp( self::$hook_prefix , $hook, strlen(self::$hook_prefix) ) == 0)
				$hook = substr( $hook, strlen(self::$hook_prefix) );
			
		global $wgHooks;
		foreach( $hooks as $hook )
			$wgHooks[ $hook ][] = array( &$this, self::$hook_prefix.$hook );
	}
	private function setupTags( &$tags )
	{
		if (empty( $tags ))
			return;
			
		global $wgParser;
		foreach($tags as $index => $key)
			$wgParser->setHook( "$key", array( $this, self::$tag_prefix.$key ) );
	}
	private function setupMGs( &$mgs )
	{
		if (empty( $mgs ))
			return;
			
		global $wgParser;
		foreach($mgs as $index => $key)
			$wgParser->setFunctionHook( "$key", array( $this, self::$mg_prefix.$key ) );			
	}
	private function setupMWs( &$mws )
	{
		if (empty( $mws ))
			return;
			
		global $wgParser;
		foreach($mws as $index => $key)
			$wgParser->setFunctionHook( "$key", array( $this, self::$mw_prefix.$key ) );	
	}
	private function setupLanguageGetMagicHook()
	{
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
		// Check triggers
		// Can really only act on the namespace we can
		// derive from the global wgTitle unfortunately.
		global $wgTitle;
		if (is_object( $wgTitle ))
			if ( $this->nss !== null )	// if none provided, act as normal
				if ( !in_array( $wgTitle->getNamespace(), $this->nss ) )
					return true;
		
		if ( $this->obj === null )
			$obj = $this->obj = new $this->classe( $this->params );  // un-stub
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
// </source>
?>