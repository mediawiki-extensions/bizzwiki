<?php
/*<!--<wikitext>-->

<!--</wikitext>-->*/
// <source lang=php>

$wgExtensionCredits[rsync::thisType][] = array( 
	'name'    => rsync::thisName,
	'version' => StubManager::getRevisionId('$Id$'),
	'author'  => 'Jean-Lou Dupont',
	'description' => "Provides page changes in an export file format.", 
);

class rsync
{
	const thisType = 'other';
	const thisName = 'rsync';
	
	const defaultDir = '_backup';
	
	var $directory; 

	/**
	 */
	public function __construct() 
	{
		// assume the default directory location
		$this->directory = self::defaultDir;
		
		// format the directory path.
		global $IP;
		$this->dir = $IP.'/'.$this->directory;
		
		rsync_operation::$dir = $this->dir;
	}
	
	/**
		Page-rcid-action-namespace.xml
	 */
	protected function generateFilename( )
	{
		if ($this->id === null)
		{
			$this->isFilenameTemp = true;
			$this->filename = tempnam( self::$dir, 'rsync_' );
			return;
		}
			
		$this->filename = self::$dir."/Page-".$this->id.'-'.$this->action.'-'.$this->ns.'.xml';
	}

	protected function rename( &$source, &$target )
	{
		$r = rename( $source, $target );
		if ($r === false)
			throw new MWException();
	}
	/**
		This function uses MediaWiki's 'WikiExporter' class.
	 */
	private function export( )
	{
		$dump = new DumpFileOutput( $this->filename );

		$db = wfGetDB( DB_SLAVE );
		$exporter = new WikiExporterEx( $db, $this->history );
		
		// used for 'move' operation
		if (!empty( $this->sourceTitle ))
			$exporter->setSourceTitle( $this->sourceTitle );
		
		$exporter->setOutputSink( $dump );
		$exporter->includeRevision( $this->includeRevision );

		$exporter->openStream();
		
		$title = Title::makeTitle( $this->ns, $this->titre );
		if( is_null( $title ) ) return;

		$exporter->setPageTitle( $title );
		
		$exporter->pageByTitle( $title );
		
		$exporter->closeStream();
	}
	
} // end class





/**
	Class definition can be found in includes/Export.php
 */
class XmlDumpWriterEx extends XmlDumpWriter
{
	var $pageTitle;
	var $sourceTitle;
	var $includeRevision;
	
	function openPage( $row )
	{
		$out = parent::openPage( $row );

		if ($this->pageTitle instanceof Title)
		{
			$this->pageTitle->loadRestrictions();
			if (!empty( $this->pageTitle->mRestrictions ))
				$out .= $this->getRestrictionsSection(	$this->pageTitle->mRestrictions, 
														$this->pageTitle->mRestrictionsExpiry,
														$this->pageTitle->mCascadeRestriction
														 );
		}
		
		if (is_a( $this->sourceTitle, 'Title'))
			$out .= $this->getSourceTitleSection();
			
		return $out;
	}
	function getRestrictionsSection( &$restrictions, $expiry, $cascading )
	{
		$result = "<restrictions>\n";
		foreach( $restrictions as $restrictionType => &$levels )
			foreach( $levels as $level)
				$result .= "    <restriction type='".$restrictionType."' level='".$level.
							"' expiry='".$expiry."' cascading='".$cascading."' />\n";

		$result .= "</restrictions>\n";
		
		return $result;
	}
	function getSourceTitleSection()
	{
		return "<source_title>".Namespace::getCanonicalName( $this->sourceTitle->getNamespace() ).
				":".$this->sourceTitle->getText()."</source_title>\n";	
	}
	function writeRevision( &$row )
	{
		if (!$this->includeRevision)
			return null;
		return parent::writeRevision( $row );
	}
} // end class

class WikiExporterEx extends WikiExporter
{
	public function __construct( &$db, $history = WikiExporter::CURRENT,
			$buffer = WikiExporter::BUFFER, $text = WikiExporter::TEXT )	
	{
		parent::__construct( $db, $history, $buffer, $text );	
		$this->writer = new XmlDumpWriterEx();
		$this->writer->includeRevision = true;
	}			
	public function setPageTitle( &$title )	
	{
		$this->writer->pageTitle = $title;	
	}
	/**
		The source title is used in 'move' operations.
	 */
	public function setSourceTitle( &$title )
	{
		$this->writer->sourceTitle = $title;	
	}
	/**
		It is helpful not to include the full revision text sometimes.
	 */
	public function includeRevision( &$enable )
	{
		$this->writer->includeRevision = $enable;
	}
} // end class

//</source>
