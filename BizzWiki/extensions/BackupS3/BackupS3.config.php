<?php
/*
<file>
	<name>BackupS3.config.php</name>
	<id>$Id$</id>
	<package>Extension.BackupS3</package>
</file>
*/
// <source lang=php>

class BackupS3Config
{
	// there can not be any sensible default:
	// the user of this extension MUST set these.
	static $bucket_name = null;
	static $secret_key  = null;
}

//</source>