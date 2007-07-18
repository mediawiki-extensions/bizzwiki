<?php
/*<wikitext>
{| border=1
| <b>File</b> || TaskScheduler.php
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==
The purpose of this extension is to provide flexible scheduling services to 'job' related extensions. 
The standard MW only provides 'FIFO' scheduling with a rudimentary queue draining function. On the contrary,
this extension provides a 'calendar-based' scheduler. Standard MW 'jobs' can be scheduled.

== Features ==

== Dependancy ==
* [[Extension:ExtensionClass|ExtensionClass]]

== Installation ==
To install independantly from BizzWiki:
* Download 'ExtensionClass' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/ExtensionClass.php');
require('extensions/TaskScheduler/TaskScheduler.php');
</source>

== History ==

== Code ==
</wikitext>*/

?>