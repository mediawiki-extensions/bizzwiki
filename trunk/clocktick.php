<?php
/**
	clocktick.php
	@author: Jean-Lou Dupont
	$Id$
	
    Purpose: Execute this script with a cron/shell command to initiate a 'ClockTickEvent' hook chain 
    ======== execution in a Mediawiki installation.
	 
             The main purpose of this script is to provide a regular timebase 
			 for functionality such as 'replication'.
			 
	Security Notes:
	===============
	- Ensure that this script can only be executed locally by effecting the relevant commands
	  in your installation e.g. 'chmod', 'chgrp' and 'chown'
	  
	INSTALLATION NOTES:
	===================
	- Put this script in the root directory of the mediawiki installation
	- Install appropriate rights management (chmod, chgrp, chown)
	- Customization the 'timebase' parameter
	
 */

require_once( './includes/WebStart.php' );

wfRunHooks('ClockTickEvent', 60 /* timebase in seconds */ );

?>