<?php
/*<!--<wikitext>-->
 <file>
  <name>ManageNamespaces.i18.php</name>
  <version>$Id$</version>
  <package>Extension.ManageNamespaces</package>
 </file>
<!--</wikitext>-->*/
//<source lang=php>

global $logManageNamespaces;
$logManageNamespaces = 'mngns';	

global $msgManageNamespaces;
$msgManageNamespaces['en'] = array(
// en section

	// LOG related
'mngns' 					=> 'Manage Namespaces',
'mngns'.'logpage' 			=> 'Managed Namespaces Log',
'mngns'.'logpagetext'		=> 'This is a log of changes related to the managed namespaces',
'mngns'.'logentry'			=> '',
'mngns'.'-updtok-entry'		=> "Update OK",
'mngns'.'-updtfail1-entry'	=> "Update Failure",
'mngns'.'-updtfail2-entry'	=> "Update Failure",
'mngns'.'-updtfail3-entry'	=> "Update OK",

'mngns'.'-updtok-text'		=> "The namespace definition file was successfully updated.",
'mngns'.'-updtfail1-text'	=> "The template file couldn't be loaded.",
'mngns'.'-updtfail2-text'	=> "The namespace definition file couldn't be written.",
'mngns'.'-updtfail3-text'	=> "Nothing to update.",

'managenamespaces'.'-incorrect-page' 	=> 'The parser function <b>#mns</b> can not be used on this page.<br/>',
'managenamespaces'.'-insufficient-right'=> 'Insufficient right to execute the parser function <b>#mns</b>.<br/>',

'managenamespaces'.'-invalid-index'		=> 'Invalid namespace index <b>$1</b> (immutable)',
'managenamespaces'.'-invalid-name'		=> 'Invalid namespace name <b>$1</b> (immutable)',
'managenamespaces'.'-invalid-identifier'=> 'Invalid namespace identifier <b>$1</b> (immutable)',

'managenamespaces'.'-invalid-index-2'	=> 'Invalid namespace index <b>$1</b> (already defined)',
'managenamespaces'.'-invalid-name-2'	=> 'Invalid namespace name <b>$1</b> (already defined)',
'managenamespaces'.'-invalid-identifier-2'=> 'Invalid namespace identifier <b>$1</b> (already defined)',

'managenamespaces'.'-open-code'			=> '$bwManagedNamespaces = array('."\n",
'managenamespaces'.'-entry-code'		=> "'".'$1'."' => '".'$2'."',\n",
'managenamespaces'.'-close-code'		=> ');'."\n",

'managenamespaces'.'-open-code2'		=> '$bwManagedNamespacesDefines = array('."\n",
'managenamespaces'.'-entry-code2'		=> "'".'$1'."' => '".'$2'."',\n",
'managenamespaces'.'-close-code2'		=> ');'."\n",

#'managenamespaces'.'' => '',
#'' => '',
);
//</source>