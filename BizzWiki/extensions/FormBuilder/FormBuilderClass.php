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
	
	// 
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

	public function mg_html_form_begin( &$parser )
	{
	}
	public function mg_html_form_end( &$parser )
	{
	}
	public function mg_html_select_begin( &$parser )
	{
	}
	public function mg_html_select_end( &$parser )
	{
	}
	public function mg_html_fieldset_begin( &$parser )
	{
	}
	public function mg_html_fieldset_end( &$parser )
	{
	}
	public function mg_html_textarea_begin( &$parser )
	{
	}
	public function mg_html_textarea_end( &$parser )
	{
	}
	public function mg_html_option( &$parser )
	{
	}
	public function mg_html_label( &$parser )
	{
	}
	public function mg_html_input( &$parser )
	{
	}
	public function mg_html_legend( &$parser )
	{
	}
	public function mg_html_optgroup( &$parser )
	{
	}
	public function mg_html_button( &$parser )
	{
	}
	
	private function setupParams( &$params )
	{
		$template = array(
			array( 'key' => 'src',  'index' => '0', 'default' => '' ),
			array( 'key' => 'type', 'index' => '1', 'default' => 'js' ),
			array( 'key' => 'pos',  'index' => '2', 'default' => 'body' ),
			#array( 'key' => '', 'index' => '', 'default' => '' ),
		);
		// ask initParams to strip off the parameters
		// which aren't registered in $template.
		parent::initParams( $params, $template, true );
	}


} // END CLASS DEFINITION
?>