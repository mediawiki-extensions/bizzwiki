<?php
/*<wikitext>
{{extension:
|RegexToolsClass.php
|$Id$
|Jean-Lou Dupont
}}
== Code ==
</wikitext>*/

class RegexToolsClass extends ExtensionClass
{
	// constants.
	const thisName = 'RegexToolsClass';
	const thisType = 'other';
	const id       = '$Id$';		
	  
	public static function &singleton()
	{ return parent::singleton( );	}
	
	function __construct( )
	{
		parent::__construct( );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => self::getRevisionId( self::id ),
			'author'      => 'Jean-Lou Dupont', 
			'description' => ' ',
			'url' => self::getFullUrl(__FILE__),			
		);
	}
	public function setup() 
	{ parent::setup(); }

	/**
		Returns index in pattern array of *first* pattern match.
		
		@param: patternArrayName:	variable name (found in PageFunctions extension) 
		@param: input:				input string to regex match
	 */
	public function mg_regx_vars( &$parser, &$patternArrayName, &$input )
	{
		// the worst that can happen is that no valid return values are received.
		wfRunHooks('PageVarGet', array( &$patternArrayName, &$parray ) );
		$mIndex = $this->regexMatchArray( $parray, $input );	
		
		return $mIndex;
	}
	public function mg_regx( &$parser, &$patternString, &$input )
	{
		return $this->regexMatch( $patternString, $input );
	}
	private function regexMatchArray( &$patternArray, &$input )
	{
		if (!empty( $patternArray ))
			foreach( $patternArray as $index => &$p )
				if ( $this->regexMatch( $p, $input ) )
					return $index;
		return null;
	}
	private function regexMatch( &$p, &$input )
	{
		$pms= '/'.$p.'/siU';

		$m = preg_match( $pms, $input );
		if (($m !== false) && ($m>0))
			return true;
			
		return false;
	}
} // end class declaration.
?>