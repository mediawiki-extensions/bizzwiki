<?php
/*<!--<wikitext>-->
Internationalisation file for [[Extension:ExtensionManager]] extension.

Version: $Id$
<!--</wikitext>-->*/
//<source lang=php>

// register the actions for the 'logging' facility.
NamespaceManagers::addLog( $a = array( 'extlog' => array( 'installok', 'installfail' ) ));

// register the messages
NamespaceManagers::addMessages( $a['en'] = array(
// Logging related messages
'extlog'				=> 'Extension Manager Log',
'extlog'.'logpage'		=> 'Extension Manager Log',
'extlog'.'logpagetext'	=> 'This is a log for the [[Extension:ExtensionManager|Extension Manager]]',
#'extlog'.''	=> '',

// Other messages.
'extensionmanager'								=> '<b>ExtensionManager: </b>',

'extensionmanager'.'-missing-namespace'			=> '(<i> Extension namespace missing </i>)',

'extensionmanager'.'-permissionerror-title'		=> 'Permission Error',
'extensionmanager'.'-permissionerror-subtitle'	=> 'Page: $1',
'extensionmanager'.'-permissionerror-read'		=> 'You are missing the "read" permission to access this page.',

'extensionmanager'.'-error-loadingrepo'			=> 'error loading from repository type <i>$1</i> and project <i>$2</i>.',

'extensionmanager'.'-missing-extensiondirectory' => '(<i> Extension directory missing </i>)',

'extensionmanager'.'file-entry-withfilesystem' 		=> '* File $1',
'extensionmanager'.'file-entry-withoutfilesystem'	=> '* File [[Filesystem:$1]]',

'extensionmanager'.'list-header' 					=>	"This file is managed by [[Extension:ExtensionManager]]\n".
														"*** DO NOT MODIFY MANUALLY ***\n\n",
#'extensionmanager'.'' => '',
#'' => '',
) );
//</source>