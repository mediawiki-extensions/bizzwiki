<?php
/*
 * addHTML.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 * $Id$
 * $LastChangedRevision: 206 $
 *
 * Purpose:  Inserts <html> section(s) in the output page.
 *
 * Features:
 * *********
 * - Security: only page protected on edit with 'sysop' restriction
 *             can use this extension.
 *
 * <addhtml id=xyz /> : meant to be used in conjunction
 *                      with some PHP code using the 'addHtml' method
 *                      of this class. The 'tag' is used to position 
 *                      the HTML code in the page.
 *                      An extension which can execute PHP code, such
 *                      as 'Runphp page' can be used to prepare the
 *                      HTML code and inserts through the 'addHtml'
 *                      method.
 *
 * <addhtml [id=xyz] > html code </addhtml>
 *
 * USAGE NOTES:
 * ============
 * When parser caching is used, one should use the 'ParserCacheControl'
 * extension to reap the full benefits of this extension.
 *
 * DEPENDANCY:  ExtensionClass >= v1.1
 * 
 * Tested Compatibility:  MW 1.8.2, 1.9.3
 *
 * History:
 * - v1.0
 * - v1.1  : changed hook method for better parser cache integration.
 * - v1.2  : fixed hook chain in 'ParserAfterTidy'
 * - v1.21 : - added check for 'ExtensionClass' availability.
 *           - minor edits, no functional level changes.
 =========== Moved to BizzWiki SVN
 
 */

// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: AddHtml extension will not work!';	
else
{
	require('addHTMLclass.php');
	addHTMLclass::singleton();
}
?>