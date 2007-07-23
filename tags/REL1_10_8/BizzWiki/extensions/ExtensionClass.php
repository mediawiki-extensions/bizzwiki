<?php
/*
* ExtensionClass.php
* 
* MediaWiki extension
* @author: Jean-Lou Dupont (http://www.bluecortex.com)
* $Id$
*
* Purpose:  Provides a toolkit for easier Mediawiki
*           extension development.
*
* FEATURES:
* 0) Can be used independantly of BizzWiki environment 
* - 'singleton' implementation suited for extensions that require single instance
* - 'magic word' helper functionality
* - limited pollution of global namespace
* - Automatic registration of hooks
*
* Tested Compatibility: MW 1.8.2 (PHP5), 1.9.3, 1.10
*
* History:
* v1.0		Initial availability
* v1.01    Small enhancement in processArgList
* v1.02    Corrected minor bug
* v1.1     Added function 'checkPageEditRestriction'
* v1.2     Added 'getArticle' function
* ----     Moved to SVN management
* v1.3     Added wgExtensionCredits updating upon Special:Version viewing
* v1.4     Fixed broken singleton functionality
* v1.5		Added automatic registration of hook functions based
*          on the definition of an handler in the derived class
*          (e.g. if handler 'hArticleSave' exists, then the appropriate
*           'ArticleSave' hook is registered)
* v1.51    Fixed '$passingStyle' bug (thanks to Joshua C. Lerner)
* v1.6     Added 'updateCreditsDescription' helper method.
* v1.7		Added 'depth' parameter support: more than 1 class depth can be created.
*          Added 'setupTags' method (support for parser tags)
*          Enhancement to 'getParam' method
*          Added 'formatParams' method
* v1.8     Added 'initFirst' parameter
* v1.9     Added support for including 'head' scripts and stylesheeets
*          in a manner compatible with parser caching functionality.
*          (Original idea from [user:Jimbojw]
* v1.91    Added check for screening script duplicates in 'addHeadScript'
* v1.92    Added optional removal of parameters not listed in template.
* v1.93    Added 'replaceHook' method.
*          (dependancy on 'replaceHookList')
*
* ------   Moved to BizzWiki
<wikitext>
== History ==
* Added check to automatic hook handler to make sure that hooks are only registered when extended class requires them.
* Added 'SyntaxHighlight' hook.
* Changed hooks 'ParserAfterTidy' and 'OutputPageBeforeHtml'
* Added 'getRevisionData'
* Added BIZZWIKI release number in credits
* Added 'getRelativePath' function 
* Added 'AutoMethods' functionality: ExtensionClass just looks up the method list of the derived class
  and looks for the $prefix matching methods to initialize hooks, parser functions and magic words.
* Added better support for adding 'head' and 'body' scripts whilst preserving parser caching coherency.
* Added initialization code for '$l' variable -> stops PHP from issuing warnings
* Added 'UserSettingsChanged' hook (User.php)
* Removed 'UnwatchArticle' duplicate entry
* More support for PageFunctions extension

</wikitext>*/
$wgExtensionCredits['other'][] = array( 
	'name'    => 'ExtensionClass',
	'version' => ExtensionClass::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => 'Part of BizzWiki release '.BIZZWIKI, 
);

class ExtensionClass
{
	static $gObj; // singleton instance

// List up-to-date with MW 1.10 SVN 21828
static $hookList = array(
'ArticlePageDataBefore', 
'ArticlePageDataAfter', 
'ArticleAfterFetchContent',
'ArticleViewRedirect', 
'ArticleViewHeader',
'ArticlePurge',
'ArticleSave', 					// public function hArticleSave        ( &$article, &$user, &$text, $summary, $minor, $dontcare1, $dontcare2, &$flags ) {}
'ArticleInsertComplete',		
'ArticleSaveComplete',			// public function hArticleSaveComplete( &$article, &$user, &$text, $summary, $minor, $dontcare1, $dontcare2, &$flags ) {}
'MarkPatrolled', 
'MarkPatrolledComplete', 
'WatchArticle', 
'WatchArticleComplete',
'UnwatchArticle', 
'UnwatchArticleComplete', 
'ArticleProtect', 
'ArticleProtectComplete',
'ArticleDelete', 
'ArticleDeleteComplete', 
'ArticleEditUpdatesDeleteFromRecentchanges',
'ArticleEditUpdateNewTalk',
'DisplayOldSubtitle',
'IsFileCacheable',
'CategoryPageView',
'FetchChangesList',
'DiffViewHeader',
'AlternateEdit', 
'EditFormPreloadText', 			// public function hEditFormPreloadText( &$textbox, &$title ) {}
'EditPage::attemptSave', 
'EditFilter', 
'EditPage::showEditForm:initial',
'EditPage::showEditForm:fields',
'SiteNoticeBefore',
'SiteNoticeAfter',
'FileUpload',
'BadImage', 
'MagicWordMagicWords', 
'MagicWordwgVariableIDs',
'MathAfterTexvc',
'MessagesPreLoad',
'LoadAllMessages',
'OutputPageParserOutput',
'OutputPageBeforeHTML',
'AjaxAddScript', 
'PageHistoryBeforeList',
'PageHistoryLineEnding',
'ParserClearState', 
'ParserBeforeStrip',
'ParserAfterStrip',
'ParserBeforeTidy',
'ParserAfterTidy',						// public function hParserAfterTidy( &$parser, &$text ) {}
'ParserBeforeInternalParse',
'InternalParseBeforeLinks', 
'ParserGetVariableValueVarCache',
'ParserGetVariableValueTs', 
'ParserGetVariableValueSwitch',
'IsTrustedProxy',
'wgQueryPages', 
'RawPageViewBeforeOutput', 
'RecentChange_save',
'SearchUpdate', 
'AuthPluginSetup', 
'LogPageValidTypes',
'LogPageLogName', 
'LogPageLogHeader', 
'LogPageActionText',
'SkinTemplateTabs', 
'BeforePageDisplay', 
'SkinTemplateOutputPageBeforeExec', 
'PersonalUrls', 
'SkinTemplatePreventOtherActiveTabs',
'SkinTemplateTabs', 
'SkinTemplateBuildContentActionUrlsAfterSpecialPage',
'SkinTemplateContentActions', 
'SkinTemplateBuildNavUrlsNav_urlsAfterPermalink',
'SkinTemplateSetupPageCss',
'BlockIp', 
'BlockIpComplete', 
'BookInformation', 
'SpecialContributionsBeforeMainOutput',
'EmailUser', 
'EmailUserComplete',
'SpecialMovepageAfterMove',
'SpecialPage_initList',
'SpecialPageExecuteBeforeHeader',
'SpecialPageExecuteBeforePage',
'SpecialPageExecuteAfterPage',
'PreferencesUserInformationPanel',
'SpecialSearchNogomatch',
'ArticleUndelete',
'UndeleteShowRevision',
'UploadForm:BeforeProcessing',
'UploadVerification',
'UploadComplete',
'UploadForm:initial',
'AddNewAccount',
'AbortNewAccount',
'UserLoginComplete',
'UserCreateForm',
'UserLoginForm',
'UserLogout',
'UserLogoutComplete',
'UserRights',
/*'SpecialVersionExtensionTypes',*/ // reserved special treatment
'AutoAuthenticate', 
'GetFullURL',
'GetLocalURL',
'GetInternalURL',
'userCan',
'TitleMoveComplete',
'isValidPassword',
'UserToggles',
'GetBlockedStatus',
'PingLimiter',
'UserRetrieveNewTalks',
'UserClearNewTalkNotification',
'PageRenderingHash',
'EmailConfirmed',
'ArticleFromTitle',
'CustomEditor',
'UnknownAction',
'LanguageGetMagic',  // reserved a special treatment in this class 
'LangugeGetSpecialPageAliases',
'MonoBookTemplateToolboxEnd',
'SkinTemplateSetupPageCss',
'SkinTemplatePreventOtherActiveTabs',

// BizzWiki Additions
'SyntaxHighlight',  // for geshi extension
'PageVarGet',		// PageFunctions extension
'PageVarSet',		// PageFunctions extension
);

	// filled by subclipse.
	const id = '$Id$';
	
	var $className;
	
	var $paramPassingStyle;
	var $ext_mgwords;	
	
	// Parameter passing style.
	const mw_style = 1;
	const tk_style = 2;
	
	// Magic Word related {{
	static $mw_prefix = 'MW_';
	static $mg_prefix = 'mg_';
	
	const mw_only_parser_functions = 0;
	const mw_mixed = 1;
	
	const mw_parser_function = 0;
	const mw_parser_variable = 1;
	// }}
	
	// Tag related
	static $tag_prefix = 'tag_';
	
	// Hook related
	static $hook_prefix = 'h';

	// Auto method functionality related
	var $tagList;
	var $varList;
	var $fncList;
	var $hokList;
	
	public static function &singleton( $mwlist=null ,$globalObjName=null, 
										$passingStyle = self::mw_style, $depth = 1,
										$initFirst = false )
	{
		// Let's first extract the callee's classname
		$trace = debug_backtrace();
		$cname = $trace[$depth]['class'];

		// If no globalObjName was given, create a unique one.
		if ($globalObjName === null)
			$globalObjName = substr(create_function('',''), 1 );
		
		// Since there can only be one extension with a given child class name,
		// Let's store the $globalObjName in a static array.
		if (!isset(self::$gObj[$cname]) )
			self::$gObj[$cname] = $globalObjName; 
				
		if ( !isset( $GLOBALS[self::$gObj[$cname]] ) )
			$GLOBALS[self::$gObj[$cname]] = new $cname( $mwlist, $passingStyle, $depth, $initFirst );
			
		return $GLOBALS[self::$gObj[$cname]];
	}
	public function ExtensionClass( $mgwords=null, $passingStyle = self::mw_style, 
									$depth = 1, $initFirst = false, $replaceHookList = null )
	/*
	 *  $mgwords: array of 'magic words' to subscribe to *if* required.
	 */
	{
		global $wgHooks;
			
		if ($passingStyle == null) $passingStyle = self::mw_style; // prevention...
		$this->paramPassingStyle = $passingStyle;
		
		// Let's first extract the callee's classname
		$trace = debug_backtrace();
		$this->className= $cname = $trace[$depth]['class'];
		// And let's retrieve the global object's name
		$n = self::$gObj[$cname];
		
		global $wgExtensionFunctions;
		
		// v1.8 feature
		$initFnc = create_function('',"global $".$n."; $".$n."->setup();");
		if ($initFirst)
			 array_unshift(	$wgExtensionFunctions, $initFnc );
		else $wgExtensionFunctions[] = $initFnc;
		
		// %%%%%%%%%%%%%%%%%%%%%%%%%%
		#echo 'setup: '.$this->className.'<br/>';
		$this->setupAutoMethods();
		
		$this->initMagicWordsList();
		// %%%%%%%%%%%%%%%%%%%%%%%%%%
	
		// $this->ext_mgwords = $this->normalizeMagicWordsList( $mgwords );		
		if (is_array($this->ext_mgwords) )
			$wgHooks['LanguageGetMagic'][] = array($this, 'getMagic');

		// v1.3 feature
		if ( in_array( 'hUpdateExtensionCredits', get_class_methods($this->className) ) )
			$wgHooks['SpecialVersionExtensionTypes'][] = array( &$this, 'hUpdateExtensionCredits' );				

		foreach ( self::$hookList as $index => $hookName)
		{
			$replaceFlag = false;
			
			if (!empty($replaceHookList))
				$replaceFlag = in_array( $hookName, $replaceHookList);
					
			if ( method_exists( $this, 'h'.$hookName ) )					
				if ( $replaceFlag )
					$wgHooks[$hookName][count($wgHooks[$hookName])-1] = array( &$this, 'h'.$hookName );
				else
					$wgHooks[$hookName][] = array( &$this, 'h'.$hookName );
		}
	}
	public function getParamPassingStyle() { return $this->passingStyle; }
	
	public function setup( )
	{
		if (is_array($this->ext_mgwords))
			$this->setupMagicMixed();
		if (is_array( $this->tagList))
			$this->setupTags( $this->tagList );
	}
	public function setupMagicMixed()
	{
		global $wgParser;
		static $hooked = false;
		
		if ( empty($this->ext_mgwords) ) return;
		
		foreach($this->ext_mgwords as $key => $type)
		{
			switch ( $type )
			{
				case self::mw_parser_function:
					$wgParser->setFunctionHook( "$key", array( $this, 'mg_'.$key ) );
					break;
				case self::mw_parser_variable:
					$vars[] = $key;
					break;
			}
		}
		if (!$hooked) // paranoia
		{
			$hooked = true;
			global $wgHooks;
			$wgHooks['MagicWordMagicWords'][]          = array( $this, 'hookMagicWordMagicWords' );
			$wgHooks['MagicWordwgVariableIDs'][]       = array( $this, 'hookMagicWordwgVariableIDs' );
			$wgHooks['ParserGetVariableValueSwitch'][] = array( $this, 'hookParserGetVariableValueSwitch' );			
		}
	}
	private function setupAutoMethods()
	{
		$m = get_class_methods( $this->className );
	
		foreach ( $m as $method )
		{
			// get methods pertaining to 'parser tag' functionality
			// i.e. ones starting with 'tag_'
			$isTag = strncasecmp( $method, self::$tag_prefix, strlen(self::$tag_prefix) )== 0;
			$tag = substr( $method, strlen(self::$tag_prefix) );
			
			// get methods pertaining to 'parser variable' functionality
			// i.e. ones starting with 'MW_'
			$isVar = strncasecmp( $method, self::$mw_prefix, strlen(self::$mw_prefix) )== 0;
			$var = substr( $method, strlen(self::$mw_prefix) );
						
			// get methods pertaining to 'parser function' functionality
			// i.e. ones starting with 'mg_'
			$isFnc = strncasecmp( $method, self::$mg_prefix, strlen(self::$mg_prefix) )== 0;
			$fnc = substr( $method, strlen(self::$mg_prefix) );
						
			// get methods pertaining to 'hook' functionality
			// i.e. ones listed in $hookList starting with 'h'
			$hok = substr( $method, strlen( self::$hook_prefix ) );
			$isHookTest1 = strncasecmp( $method, self::$hook_prefix, strlen(self::$hook_prefix) )== 0;
			$isHookTest2 = in_array( $hok, self::$hookList );
			
			$isHook = ($isHookTest1==true) && ($isHookTest2==true);

			if ( $isTag )	$this->tagList[] = $tag;
			if ( $isVar )	$this->varList[] = $var;			
			if ( $isFnc )	$this->fncList[] = $fnc;			
			if ( $isHook )	$this->hokList[] = $hok;
		}
	}
	private function initMagicWordsList()
	{
		if (!empty( $this->varList ))
			foreach( $this->varList as $var )
				$this->ext_mgwords[$var] = self::mw_parser_variable;

		if (!empty( $this->fncList ))
			foreach( $this->fncList as $fnc )
				$this->ext_mgwords[$fnc] = self::mw_parser_function;
	}
	// ================== MAGIC WORD HELPER FUNCTIONS ===========================
	public function getMagic( &$magicwords, $langCode )
	{
		foreach($this->ext_mgwords as $key => $style )
		{
			switch( $style )
			{
				case self::mw_parser_function:
					$magicwords [$key] = array( 0, $key );
					break;
				case self::mw_parser_variable:
					$magicwords [ defined($key) ? constant($key):$key ] = array( 0, $key );
					break;					
			}
		}
		return true;
	}
	public function hookMagicWordMagicWords( &$mw )
	{
		$l = $this->getMagicWordsVariables();
		if (!empty( $l ))		
			foreach ( $l as $index => $key )
				$mw[] = $key;

		return true;
	} 
	public function hookMagicWordwgVariableIDs( &$mw )
	{
		$l = $this->getMagicWordsVariables();
		if (!empty( $l ))
			foreach ( $l as $index => $key )
				$mw[] = constant( $key  );

		return true;
	} 
	public function hookParserGetVariableValueSwitch( &$parser, &$varCache, &$id, &$ret )
	{
		$l = $this->getMagicWordsVariables();
		
		if (empty( $l )) return true;

		// when called through {{magic word here}}
		// will call the method "MW_magic_word"
		if ( in_array( $id, $l ) )
		{
			$method= self::$mw_prefix.$id;	
			$this->$method( $parser, $varCache, $ret );	
		}
		return true;
	}
	public function normalizeMagicWordsList( &$l )
	{
		if (empty($l)) return null;
		
		foreach( $l as $p1 => $p2 )
		{
			if ( is_numeric($p1) )
				// legacy
				$newStyleList[ $p2 ] = self::mw_parser_function;
			else
				$newStyleList[ $p1 ] = $p2;
		}
		return $newStyleList;		
	}
	public function getMagicWordsVariables()
	{
		$l = null; // stop PHP from complaining
		if (!empty($this->ext_mgwords))
			foreach ( $this->ext_mgwords as $key => $style )
				if ($style==self::mw_parser_variable)
					$l[] = $key;
		return $l;		
	}
	public function getMagicWordsFunctions()
	{
		$l = null; // stop PHP from complaining
		if (!empty($this->ext_mgwords))
			foreach ( $this->ext_mgwords as $key => $style )
				if ($style==self::mw_parser_function)
					$l[] = $key;
		return $l;		
	}

	// TAG RELATED
	public function setupTags( $tagList )
	{
		global $wgParser;
		if (!empty( $tagList ))
			foreach($tagList as $index => $key)
				$wgParser->setHook( "$key", array( $this, self::$tag_prefix.$key ) );
	}
	// ================== GENERAL PURPOSE HELPER FUNCTIONS ===========================
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
	public function initParams( &$alist, &$templateElements, $removeNotInTemplate = true )
	{
		// v1.92 feature.
		if ($removeNotInTemplate)
			foreach( $templateElements as $index => &$el )
				if ( !isset($alist[ $el['key'] ]) )
					unset( $alist[$el['key']] );
		
		foreach( $templateElements as $index => &$el )
			$alist[$el['key']] = $this->getParam( $alist, $el['key'], $el['index'], $el['default'] );
	}
	public function formatParams( &$alist , &$template )
	// look at yuiPanel extension for usage example.
	// $alist = { 'key' => 'value' ... }
	{
		foreach ( $alist as $key => $value )
			// format the entry.
			$this->formatParam( $key, $value, $template );
	}
	private function formatParam( &$key, &$value, &$template )
	{
		$format = $this->getFormat( $key, $template );
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
	public function getFormat( &$key, &$template )
	{
		$format = null;
		foreach( $template as $index => &$el )
			if ( $el['key'] == $key )
				$format  = $el['format'];
			
		return $format;
	}
	public function checkPageEditRestriction( &$title )
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
	public function getArticle( $article_title )
	{
		$title = Title::newFromText( $article_title );
		  
		// Can't load page if title is invalid.
		if ($title == null)	return null;
		$article = new Article($title);

		return $article;	
	}
	
	function isSysop( $user = null ) // v1.5 feature
	{
		if ($user == null)
		{
			global $wgUser;
			$user = $wgUser;
		}	
		return in_array( 'sysop', $user->getGroups() );
	}
	
	function updateCreditsDescription( &$text ) // v1.6 feature.
	{
		global $wgExtensionCredits;
	
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if ($el['name']==self::thisName)
				$el['description'].=$text;	
	}

	static function getRevisionData( &$id, &$date, $d = null )
	{
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
	{
		return self::getRevisionData( $id, $date, $data );
	}

	static function getFullUrl( $filename )
	{ return 'http://www.bizzwiki.org/index.php?title=Filesystem:'.self::getRelativePath( $filename );	}

	static function getRelativePath( $filename )
	{
		global $IP;
		$relPath = str_replace( $IP, '', $filename ); 
		return str_replace( '\\', '/', $relPath );    // at least windows & *nix agree on this!
	}

/*  Add scripts & stylesheets functionality.
This process must be done in two phases:
phase 1- encode information related to the required
         scripts & stylesheets in a 'meta form' in
		 the parser cache text.
phase 2- when the page is rendered, extract the meta information
         and include the information appropriately in the 'head' of the page.		  
************************************************************************************/
	static $scriptsHeadList = array();
	static $scriptsBodyList = array();

	function addHeadScript( &$st )
	{
		if ( empty($st) ) return;
		
		// try to add scripts only once!
		if	(!in_array($st, self::$scriptsHeadList)) 
		{
			self::$scriptsHeadList[] = $st;						
			$this->setuScriptsInjectionFeeder();					
		}
	}
	private static function encodeHeadScriptTag( &$st )
	{
		return '<!-- META_SCRIPTS '.base64_encode($st).' -->';	
	}
	
	// This hook should *ALWAYS* be initialized if we are to have any chance
	// of catching the 'head' scripts we need to add !! 
	public function initHeadScriptsHook()
	{
		static $installed = false;
		if ( $installed ) return;
		$installed = true;
		
		global $wgHooks;
		$wgHooks['OutputPageBeforeHTML'][] = array( $this, 'hookOutputPageBeforeHTML' );		
	}
	function hookOutputPageBeforeHTML( &$op, &$text )
	// This function sifts through 'meta tags' embedded in html comments
	// and picks out scripts & stylesheet references that need to be put
	// in the page's HEAD.
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
			$this->setuScriptsInjectionFeeder();
		}
	}
	
	// Scripts Feeding Logic
	// Required for both 'head' & 'body' injected scripts.
	
	private function setuScriptsInjectionFeeder()
	{
		static $installed = false;
		if ( $installed ) return;
		$installed = true;
		
		global $wgHooks;
		$wgHooks['ParserAfterTidy'][] = array( $this, 'hookParserAfterTidy' );
	}

	function hookParserAfterTidy( &$parser, &$text )
	// set the meta information in the parsed 'wikitext'.
	{
		static $scriptsListed = false;
		if ($scriptsListed) return true;
		$scriptsListed = true;

		if (!empty(self::$scriptsBodyList))
			foreach(self::$scriptsBodyList as $sc)
				$text .= $sc; 

		if (!empty(self::$scriptsHeadList))
			foreach(self::$scriptsHeadList as $sc)
				$text .= $this->encodeHeadScriptTag( $sc ); 
	
		return true;
	}

} // end class definition.
?>