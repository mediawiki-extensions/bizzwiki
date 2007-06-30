<?php
/*

 */
// Verify if 'ExtensionClass' is present.
if ( !class_exists('ExtensionClass') )
	echo 'ExtensionClass missing: FormBuilder extension will not work!';	
else
{
	require( "FormBuilderClass.php" );
	FormBuilderClass::singleton();
}
?>