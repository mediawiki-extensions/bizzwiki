<?php
/*<wikitext>
{{extension:
|DocProc.php
|$Id$
|Jean-Lou Dupont
}}
 
== Purpose==
Serves to document markup/magic words whilst still executing them as per required.

== Features ==
* Documents all wikitext types by enclosing the said wikitext in either 'code' or 'pre' tags
* Executes the passed wikitext as per usual processing flow
* Secure: only predefined HTML documentation tags can be specified
** Currently, only the 'pre' and 'code' tags are supported

== Usage ==
Let's say one wants to document & still execute the following wikitext:
:<docproc tag=code>{{CURRENTTIME}}</docproc>
:Here the wikitext magic word ''CURRENTTIME'' would be executed and the result would be presented next to the 'documented' 
wikitext enclosed inside a 'code' section.

== Target Application ==
The envisaged target application for this extension is to document wikitext that produces no direct user visible results.

== Dependancy ==
* [[Extension:ExtensionClass|ExtensionClass]]

== Installation ==
To install independantly from BizzWiki:
* Download 'StubManager' extension
* Apply the following changes to 'LocalSettings.php'
<source lang=php>
require('extensions/StubManager.php');
require('extensions/DocProc/DocProc.php');
</source>

== History ==

== Code ==
</wikitext>*/

global $wgExtensionCredits;
$wgExtensionCredits[DocProcClass::thisType][] = array( 
	'name'        => DocProcClass::thisName, 
	'version'     => StubManager::getRevisionId( '$Id$' ),
	'author'      => 'Jean-Lou Dupont', 
	'description' => "Documents wikitext with 'markup/magic words' whilst still processing as per normal.",
	'url' 		=> StubManager::getFullUrl(__FILE__),			
);

class DocProcClass
{
	// constants.
	const thisName = 'DocProc';
	const thisType = 'other';
	
	static $allowedDocTags = array( 'code', 'pre' );
	static $defaultDocTag = 'code';
	
	function __construct( ) {}

	public function tag_docproc( &$text, &$params, &$parser )
	{
		$tag = @$params['tag'];
		
		// make sure the user is asking for a valid HTML tag for the documentation part.
		$docTag = (in_array($tag, self::$allowedDocTags)) ? ($tag) : (self::$defaultDocTag);		
		
		// parse the wikitext as per required as if the said text wasn't being automatically documented.
		$pt = $parser->recursiveTagParse( $text, null, $parser );
		
		return '<'.$docTag.'>'.htmlspecialchars($text).'</'.$docTag.'>'.$pt;
	}
} // end class

?>