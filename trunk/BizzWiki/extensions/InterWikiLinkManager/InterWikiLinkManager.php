<?php
/**
 * @author Jean-Lou Dupont
 * @package InterWikiLinkManager
 */
//<source lang=php>*/
StubManager::createStub(	'InterWikiLinkManager', 
							dirname(__FILE__).'/InterWikiLinkManager.body.php',
							null,
							array( 'ArticleSave', 'EditFormPreloadText' ),
							false,					// no need for logging support
							null,					// no tags
							array('iwl'),			// 1 parser functions
							null,					// no magic words
							array( NS_MEDIAWIKI )	// namespace triggering
						 );
//</source>
