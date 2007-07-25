/*
 * 
 * DEPENDENCIES:
 * 1) YAHOO yui library
 *    YAHOO.log
 *    YAHOO event
 */

YAHOO.namespace('BIZZWIKI');

/**
 *  
 */
function var_dump(obj) 
{
   if(typeof obj == "object") 
   {
      return "Type: "+typeof(obj)+((obj.constructor) ? "\nConstructor: "+obj.constructor : "")+"\nValue: " + obj;
   } 
   else 
   {
      return "Type: "+typeof(obj)+"\nValue: "+obj;
   }
}//end function var_dump

YAHOO.BIZZWIKI = function()
{
	this.version = '$Id$';
	this.events  = [];
};

YAHOO.BIZZWIKI.prototype =
{
	createEvent: function( event )
	{
		// make sure we only define one handler per event type
		if ( this.events[event] !== undefined )
			return this.events[event];
		
		this.events[event] = new YAHOO.util.CustomEvent( event, o );
		
		return this.events[event];
	},	

	subscribeEvent: function( event, c )
	{
		if ( this.events[event] === undefined )
			throw new Error( "BIZZWIKI::subscribeEvent: Event '"+event+"' not defined." );
			
		var dispatcher = this.events[event];
		dispatcher.subscribe( c );
	},

	fireEvent: function( event, params )
	{
		if (this.events[event] === undefined )
			throw new Error( "BIZZWIKI::fireEvent: undefined event '"+event+"'.");
	
		var dispatcher = this.events[event];
		dispatcher.fire( params );
	}


};

var BIZZWIKI = new YAHOO.BIZZWIKI;
