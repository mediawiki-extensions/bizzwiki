<?php
/*<!--<wikitext>-->
Internationalisation file for [[Extension:RepositoryManager]] extension.

Version: $Id$
<!--</wikitext>-->*/
//<source lang=php>

// register the actions for the 'logging' facility.
#NamespaceManagers::addLog( $a = array( 'extlog' => array( 'installok', 'installfail' ) ));

// register the messages
NamespaceManagers::addMessages( $a['en'] = array(
// Logging related messages
#'extlog'				=> 'Extension Manager Log',
#'extlog'.'logpage'		=> 'Extension Manager Log',
#'extlog'.'logpagetext'	=> 'This is a log for the [[Extension:ExtensionManager|Extension Manager]]',
#'extlog'.''	=> '',

// Other messages.
'repositorymanager'								=> '<b>RepositoryManager: </b>',

'repositorymanager'.'-select-type'				=> 'Select Repository Type',

'repositorymanager'.'-missing-namespace'		=> '(<i> Repository namespace missing </i>)',

// Permission Related
'repositorymanager'.'-permissionerror-title'	=> 'Permission Error',
'repositorymanager'.'-permissionerror-subtitle'	=> 'Page: $1',
'repositorymanager'.'-permissionerror-read'		=> 'You are missing the "read" permission to access this page.',

'repositorymanager'.'-error-loadingrepo'		=> 'error loading from repository type <i>$1</i> and project <i>$2</i>.',

#'repositorymanager'.'' => '',
#'' => '',
) );
//</source>