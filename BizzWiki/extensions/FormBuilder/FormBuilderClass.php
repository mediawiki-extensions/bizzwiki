<?php
/*<wikitext>
{| border=1
| <b>File</b> || FormBuilderClass.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Code ==
</wikitext>*/
class FormBuilderClass extends ExtensionClass
{
	// constants.
	const thisName = 'FormBuilderClass';
	const thisType = 'other';
	
	/* 
	static $mgwords = array(	'html_form_begin',	'html_form_end', 
								'html_select_begin','html_select_end',
								'html_fieldset_begin','html_fieldset_end',
								'html_textarea_begin','html_textarea_end',
								'html_option',
								'html_input' ,
								'html_label',
								'html_legend',
								'html_optgroup',
								'html_button',
							);
	*/
	static $mgwords = array( 'rawhtml' );
	
	public static function &singleton()
	{ return parent::singleton( );	}
	public function setup() 
	{ parent::setup();	}
	
	function FormBuilderClass( $mgwords = null, $passingStyle = self::mw_style, $depth = 1 )
	{
		parent::__construct( self::$mgwords );

		global $wgExtensionCredits;
		$wgExtensionCredits[self::thisType][] = array( 
			'name'        => self::thisName, 
			'version'     => '$Id$',
			'author'      => 'Jean-Lou Dupont', 
			'description' => ''
		);
	}

	public function mg_rawhtml ( &$parser, $text )
	{
		return $text;
	}
	
} // END CLASS DEFINITION
?>