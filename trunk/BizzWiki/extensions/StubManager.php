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


== Dependancy ==

== Installation ==
To install independantly from BizzWiki:
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager/StubManager.php');
</source>

== History ==

== Code ==
</wikitext>*/

class StubManager
{
	static $stubList;

	public static function createStub( $class, $filename, $hooks )
	{
		global $wgAutoloadClasses;
		$wgAutoloadClasses[$class] = $filename;
		
		self::$stubList[ $class ] = new Stub( $class, $hooks );
	}
	
} // end class

class Stub
{
	var $classe;
	var $obj;
	
	public function __construct( &$class, &$hooks )
	{
		global $wgHooks;
		foreach( $hooks as $hook )
			$wgHooks[ $hook ][] = array( &$this, 'h'.$hook );
		
		// don't create the object just yet!
		$this->classe = $class;
		$this->obj = null;
	}

	// intercept all methods called
	// instantiate the necessary object
	function __call( $method, $args )
	{
		if ( $this->obj === null )
			$obj = $this->obj = new $this->classe;
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
		}
		
		throw new MWException( "Too many arguments to method called in ".__METHOD__ );
	}

} // end class Stub
?>