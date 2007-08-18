<?php
/*<!--<wikitext>-->
Internationalisation file for [[Extension:ExtensionManager]] extension.

Version: $Id$
<!--</wikitext>-->*/
//<source lang=php>

global $msgExtensionManager;		// required for StubManager
global $logExtensionManager;		// required for StubManager

// required for StubManager. The format is important:  'log'.$classname
$logExtensionManager = 'extlog';	

$msgExtensionManager['en'] = array(
// Logging related messages
'extlog'				=> 'Extension Manager Log',
'extlog'.'logpage'		=> 'Extension Manager Log',
'extlog'.'logpagetext'	=> 'This is a log for the [[Extension:ExtensionManager|Extension Manager]]',
#'extlog'.''	=> '',

// Other messages.
'extensionmanager'								=> '<b>ExtensionManager: </b>',

'extensionmanager'.'-missing-namespace'			=> '(<i> Extension namespace missing </i>)',

'extensionmanager'.'-error-loadingrepo'			=> 'error loading from repository type <i>$1</i> and project <i>$2</i>.',

'extensionmanager'.'-missing-extensiondirectory' => '(<i> Extension directory missing </i>)',

'extensionmanager'.'file-entry-withfilesystem' 		=> '* File $1',
'extensionmanager'.'file-entry-withoutfilesystem'	=> '* File [[Filesystem:$1]]',

#'extensionmanager'.'' => '',
#'' => '',
);
//</source>