/*
 * BwDocumentTools
 * $Id$
 * Jean-Lou Dupont
 * 
 * == Purpose ==
 * - Provides registration services for element class proxies
 * - Provides automatic discovery of registered elements
 * - Provides automatic addition of event handlers
 * 
 * == Dependencies ==
 * - yui 'element' class 
 * 
 * == Usage ==
 * 1) Be sure to include the proxy classes *after* this file.
 * 
 * == History ==
 * 
 */

YAHOO.namespace('BIZZWIKI.DocElements');

/**
 *  Constructor
 */
YAHOO.BIZZWIKI.DocElements = function()
{
	var es;
	var classes	= [];
};

YAHOO.BIZZWIKI.DocElements.prototype =
{
	registerClass: function( className, implementationClassName )
	{
		this.classes[className] = implementationClassName;
	},
	
	init: function()
	{
		var l;
		
		// go through the list of registered classes
		// and seek elements of each class
		for (i=0; i<this.classes.length; i++)
		{
			l = YAHOO.util.Dom.getElementsByClassName( this.classes[i] );
			
		};	
	}
};

// wait for the DOM to be ready.
var bwDocElements = new YAHOO.BIZZWIKI.DocElements;
YAHOO.util.Event.onDOMReady( function(){ return bwDocElements.init(); } ); 