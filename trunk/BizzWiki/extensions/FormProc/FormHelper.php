<?php
/*<wikitext>
{| border=1
| <b>File</b> || FormProcBaseClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
Provides a useful base class for form processing classes.

== Features ==
* Only loaded when actually used ('stub object' functionality)

== History ==

== Code ==
</wikitext>*/

class FormHelper
{
	private function &singleton()
	{
		static $instance = null;
		if (!$instance) $instance = new FormHelper;
		return $instance;	
	}

	function __call( $name, $args ) 
	{ return $this->_call( $name, $args );	}

	function _newObject() 
	{ return self::singleton();	}

	function __construct( ) 
	{	}
	
	

	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

} // end class
?>