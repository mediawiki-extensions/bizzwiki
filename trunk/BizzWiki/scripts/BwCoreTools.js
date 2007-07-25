/*
 * 
 * DEPENDENCIES:
 * 1) YAHOO yui library
 *    YAHOO.log
 *    YAHOO event
 */


/**

 */
if (typeof BIZZWIKI == "undefined") 
    var BIZZWIKI = {};

/**
 * BIZZWIKI global environment variable
 * Purpose: used to keep track of all the other modules
 * 
 */
BIZZWIKI.env = BIZZWIKI.env || 
{
	version : 	'$Id$',
	modules: 	[],
	listeners: 	[],
	events:		[]
};

BIZZWIKI.namespace = function() 
{
    var a=arguments, o=null, i, j, d;
    for (i=0; i<a.length; i=i+1) {
        d=a[i].split(".");
        o=BIZZWIKI;

        // BIZZWIKI is implied, so it is ignored if it is included
        for (j=(d[0] == "BIZZWIKI") ? 1 : 0; j<d.length; j=j+1) {
            o[d[j]]=o[d[j]] || {};
            o=o[d[j]];
        }
    }

    return o;
};


BIZZWIKI.init = function() 
{
    this.namespace( "util" );
	
    if (typeof BIZZWIKI_config != "undefined") {
        var l=BIZZWIKI_config.listener, ls=BIZZWIKI.env.listeners, unique=true, i;
        if (l) {
            for (i=0;i<ls.length;i=i+1) {
                if (ls[i]==l) {
                    unique=false;
                    break;
                }
            }
            if (unique) {
                ls.push(l);
            }
        }
    }
};

BIZZWIKI.register = function(name, mainClass, data) 
{
    var mods = BIZZWIKI.env.modules;
    if (!mods[name]) {
        mods[name] = { versions:[], builds:[] };
    }
    var m=mods[name],
		v=data.version,
		b=data.build,
		ls=BIZZWIKI.env.listeners;
    m.name = name;
    m.version = v;
    m.build = b;
    m.versions.push(v);
    m.builds.push(b);
    m.mainClass = mainClass;
	
    // fire the module load listeners
    for (var i=0;i<ls.length;i=i+1) {
        ls[i](m);
    }
    // label the main class
    if (mainClass) {
        mainClass.VERSION = v;
        mainClass.BUILD = b;
    } else {
        BIZZWIKI.log("mainClass is undefined for module " + name, "warn");
    }
};

/**
 * DEPENDENCY:  Yahoo yui
 * @param {Object} msg
 * @param {Object} cat
 * @param {Object} src
 */
BIZZWIKI.log = function(msg, cat, src) {
    var l=YAHOO.widget.Logger;
    if(l && l.log) {
        return l.log(msg, cat, src);
    } else {
        return false;
    }
};

/**
 *  Holds the BIZZWIKI environment variables.
 * 
 */
BIZZWIKI.env = BIZZWIKI.env || 
{
    modules: [],
    listeners: [],

    getVersion: function(name) {
        return BIZZWIKI.env.modules[name] || null;
    }
};

BIZZWIKI.createEvent = function( event, o )
{
	var e = BIZZWIKI.env.events;
	
	// make sure we only define one handler per event type
	if ( e[event] !== undefined )
		return e[event];
	
	e[event] = new YAHOO.util.CustomEvent( event, o );
	
	return e[event];
};

BIZZWIKI.subscribeEvent = function( event, c )
{
	var e = BIZZWIKI.env.events;
	
	if ( e[event] === undefined )
		throw new Error( "BIZZWIKI::subscribeEvent: Event '"+event+"' not defined." );
		
	var dispatcher = e[event];
	dispatcher.subscribe( c );
};

BIZZWIKI.fireEvent = function( event, params )
{
	var e = BIZZWIKI.env.events;
	
	if (e[event] === undefined )
		throw new Error('BIZZWIKI::fireEvent: undefined event.');

	var dispatcher = e[event];
	dispatcher.fire( params );
};
BIZZWIKI.init();

BIZZWIKI.register("bizzwiki", BIZZWIKI, {version: "1.0.0", build: "$Id$"});
