<?php
/*
 * FormProcClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont
 * $Id$
 * 
 */

class PostProcClass extends ExtensionClass
{
	// constants.
	const thisName = 'PostProc';
	const thisType = 'other';
	  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function PostProcClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => '$Id$',
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Handles "action=submit" post requests through page based PHP code'
		);
	}
	public function setup() 
	{ 
		parent::setup();
	}

	public function hArticleFromTitle( &$title, &$article )
	{
		// check if request 'action=submit'
		
		// follow redirects
	}

} // END CLASS DEFINITION
?>