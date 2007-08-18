<?php
/*<!--<wikitext>-->
{{Extension
|name        = ExtensionManager
|status      = beta
|type        = parser
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/ExtensionManager/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}

== Code ==
<!--</wikitext>--><source lang=php>*/

class ExtensionLog
{
	public static function addEntry( &$action, $param1, $param2 )
	{
		global $wgUser;
		
		$message = wfMsgForContent( 'extlog'.'-'.$action
									.'-text', $param1, $param2 );
		
		$log = new LogPage( 'extlog' );
		$log->addEntry( $action, $wgUser->getUserPage(), $message );
		
	}	
} // end class declaration.

//</source>