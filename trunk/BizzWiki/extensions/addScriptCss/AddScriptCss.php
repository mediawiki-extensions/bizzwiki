<?php
/*<!--<wikitext>-->
{{Extension
|name        = AddScriptCss
|status      = stable
|type1       = tag
|type2       = parser function
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/AddScriptCss/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->
== Purpose== 
Inserts <script> & <link> (i.e. CSS) scripts at the bottom of the page's head or within the page's body. 
Securily insert arbitrary code to the page's head using <addtohead>.

== Features ==
* Security: local files (URI) only
** Files must be located in wiki installation home directory/scripts
* <code><addtohead>some html code here></addtohead></code>
* Security:
** Only 'edit' restricted pages can access 'addtohead' functionality.
* Use global variable '$bwScriptsDirectory' to customize scripts directory

== Examples ==
<pre><addscript src='local URL' /></pre>
* (R1) e.g. <addscript src=/sarissa/sarissa type=js />
* (R2) e.g. {{#addscript: src=/styleinfo|pos=head|type=css}}

:R1) Results in <code>/home/scripts/sarissa/sarissa.js</code> being added to the page's body section provided the said file exists.

:R2) The CSS file <code>/home/scripts/styleinfo.css</code> will be added to the page's HEAD section (provided it exists).

== Syntax ==
Form 1: <addscript src=filename [type={js|css}] [pos={head|body}] />

Form 2: {{#addscript:src=filename [|type={js|css} [|pos={head|body}] }}

If no 'type' field is present, then the extension assumes 'js'.

If no 'pos' field is present, then the extension assumes 'body'

== USAGE NOTES ==
* When using 'pos=body', it is recommended to use the extension 'ParserCacheControl' in order to better integrate this extension with the standard MW parser cache.
* Use the global variable <code>$bwScriptsDirectory</code> to set a new directory for the scripts
** e.g. <code>$bwScriptsDirectory = 'scripts'; </code> will result in <code>$IP.'/'.$scripts</code> to be used.
 
== Dependency ==
* Depends on [[Extension:StubManager]]

== History ==
* Adjusted for new ExtensionClass version (no automatic registering of hooks of ExtensionClass)
* Adjusted singleton invocation to end of file (PHP limitation)
* Imported required functionality from ExtensionClass
* Imported 'addtohead' tag functionality from SecureHTML extension
* Removed dependency on ExtensionClass
* Added support for a global setting '$bwScriptsDirectory'
* Fixed issue when $IP !== $wgScriptPath

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
$bwScriptsDirectory = '/path/to/your/scripts/relative/to/root.install.directory';
require('/extensions/AddScriptCss/AddScriptCss_stub.php');
</source>

== TODO ==
* - internationalize

== Code ==
<!--</wikitext>--><source lang=php>*/
		
$wgExtensionCredits[AddScriptCss::thisType][] = array( 
	'name'        => AddScriptCss::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Adds javascript and css scripts to the page HEAD or BODY sections',
	'url'		=> 'http://mediawiki.org/wiki/Extension:AddScriptCss',
);

class AddScriptCss
{
	// constants.
	const thisName = 'AddScriptCss';
	const thisType = 'other'; 

	// error codes.
	const error_none     = 0;
	const error_uri      = 1;
	const error_bad_type = 2;
	const error_bad_pos  = 3;
		
	static $base = 'BizzWiki/scripts/';

	function __construct( )
	{
		// take on the global setting if found.
		global $bwScriptsDirectory;
		if (isset( $bwScriptsDirectory ))		
			self::$base = $bwScriptsDirectory;
	}

	public function tag_addscript( &$text, &$params, &$parser)
	{ return $this->process( $params );	}

	/**
		Parser Tag Magic Word for adding un-restricted content in the document's 'head'
	 */
	public function tag_addtohead( &$text, &$params, &$parser )
	{
		if (!$this->canProcess( $parser->mTitle) ) 
			return "<b>AddScriptCss:</b> ".wfMsg('badaccess');
		
		$this->addHeadScript( $text );		
	}
	
	public function mg_addscript( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );		
		return $this->process( $params );
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
		$this->initParams( $params, $template, true );
	}
	private function normalizeParams( &$params )
	{
		// This function checks the validity of the following
		// parameters: 'type' and 'pos'
		extract( $params );
		
		$type=strtolower( $type );
		if ( ($type!='js') && ($type!='css') )
			return self::error_bad_type;

		$pos=strtolower( $pos );
		if ( ($pos!='head') && ($pos!='body') )
			return self::error_bad_pos;

		return self::error_none;		
	}
	private function process( &$params )
	{
		$this->setupParams( $params );

		$errCode = self::error_none;
		$r = $this->normalizeParams( $params );
		if ($r!=self::error_none) return $this->errMessage( $r );

		// src, type, pos
		extract( $params );
		
		$src = $this->cleanURI( $src, $type );
		if (!$this->checkURI( $src, $type ))
			return $this->errMessage( self::error_uri ); 

		global $IP;
		$p = $IP.'/'.self::$base.$src.'.'.$type;

		// Which type of script does the user want?
		switch( $type )
		{
			case 'css': $t = '<link href="'.$p.'" rel="stylesheet" type="text/css" />'; break;		
			default:
			case 'js':	$t = '<script src="'.$p.'" type="text/javascript"></script>';   break;
		}	

		// Where does the user want the script?
		switch( $pos )
		{
			case 'head':
				// For 'head' scripts, we need to embed a 'meta tag' in the text
				// This 'meta tag' will be saved in the parser cache waiting to be
				// look at by the hook 'OutputPageBeforeHTML'.
				$this->addHeadScript( $t );
				break;
			default:
			case 'body': 
				// For 'body' scripts, we need to intercept the processing flow
				// after the 'tidy' process in the parser and feed script tags there.
				// No need to encode them, they should be safe from the parser/parser cache.
				$this->addBodyScript( $t );
				break;	
		}
		// everything OK
		return null;
	}
	private function cleanURI( $uri )
	{
		return str_replace( array('/../', '../', '\\..\\',
									"..\\",'"','`','&','?',
									'<','>','.' ), "", $uri);
	}
	private function checkURI( $uri, $type )
	{
		// uri must resolved to a local file in the $base directory.
		$spath = self::$base.$uri.'.'.$type;
		
		global $IP;
	
		return file_exists( $IP."/{$spath}" );
	} 
	private function errMessage( $errCode )  // FIXME
	{
		$m = array(
			self::error_none     => 'no error',
			self::error_uri      => 'invalid URI',
			self::error_bad_type => 'invalid TYPE parameter',
			self::error_bad_pos  => 'invalid POS parameter',
		);
		return 'AddScriptCss: '.$m[ $errCode ];
	}

/****************************************************************************
Add scripts & stylesheets functionality.
This process must be done in two phases:
phase 1- encode information related to the required
         scripts & stylesheets in a 'meta form' in
		 the parser cache text.
phase 2- when the page is rendered, extract the meta information
         and include the information appropriately in the 'head' of the page.		  
*****************************************************************************/
	static $scriptsHeadList = array();
	static $scriptsBodyList = array();

	function addHeadScript( &$st )
	{
		if ( empty($st) ) return;
		
		// try to add scripts only once!
		if	(in_array($st, self::$scriptsHeadList))
			return;
		
		self::$scriptsHeadList[] = $st;						
		//$this->setupScriptsInjectionFeeder();					
	}
	private static function encodeHeadScriptTag( &$st )
	{
		return '<!-- META_SCRIPTS '.base64_encode($st).' -->';	
	}
	
	/** 
		This hook should *ALWAYS* be initialized if we are to have any chance
		of catching the 'head' scripts we need to add !! 
		
		This function sifts through 'meta tags' embedded in html comments
		and picks out scripts & stylesheet references that need to be put
		in the page's HEAD.

	*/
	function hOutputPageBeforeHTML( &$op, &$text )
	{
		static $scriptsAdded = false;
		
		// some hooks get called more than once...
		// In this case, since ExtensionClass provides a 
		// base class for numerous extensions, then it is very
		// likely this method will be called more than once;
		// so, we want to make sure we include the head scripts just once.
		if ($scriptsAdded) return true;
		$scriptsAdded = true;
		
		if (preg_match_all(
        	'/<!-- META_SCRIPTS ([0-9a-zA-Z\\+\\/]+=*) -->/m', 
        	$text, 
        	$matches)===false) return true;
			
    	$data = $matches[1];

	    foreach ($data AS $item) 
		{
	        $content = @base64_decode($item);
	        if ($content) $op->addScript( $content );
	    }
	    return true;
	}
	
	
	// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	
	public function addBodyScript( &$st )
	{
		if ( empty($st) ) return;
		
		// try to add scripts only once!
		if	(!in_array($st, self::$scriptsBodyList)) 
		{
			self::$scriptsBodyList[] = $st;
			//$this->setupScriptsInjectionFeeder();
		}
	}
	
	// Scripts Feeding Logic
	// Required for both 'head' & 'body' injected scripts.
	function hParserAfterTidy( &$parser, &$text )
	// set the meta information in the parsed 'wikitext'.
	{
		// it seems that trying to protect
		// against multiple calls break more things
		// than help.
		
#		static $scriptsListed = false;
#		if ($scriptsListed) return true;
#		$scriptsListed = true;

		if (!empty(self::$scriptsBodyList))
			foreach(self::$scriptsBodyList as $sc)
				$text .= $sc; 

		if (!empty(self::$scriptsHeadList))
			foreach(self::$scriptsHeadList as $sc)
				$text .= $this->encodeHeadScriptTag( $sc ); 
	
		return true;
	}

	private function canProcess( &$obj )
	{
		if (!is_object( $obj ))
			return false; // paranoia
			
		if (is_a( $obj, 'Article'))
			$title = $obj->mTitle;
		elseif (is_a( $obj, 'Title'))
			$title = $obj;
		else
			return false;
		
		// check protection status
		if ( $title->isProtected( 'edit' ) ) return true;
		
		return false;
	}
	public function initParams( &$alist, &$templateElements, $removeNotInTemplate = true )
	{
		if ($removeNotInTemplate)
			foreach( $templateElements as $index => &$el )
				if ( !isset($alist[ $el['key'] ]) )
					unset( $alist[$el['key']] );
		
		foreach( $templateElements as $index => &$el )
			$alist[$el['key']] = $this->getParam( $alist, $el['key'], $el['index'], $el['default'] );
	}

	public function processArgList( $list, $getridoffirstparam=false )
	/*
	 * The resulting list contains:
	 * - The parameters extracted by 'key=value' whereby (key => value) entries in the list
	 * - The parameters extracted by 'index' whereby ( index = > value) entries in the list
	 */
	{
		if ($getridoffirstparam)   
			array_shift( $list );
			
		// the parser sometimes includes a boggie
		// null parameter. get rid of it.
		if (count($list) >0 )
			if (empty( $list[count($list)-1] ))
				unset( $list[count($list)-1] );
		
		$result = array();
		foreach ($list as $index => $el )
		{
			$t = explode("=", $el);
			if (!isset($t[1])) 
				continue;
			$result[ "{$t[0]}" ] = $t[1];
			unset( $list[$index] );
		}
		if (empty($result)) 
			return $list;
		return array_merge( $result, $list );	
	}
	public function getParam( &$alist, $key, $index, $default )
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

} // END CLASS DEFINITION
//</source>