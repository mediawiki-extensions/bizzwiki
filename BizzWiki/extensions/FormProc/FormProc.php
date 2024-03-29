<?php
/*<!--<wikitext>-->
{{Extension
|name        = FormProc
|status      = stable
|type        = hook
|author      = [[user:jldupont|Jean-Lou Dupont]]
|image       =
|version     = See SVN ($Id$)
|update      =
|mediawiki   = tested on 1.10 but probably works with a earlier versions
|download    = [http://bizzwiki.googlecode.com/svn/trunk/BizzWiki/extensions/FormProc/ SVN]
|readme      =
|changelog   =
|description = 
|parameters  =
|rights      =
|example     =
}}
<!--@@
{{#autoredirect: Extension|{{#noext:{{SUBPAGENAME}} }} }}
== File Status ==
This section is only valid when viewing the page in a BizzWiki environment.
<code>(($#extractmtime|@@mtime@@$))  (($#extractfile|@@file@@$))</code>

Status: (($#comparemtime|<b>File system copy is newer - [{{fullurl:{{NAMESPACE}}:{{PAGENAME}}|action=reload}} Reload] </b>|Up to date$))
@@-->

== Purpose==
This extension offers the ability to process posted pages/forms through the 'action=formsubmit' action. 
The processing code resides in the database. The code can be 'syntax highlighted' through a 
<nowiki><php></nowiki> tag.

== Features ==
* Handles 'action=formsubmit' action
* Follows 'redirects'
* Executes PHP code stored in a standard Mediawiki page
* Supports code extraction when enclosed in 'PHP' tags
* Supports the definition of a class in the processor page ( $page.'Class' )
** If a method 'submit' is present in the said class, it will be called upon formsubmit action (see example)

== Example ==
=== Form Processing Page 'MyFormProc' ===
<pre> <!-- remove pre section -->
<php>
  class MyFormProcClass
  {
  	 function submit() { implement your handler here }
  }
</php>
</pre>
== Dependancies ==
* [[Extension:StubManager]] extension
* [[Extension:RunPHP Class]] extension

== Installation ==
To install independantly from BizzWiki:
* Download & Install [[Extension:StubManager]] extension
* Dowload all this extension's files and place in the desired directory
* Apply the following changes to 'LocalSettings.php' after the statements of [[Extension:StubManager]]:
<source lang=php>
require('extensions/FormProc/FormProc_stub.php');
</source>

== History ==
* added functionality to define a class for handling form processing
* removed dependency on ExtensionClass
* added stubbing capability through StubManager

== See Also ==
* [[Extension:BizzWiki|BizzWiki platform]]

== Code ==
<!--</wikitext>--><source lang=php>*/

/*
	// create stub object.
	require( $IP.'/includes/StubObject.php');
	$wgAutoloadClasses['FormHelper'] = dirname(__FILE__) . "/FormHelper.php" ;
	$bwFormHelper = new StubObject( 'bwFormHelper', 'FormHelper' );
*/

$wgExtensionCredits[FormProc::thisType][] = array( 
	'name'        => FormProc::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => 'Handles "action=formsubmit" post requests through page based PHP code',
	'url' 		=> 'http://mediawiki.org/wiki/Extension:FormProc',			
);

class FormProc
{
	// constants.
	const thisName = 'FormProc';
	const thisType = 'other';
		  
	function __construct( ) {}

	public function hUnknownAction( $action, &$article )
	{
		// check if request 'action=formsubmit'
		if ($action != 'formsubmit') return true; // continue hook-chain.

		$article->loadContent();

		// follow redirects
		if ( $article->mIsRedirect == true )
		{
			$title = Title::newFromRedirect( $article->getContent() );
			$article = new Article( $title );
			$article->loadContent();
		}
		// Extract the code
		// Use our runphpClass helper
		$runphp = new runphpClass;
		$runphp->initFromContent( $article->getContent() );	

		// Execute Code
		$code = $runphp->getCode( true ); 

		if (!empty($code))
			$callback = eval( $code );  // we might implement functionality around a callback method in the future

		// Was there an expected class defined?
		$name = $article->mTitle->getDBkey();

		// the page name might actually be a sub-page; extract the basename without the full path.
		$pn   = explode( '/', $name );
		if ( !empty( $pn ))
		{
			$rn = array_reverse( $pn );
			$name = $rn[0];
		}
		$name .= 'Class';

		if ( class_exists( $name ))
		{
			$class = new $name();
			if ( is_object( $class))
				if (method_exists( $class, 'submit' ))
					$class->submit();
		}	

		// ... then it was a page built from ground up; nothing more to do here.
		return false;
	}

} // END CLASS DEFINITION

//</source>