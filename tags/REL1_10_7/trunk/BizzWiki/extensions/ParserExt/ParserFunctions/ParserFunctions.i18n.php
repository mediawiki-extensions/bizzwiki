<?php

/**
 * Get translated magic words, if available
 *
 * @param string $lang Language code
 * @return array
 */
function efParserFunctionsWords( $lang ) {
	$words = array();

	/**
	 * English
	 */
	$words['en'] = array(
		'expr' 		=> array( 0, 'expr' ),
		'if' 		=> array( 0, 'if' ),
		'ifeq' 		=> array( 0, 'ifeq' ),
		'ifexpr' 	=> array( 0, 'ifexpr' ),
		'switch' 	=> array( 0, 'switch' ),
		'default' 	=> array( 0, '#default' ),
		'ifexist' 	=> array( 0, 'ifexist' ),
		'time' 		=> array( 0, 'time' ),
		'rel2abs' 	=> array( 0, 'rel2abs' ),
		'titleparts' => array( 0, 'titleparts' ),
	);

	# English is used as a fallback, and the English synonyms are
	# used if a translation has not been provided for a given word
	return ( $lang == 'en' || !isset( $words[$lang] ) )
		? $words['en']
		: array_merge( $words['en'], $words[$lang] );
}
?>