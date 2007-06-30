<?php
/*

 */
// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: FormProc extension will not work!';	
else
{
	require( "FormProcClass.php" );
	FormProcClass::singleton();
}
?>