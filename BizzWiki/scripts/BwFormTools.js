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

YAHOO.namespace('BIZZWIKI.Forms');
YAHOO.namespace('BIZZWIKI.Form');

/*********************************************************************************************/
							BIZZWIKI.Forms
/*********************************************************************************************/

/**
 *  Constructor
 */
YAHOO.BIZZWIKI.Forms = function() 
{
	this.fl = [];
};

YAHOO.BIZZWIKI.Forms.formClass = 'bwForm';

YAHOO.BIZZWIKI.Forms.prototype =
{
	init: function()
	{
		// Find all forms of the document.
		var es = YAHOO.util.Dom.getElementsByClassName(YAHOO.BIZZWIKI.Forms.formClass, 'form');
		
		// create our own supporting FormElement for each
		for( i=0; i<es.length; i++ )
			this.fl.push( new YAHOO.BIZZWIKI.Form( es[i].id ) );
	}

};

/*********************************************************************************************/
							BIZZWIKI.Form
/*********************************************************************************************/

/**
 * Constructor
 * @param {Object} el: unique DOM 'Form' element id
 * @param {Object} attr
 */
YAHOO.BIZZWIKI.Form = function( el, attr )
{
    attr = attr || {};		
	
   	YAHOO.BIZZWIKI.Form.superclass.constructor.call(this, el, attr);
};
YAHOO.BIZZWIKI.Form.prototype =
{
	

};

// Inheritance declaration
YAHOO.extend(YAHOO.BIZZWIKI.Form, YAHOO.util.Element);


/*********************************************************************************************/

// Make sure we only initialize the forms when the DOM is ready.
var bwPageForms = new YAHOO.BIZZWIKI.Forms;
YAHOO.util.Event.onDOMReady( function(){ return bwPageForms.init(); } ); 
