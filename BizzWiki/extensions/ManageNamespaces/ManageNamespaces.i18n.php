<?php
/*<!--<wikitext>-->
 <file>
  <name>ManageNamespaces.i18.php</name>
  <version>$Id$</version>
  <package>Extension.ManageNamespaces</package>
 </file>
<!--</wikitext>-->*/
//<source lang=php>

// register the messages
//ManageNamespaces::addMessages( array(
global $msgManageNamespaces;
$msgManageNamespaces['en'] = array(
// en section
'managenamespaces'.'-incorrect-page' 	=> 'The parser function <b>#mns</b> can not be used on this page.<br/>',
'managenamespaces'.'-insufficient-right'=> 'Insufficient right to execute the parser function <b>#mns</b>.<br/>',

'managenamespaces'.'-file-update-success'		=> "<br/>The namespace definition file was successfully updated.",
'managenamespaces'.'-file-write-error'			=> "<br/>The namespace definition file couldn't be written.",
'managenamespaces'.'-file-not-updated'			=> "<br/>The namespace definition file couldn't be updated.",
'managenamespaces'.'-template-file-read-error'	=> "<br/>The template file couldn't be loaded.",

'managenamespaces'.'-invalid-index'		=> 'Invalid namespace index <b>$1</b> (immutable)',
'managenamespaces'.'-invalid-name'		=> 'Invalid namespace name <b>$1</b> (immutable)',

'managenamespaces'.'-invalid-index-2'	=> 'Invalid namespace index <b>$1</b> (already defined)',
'managenamespaces'.'-invalid-name-2'	=> 'Invalid namespace name <b>$1</b> (already defined)',

'managenamespaces'.'-open-code'			=> '$bwManagedNamespaces = array('."\n",
'managenamespaces'.'-entry-code'		=> "'".'$1'."' => '".'$2'."',\n",
'managenamespaces'.'-close-code'		=> ');'."\n",
#'managenamespaces'.'' => '',
#'' => '',
);
//</source>