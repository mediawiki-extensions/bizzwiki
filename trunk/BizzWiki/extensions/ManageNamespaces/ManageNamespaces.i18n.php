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
ManageNamespaces::addMessages( array(
'en' => array(
// en section
'managenamespaces'.'-incorrect-page' 	=> 'The parser function <b>#mns</b> can not be used on this page.<br/>',
'managenamespaces'.'-insufficient-right'=> 'Insufficient right to execute the parser function <b>#mns</b>.<br/>',

'managenamespaces'					=> '<b>Manage Namespaces</b>',
'managenamespaces'.'-table-begin'	=> '<table border="1" id="mns">'.
										'<thead>'.
										 '<tr><td>Index</td><td>Canonical Name</td></tr>'.  // only this to translate
										'</thead>'.
										 '<tbody>',
'managenamespaces'.'-table-row'		=>    '<tr><td>$index</td><td>$name</td></tr>',
'managenamespaces'.'-table-end'		=>   '</tbody>'.
										'</table>',
										
'managenamespaces'.'-file-updated'		=> '',
'managenamespaces'.'-file-not-updated'	=> '',

#'managenamespaces'.'' => '',
#'' => '',
),
// fr section
# 'fr' => array(
# '' => '',
# '' => '',
#),
) );
//</source>