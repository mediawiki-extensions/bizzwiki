<?php
/*
 * PageAfterAndBefore.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 *
 * Purpose:  Provides a 'magic word' interface to retrieve
 *           'preceding' and 'succeding' pages relative to a
 *           given page title.           
 *
 * Features:
 * *********
 *
 * {{#pagebefore: [context]|[namespace]|[title]|[category] }}
 * {{#pageafter:  [context]|[namespace]|[title]|[category] }}
 * {{#firstpage:  [context]|[namespace]|        [category]|[filtercurrent] }}
 * {{#lastpage:   [context]|[namespace]|        [category]|[filtercurrent] }}
 *
 * Where: 
 * -- 'context'       is reserved for future use
 * -- 'namespace'     denotes the canonical name of the namespace one wishes to act on
 * -- 'title'         denotes the 'prefixedDBkey' (i.e. title name with underscores) 
 * -- 'category'      denotes the category name used for filtering titles
 * -- 'filtercurrent' if the current title == last/first page, filter if 'yes'
 *
 * DEPENDANCY:
 * 1) 'ExtensionClass' extension
 *
 * Tested Compatibility:  MW 1.8.2, 1.9.3
 *
 * HISTORY:
 * -- Version 1.0:	initial availability
 *            1.1:  Added support for checking
 *                  IF 'firstpage' == 'currentpage'
 *                  OR 'lastpage'  == 'currentpage'
 *                  THEN don't return title name.
 *          
 */
$wgExtensionCredits['other'][] = array( 
	'name'    => 'PageAfterAndBefore Extension', 
	'version' => '$Id$',
	'author'  => 'Jean-Lou Dupont', 
);

class PageAfterAndBefore extends ExtensionClass
{
	public static function &singleton( )
	{ return parent::singleton(); }
	
	// Our class defines magic words: tell it to our helper class.
	public function __construct()
	{	return parent::__construct( );	}
	public function setup()
	{ parent::setup(); }

	// ===============================================================
	var $cList = array();

	public function mg_pagebefore( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );
		$this->setupParams($params);

		$res = $this->getPages( $params['namespace'], $params['title'], 'desc',$params['category'] );
		return $res[0];
	}
	public function mg_pageafter( &$parser )
	{
		$params = $this->processArgList( func_get_args(), true );
		$this->setupParams($params);

		$res = $this->getPages( $params['namespace'], $params['title'], 'asc',$params['category'] );		
		return $res[0];
	}
	public function mg_firstpage( &$parser )
	// If 'namespace' is not supplied, defaults to current page's namespace
	{
		$params = $this->processArgList( func_get_args(), true );
		$this->setupParams($params);
		
		$res = $this->getPages( $params['namespace'], '' , 'asc', $params['category'] );
		// filter out if requested and currentpage==firstpage
		$currentpage = $this->getCurrentPage( $ns, $title );
		if ( ($params['filtercurrent']=='yes') && ( $res[0]== $currentpage))
			return '';
		return $res[0];
	}
	public function mg_lastpage( &$parser )
	// If 'namespace' is not supplied, defaults to current page's namespace
	{
		$params = $this->processArgList( func_get_args(), true );
		$this->setupParams($params);

		$res = $this->getPages( $params['namespace'], '' , 'desc', $params['category'] );		
		// filter out if requested and currentpage==lastpage
		$currentpage = $this->getCurrentPage( $ns, $title );
		if ( ($params['filtercurrent']=='yes') && ( $res[0]== $currentpage))
			return '';
		return $res[0];
	}

	private function setupParams( &$params )
	{
		$this->getCurrentPage( $d_ns_name, $d_title );

		$template = array(
			array( 'key' => 'context',       'index' => '0', 'default' => 'context0' ),
			array( 'key' => 'namespace',     'index' => '1', 'default' => "{$d_ns_name}" ),
			array( 'key' => 'title',         'index' => '2', 'default' => "{$d_title}" ),
			array( 'key' => 'category',      'index' => '3', 'default' => '' ),
			array( 'key' => 'filtercurrent', 'index' => '4', 'default' => 'yes' ),
			#array( 'key' => '', 'index' => '', 'default' => '' ),
		);
		parent::initParams( $params, $template );
	}
/*
	public function mg_xyz( &$parser, $params )
	{
	}
*/
	// ===============================================================
	public function getCurrentPage( &$ns, &$title )
	{
		global $wgTitle;
		$ns_num = $wgTitle->getNamespace();
		$ns     = Namespace::getCanonicalName( $ns_num );
		$title  = $wgTitle->getDBkey();
		
		return $ns.":".$title;
	}
	public function getPages( $namespace, $titlename, $dir='asc', $category = null, $limit=2 )
	{
		$orderDir = ($dir=="asc")      ? "ASC" : "DESC";
		$cmpDir   = ($orderDir=='ASC') ? "1"   : "-1";
		$where = "";
		$cat = null;
		$pages = array();
						
		$dbr      =& wfGetDB( DB_SLAVE );
		$page     = $dbr->tableName( 'page' );
        $catlinks = $dbr->tableName( 'categorylinks' ); 

		if (!empty($titlename))
		{
			if (!empty($namespace))
				$namespace.=':';

			$title   =  Title::newFromText( $namespace.$titlename );
			if (!is_object($title))
				return null;
				
			$ns        = $title->getNamespace();
			$key       = $title->getDBkey();
			
			$namespace = Namespace::getCanonicalName( $ns );
			$where = "AND STRCMP({$page}.page_title,'{$key}')={$cmpDir}";
		}
		else
		{
			if (!empty($namespace))
				$ns = Namespace::getCanonicalIndex( strtolower( $namespace ) );
			else
				$ns = NS_MAIN;
		}
		// If a category is specified.
		if (!empty($category))
		{
			$where .= " AND {$catlinks}.cl_to = '{$category}' AND {$catlinks}.cl_from = page_id";
			$cat = ", {$catlinks}";
		}
				
		$query = "SELECT page_namespace, page_title, page_id FROM {$page} {$cat} WHERE {$page}.page_namespace = {$ns} {$where} ORDER BY {$page}.page_title {$orderDir} LIMIT {$limit}";
		$results = $dbr->query( $query );
		$count   = $dbr->numRows( $results );
		
		$namespace = Namespace::getCanonicalName($ns);
		
		if ($count>=1)
			while( $row = $dbr->fetchObject( $results ) )
				$pages[] = $namespace.':'.$row->page_title;

		$dbr->freeResult( $results );
		return $pages;
	}

} // end class	

PageAfterAndBefore::singleton();
?>