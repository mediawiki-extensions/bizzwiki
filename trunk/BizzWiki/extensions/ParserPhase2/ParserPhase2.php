<?php
/**
 * @author Jean-Lou Dupont
 * @package ParserPhase2
 */
//<source lang=php>*/
StubManager::createStub(	'ParserPhase2', 
							dirname(__FILE__).'/ParserPhase2.body.php',
							null,
							array( 'OutputPageBeforeHTML','ParserAfterTidy','ParserBeforeStrip' ),
							false,	// no need for logging support
							null,	// tags
							null,	// no parser functions
							null	// no magic words
						 );
//</source>