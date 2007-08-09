<?php
/**
 * Internationalisation file for AutoRedirect extension.
 *
 * $Id$
 * 
*/

DirectoryManager::$msg = array();

DirectoryManager::$msg['en'] = array(
'directorymanager'.'title'	=> 'Directory Manager',
'directorymanager'.'view'	=> 'View directory <i>$1</i>',
'directorymanager'.'-template'=> 
'	<filepattern>[[Filesystem:$1]]</filepattern>
	<dirpattern>[[Directory:$1]]</dirpattern>
	<linepattern>$1<br/></linepattern>

	<b>Directory Listing</b>
<br/>
',
#'directorymanager'.''=> '',
);
