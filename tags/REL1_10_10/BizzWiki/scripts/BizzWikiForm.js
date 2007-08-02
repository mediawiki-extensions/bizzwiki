/*<wikitext>
{| border=1
| <b>File</b> || BizzWikiForm.js
|-
| <b>Revision</b> || $Id$
|-
| <b>Author</b> || Jean-Lou Dupont
|}<br/><br/>
 
== Purpose==


== Features ==


== Dependancy ==
* Yahoo 'yui' library

== History ==

== Code ==
</wikitext>*/

/*
 * <input class='yuibutton' .. /> 
 *   onClick ...
 * 
 * 
 */

bwFormManagerClass = function( name )
{
	var isInit = false; 
	// wait for the DOM to be ready
	// and call init method
	

};

bwFormManagerClass.prototype =
{
	/**
	 *  This method initializes all the relevant DOM objects found
	 */
	init: function()
	{
		// let's deal with concurrency.
		if ( this.isInit ) return;
		this.isInit = true;
		
		// scan for elements
		
		// 
	},

	/**
	 *  This method finds and returns all relevant elements
	 */
	getElements: function()
	{

	},
	
	/**
	 *  This method sets the DOM elements with an corresponding
	 *  'handling' class.
	 */
	setElements: function()
	{
		
	},
	/**
	 *  This method enables the additional registration of
	 *  element classes.
	 */
	registerClass: function()
	{

	},
	
}; // end class declaration


var bwFormManager = new bwFormManagerClass;
YAHOO.util.Event.onDOMReady( function(){ bwFormManager.init(); } ); 