<?php
/*<!--<wikitext>-->
Internationalisation file for [[Extension:RepositoryManager]] extension.

Version: $Id$
<!--</wikitext>-->*/
//<source lang=php>

// register the actions for the 'logging' facility.
#NamespaceManagers::addLog( $a = array( 'extlog' => array( 'installok', 'installfail' ) ));

// register the messages
NamespaceManagers::addMessages( array(
'en' => array(

// Logging related messages

// Other messages.
'repositorymanager'								=> '<b>RepositoryManager: </b>',

'repositorymanager'.'-select-type'				=> 'Select Repository Type',
'repositorymanager'.'-enter-projectname'		=> 'Enter Project Name',

'repositorymanager'.'-missing-namespace'		=> '(<i> Repository namespace missing </i>)',

// Permission Related
'repositorymanager'.'-permissionerror-title'	=> 'Permission Error',
'repositorymanager'.'-permissionerror-subtitle'	=> 'Page: $1',
'repositorymanager'.'-permissionerror-read'		=> 'You are missing the "read" permission to access this page.',

'repositorymanager'.'-error-loadingrepo'		=> 'error loading from repository type <i>$1</i> and project <i>$2</i>.',

#'repositorymanager'.'' => '',
#'' => '',
), // end 'en'

)  
); // end 'addMessages
//</source>