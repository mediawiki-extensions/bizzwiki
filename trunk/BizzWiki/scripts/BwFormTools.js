/*
 * BwFormTools
 * $Id$
 * Jean-Lou Dupont
 * 
 * == Dependencies ==
 * 
 * == History ==
 * 
 */

YAHOO.namespace('BIZZWIKI.FormTools');

YAHOO.BIZZWIKI.FormTools = function() 
{
	this.fl = [];
	//alert('BIZZWIKI.FormTools::constructor');
};

YAHOO.BIZZWIKI.FormTools.formClass = 'bwForm';

YAHOO.BIZZWIKI.FormTools.prototype =
{
	init: function()
	{
		// Find all forms of the document.
		var es = YAHOO.util.Dom.getElementsByClassName(YAHOO.BIZZWIKI.FormTools.formClass, 'form');
		
//		alert( var_dump( es ) );
//		alert( es.length );
		
		// create our own supporting FormElement for each
		for( i=0; i<es.length; i++ )
			this.fl.push( new YAHOO.BIZZWIKI.FormElement( es[i].id ) );
	}

};

/*********************************************************************************************/

YAHOO.BIZZWIKI.FormElement = function( el, attr )
{
	// DOM element's id
	this.el = el;
    attr = attr || {};		
	
   	YAHOO.BIZZWIKI.FormElement.superclass.constructor.call(this, el, attr);
	
	alert('BIZZWIKI.FormElement::constructor '+el );
};
	
YAHOO.extend(YAHOO.BIZZWIKI.FormElement, YAHOO.util.Element);


/*********************************************************************************************/

var bwForms = new YAHOO.BIZZWIKI.FormTools;
YAHOO.util.Event.onDOMReady( function(){ return bwForms.init(); } ); 
