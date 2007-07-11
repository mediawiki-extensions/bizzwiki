<?php
/*
 * FormProcClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont
 * $Id$
 * 
 */

class FormProcClass extends ExtensionClass
{
	// constants.
	const thisName = 'FormProcClass';
	const thisType = 'other';
	const id       = '$Id$';	
		  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function FormProcClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => 'Handles "action=formsubmit" post requests through page based PHP code',
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	public function setup() 
	{ parent::setup();	}

	public function hUnknownAction( $action, &$article )
	{
		// check if request 'action=formsubmit'
		if ($action != 'formsubmit') return true; // continue hook-chain.

		$article->loadContent();

		// follow redirects
		if ( $article->mIsRedirect == true )
		{
			$title = Title::newFromRedirect( $article->getContent() );
			$article = new Article( $title );
			$article->loadContent();
		}
		// Extract the code
		// Use our runphpClass helper
		$runphp = new runphpClass;
		$runphp->initFromContent( $article->getContent() );	

		// Execute Code
		$code = $runphp->getCode( true ); 

		if (!empty($code))
			$callback = eval( $code );  // we might implement functionality around a callback method in the future

		// Was there an expected class defined?
		$name = $article->mTitle->getDBkey();

		// the page name might actually be a sub-page; extract the basename without the full path.
		$pn   = explode( '/', $name );
		if ( !empty( $pn ))
		{
			$rn = array_reverse( $pn );
			$name = $rn[0];
		}
		$name .= 'Class';

		if ( class_exists( $name ))
		{
			$class = new $name();
			if ( is_object( $class))
				if (method_exists( $class, 'submit' ))
					$class->submit();
		}	

		// ... then it was a page built from ground up; nothing more to do here.
		return false;
	}

} // END CLASS DEFINITION
?>