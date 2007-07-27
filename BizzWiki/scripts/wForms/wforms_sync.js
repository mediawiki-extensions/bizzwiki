// wForms - a javascript extension to web forms.
// Sync Behavior
// v2.0 beta - April 23rd 2006
// This software is licensed under the CC-GNU LGPL <http://creativecommons.org/licenses/LGPL/2.1/>
// Thanks to recidive.com.

 if(wFORMS) {

		// Component properties 
		wFORMS.classNamePrefix_sync	      = "sync";
		wFORMS.classNamePrefix_target     = "target";
		wFORMS.className_syncIsOn	      = "sncIsOn";   // used to keep track of the sync state on buttons and links (where the checked attribute is not available)
		wFORMS.className_syncIsOff	      = "sncIsOff";
		wFORMS.syncScopeRootTag		      = "";     	 // deprecated.	
		
		wFORMS.syncTriggers               = {};	 	     // associative multi-dimensional array (syncname->element Ids)
		wFORMS.syncTargets                = {};		     // associative multi-dimensional array (syncname->element Ids)
		
	
		wFORMS.behaviors['sync'] = {
		   
		   // ------------------------------------------------------------------------------------------
		   // evaluate: check if the behavior applies to the given node. Adds event handlers if appropriate
		   // ------------------------------------------------------------------------------------------
		   evaluate: function(node) {
               
			    // Handle Sync Triggers
				// add event handles and populate the wFORMS.syncTriggers 
				// associative array (syncname->element Ids)
				// ------------------------------------------------------------------------------------------				
				if (wFORMS.helpers.hasClassPrefix(node, wFORMS.classNamePrefix_sync)) {

					if(!node.id) node.id = wFORMS.helpers.randomId();
					
					//wFORMS.debug('sync/evaluate: '+ node.className + ' ' + node.tagName);
					
					// Go through each class (one element can have more than one sync trigger).
					var syncNames = wFORMS.behaviors['sync'].getSyncNames(node);
					for(var i=0; i < syncNames.length; i++) {
						if(!wFORMS.syncTriggers[syncNames[i]]) 
							wFORMS.syncTriggers[syncNames[i]] = new Array();
						if(!wFORMS.syncTriggers[syncNames[i]][node.id]) 
							wFORMS.syncTriggers[syncNames[i]].push(node.id);
						//wFORMS.debug('sync/evaluate: [trigger] '+ syncNames[i],3);
					}

					switch(node.tagName.toUpperCase()) {
							
						case "OPTION":
							// get the SELECT element
							var selectNode = node.parentNode;
							while(selectNode && selectNode.tagName.toUpperCase() != 'SELECT') {
								var selectNode = selectNode.parentNode;
							}
							if(!selectNode) { alert('Error: invalid markup in SELECT field ?'); return false;  } // invalid markup
							if(!selectNode.id) selectNode.id = wFORMS.helpers.randomId();

							// Make sure we have only one event handler for the select.
							if(!selectNode.getAttribute('rel') || selectNode.getAttribute('rel').indexOf('wfHandled')==-1) {
								//wFORMS.debug('sync/add event: '+ selectNode.className + ' ' + selectNode.tagName);
								selectNode.setAttribute('rel', (selectNode.getAttribute('rel')||"") + ' wfHandled');
								wFORMS.helpers.addEvent(selectNode, 'change', wFORMS.behaviors['sync'].run);
							}							
							break;

						case "INPUT":							
							if(node.type && node.type.toLowerCase() == 'radio') {
								// Add the onclick event on radio inputs of the same group
								var formElement = node.form;	
								for (var j=0; j<formElement[node.name].length; j++) {
									var radioNode = formElement[node.name][j];
									// prevents conflicts with elements with an id = name of radio group
									if(radioNode.type.toLowerCase() == 'radio') {
										// Make sure we have only one event handler for this radio input.
										if(!radioNode.getAttribute('rel') || radioNode.getAttribute('rel').indexOf('wfHandled')==-1) {								
											wFORMS.helpers.addEvent(radioNode, 'click', wFORMS.behaviors['sync'].run);
											// flag the node 
											radioNode.setAttribute('rel', (radioNode.getAttribute('rel')||"") + ' wfHandled');
										} 
									}
								}
							} else if(node.type && node.type.toLowerCase() == 'text') {
								wFORMS.helpers.addEvent(node, 'keyup', wFORMS.behaviors['sync'].run);
							} else {
								wFORMS.helpers.addEvent(node, 'click', wFORMS.behaviors['sync'].run);
							}
							break;
						case "TEXTAREA":
							wFORMS.helpers.addEvent(node, 'keyup', wFORMS.behaviors['sync'].run);
							break;
						default:		
							wFORMS.helpers.addEvent(node, 'click', wFORMS.behaviors['sync'].run);
							break;
					}
				}
				
				// Push targets in the wFORMS.syncTargets array 
				// (associative array with syncname -> element ids)
				// ------------------------------------------------------------------------------------------
				if (wFORMS.helpers.hasClassPrefix(node, wFORMS.classNamePrefix_target)) {
					
					if(!node.id) node.id = wFORMS.helpers.randomId();
					
					// Go through each class (one element can be the target of more than one sync).
					var syncNames = wFORMS.behaviors['sync'].getSyncNames(node);
					
					for(var i=0; i < syncNames.length; i++) {
						if(!wFORMS.syncTargets[syncNames[i]]) 
							wFORMS.syncTargets[syncNames[i]] = new Array();
						wFORMS.syncTargets[syncNames[i]].push(node.id);
						//wFORMS.debug('sync/evaluate: [target] '+ syncNames[i],3);
					}										
				}
				
				if(node.tagName && node.tagName.toUpperCase()=='FORM') {
					// function to be called when all behaviors for this form have been applied
					//wFORMS.debug('sync/push init');
					wFORMS.onLoadComplete.push(wFORMS.behaviors['sync'].init); 
				}
           },
		   
		   // ------------------------------------------------------------------------------------------
           // init: executed once evaluate has been applied to all elements
		   // ------------------------------------------------------------------------------------------	   
		   init: function() {
			   // go through all sync triggers and activate those who are already ON
			   //wFORMS.debug('sync/init: '+ (wFORMS.syncTriggers.length));
			   for(var syncName in wFORMS.syncTriggers) {
					// go through all triggers for the current sync
					for(var i=0; i< wFORMS.syncTriggers[syncName].length; i++) {		   
					   	var element = document.getElementById(wFORMS.syncTriggers[syncName][i]);
						//wFORMS.debug('sync/init: ' + element + ' ' + syncName , 5);	
					   	if(wFORMS.behaviors['sync'].isTriggerOn(element,syncName)) {
							// if it's a select option, get the select element
							if(element.tagName.toUpperCase()=='OPTION') {
								var element = element.parentNode;
								while(element && element.tagName.toUpperCase() != 'SELECT') {
									var element = element.parentNode;
								}
							}
							// run the trigger
							wFORMS.behaviors['sync'].run(element);
						}
				   }
			   }
		   },
		   
		   // ------------------------------------------------------------------------------------------
           // run: executed when the behavior is activated
		   // ------------------------------------------------------------------------------------------	   
           run: function(e) {
                var element   = wFORMS.helpers.getSourceElement(e);
				if(!element) element = e;
			    //wFORMS.debug('sync/run: ' + element.id , 5);	

				var syncs  = new Array();
				// Get list of triggered syncs
				switch(element.tagName.toUpperCase()) {
					case 'SELECT':
						for(var i=0;i<element.options.length;i++) {
							syncs = syncs.concat(wFORMS.behaviors['sync'].getSyncNames(element.options[i]));
						}
						break;
					case 'INPUT':
						if(element.type.toLowerCase() == 'radio') {
							// Go through the radio group.
							for(var i=0;i <element.form[element.name].length;i++) { 
								var radioElement = element.form[element.name][i];
								syncs = syncs.concat(wFORMS.behaviors['sync'].getSyncNames(radioElement));
							}
						} else {
							syncs = syncs.concat(wFORMS.behaviors['sync'].getSyncNames(element));
						}
						break;
					default:
						break;
				}

				// Do sync
				for(var i=0; i < syncs.length; i++) {
					var elements = wFORMS.behaviors['sync'].getTargetsBySyncName(syncs[i]);
					for(var j=0;j<elements.length;j++) {
						// An element with the REPEAT behavior limits the scope of syncs 
						// targets outside of the scope of the sync are not affected. 
						if(wFORMS.behaviors['sync'].isWithinSyncScope(element, elements[j])) {
							wFORMS.behaviors['sync'].sync(element, elements[j], syncs[i]);
							//wFORMS.debug('sync/run: [turn on ' + syncs_ON[i] + '] ' + elements[j].id , 3);	
						}
					}
				}
           },

		   // ------------------------------------------------------------------------------------------
           // remove: executed if the behavior should not be applied anymore
		   // ------------------------------------------------------------------------------------------
		   remove: function(e) {
               var element   = wFORMS.helpers.getSourceElement(e);
			  //wFORMS.debug('sync/remove: ' + element.id , 5);				   
           },
		   
		   
		   // ------------------------------------------------------------------------------------------
		   // Get the list of syncs 
		   // Note: potential conflict if an element is both a sync and a target.
		   getSyncNames: function(element) {
				var syncNames = new Array();
				var classNames  = element.className.split(' ');
				for(var i=0; i < classNames.length; i++) {
					// Note: Might be worth keeping a prefix on syncName to prevent collision with reserved names						
					if(classNames[i].indexOf(wFORMS.classNamePrefix_sync) == 0) {
						syncNames.push(classNames[i].substr(wFORMS.classNamePrefix_sync.length+1));
					}
					if(classNames[i].indexOf(wFORMS.classNamePrefix_target) == 0) {
						syncNames.push(classNames[i].substr(wFORMS.classNamePrefix_target.length+1));
					}
				}
				return syncNames;
			},

			// ------------------------------------------------------------------------------------------
			sync: function(source, target, syncName) {
				if(!source || source.nodeType != 1) return;
				if(!target || target.nodeType != 1) return;
				var value, state;
				// read in source data
				switch(source.tagName.toUpperCase()) {
					case 'SELECT':
						// get de value of the select, I think we need to change
						// this to get the value of the source option
						value = source.options[source.selectedIndex].value;
						state = wFORMS.helpers.hasClass(source.options[source.selectedIndex], wFORMS.classNamePrefix_sync + '-' + syncName);
						break;
					case 'INPUT':
						value = source.value;
						if(source.type.toLowerCase() == 'radio' || source.type.toLowerCase() == 'checkbox') {
							state = source.checked;
						} else {
							state = (value.lenght > 0);
						}
						break;
					default:
						value = source.innerHTML;
						state = (value.lenght > 0);
						break;
				}

				// sync up the target
				switch(target.tagName.toUpperCase()) {
					case 'OPTION':
						wFORMS.behaviors['sync'].syncState(target, state);
						break;
					case 'INPUT':
						if(target.type.toLowerCase() == 'radio' || target.type.toLowerCase() == 'checkbox') {
							wFORMS.behaviors['sync'].syncState(target, state);
						} else {
							wFORMS.behaviors['sync'].syncValue(target, value);
						}
						break;
					default:
						wFORMS.behaviors['sync'].syncValue(target, value);
						break;
				}

				// For  elements that don't have a native state variable (like checked, or selectedIndex)
				if(wFORMS.helpers.hasClass(target, wFORMS.className_syncIsOff)) {
					element.className = target.className.replace(wFORMS.className_syncIsOff, wFORMS.className_syncIsOn);
				} else if(wFORMS.helpers.hasClass(target, wFORMS.className_syncIsOn)) {
					element.className = target.className.replace(wFORMS.className_syncIsOn, wFORMS.className_syncIsOff);
				}
			},

			// ------------------------------------------------------------------------------------------
			syncState: function(element, state) {
				if(element.tagName.toUpperCase() == 'OPTION') {
					element.selected = state;
				} else {
					element.checked = state;
				}
			},

			// ------------------------------------------------------------------------------------------
			syncValue: function(element, value) {		
				if(element.tagName.toUpperCase() == 'INPUT') {
					element.value = value;
				} else {
					element.innerHTML = value;
				}
			},
			
			// ------------------------------------------------------------------------------------------
			getTargetsBySyncName: function(syncName) {
				var elements = new Array();
				if(wFORMS.syncTargets[syncName]) {
					for (var i=0; i<wFORMS.syncTargets[syncName].length; i++) {
						var element = document.getElementById(wFORMS.syncTargets[syncName][i]);
						if(element)
							elements.push(element);
					}
				}
				return elements;
			},
			
			// ------------------------------------------------------------------------------------------
			isTriggerOn: function(element, triggerName) {
				if(!element) return false;
				if(element.tagName.toUpperCase()=='OPTION') {
					var selectElement = element.parentNode;
					while(selectElement && selectElement.tagName.toUpperCase() != 'SELECT') {
						var selectElement = selectElement.parentNode;
					}
					if(!selectElement) return false; // invalid markup					
					if(selectElement.selectedIndex==-1) return false; // nothing selected
					// TODO: handle multiple-select
					if(wFORMS.helpers.hasClass(selectElement.options[selectElement.selectedIndex],
											   wFORMS.classNamePrefix_sync + '-' + triggerName)) {
						return true;
					}
				} else {
					if(element.checked || wFORMS.helpers.hasClass(element, wFORMS.className_syncIsOn)) 
						return true;
				}
				return false;
			},
			
			// isWithinSyncScope: An element with the REPEAT behavior limits the scope of syncs 
			// targets outside of the scope of the sync are not affected. 
			// ------------------------------------------------------------------------------------------			
			isWithinSyncScope: function(trigger, target) {
				if(wFORMS.hasBehavior('repeat') && wFORMS.limitSyncScope == true) { 
					// check if the trigger is in a repeatable/removeable element
					var scope = trigger;
				
					while(scope && scope.tagName && scope.tagName.toUpperCase() != 'FORM' && 
						  !wFORMS.helpers.hasClass(scope, wFORMS.className_repeat) &&
					      !wFORMS.helpers.hasClass(scope, wFORMS.className_delete) ) {
						scope = scope.parentNode;
					}
					if(wFORMS.helpers.hasClass(scope, wFORMS.className_repeat) || 
					   wFORMS.helpers.hasClass(scope, wFORMS.className_delete)) {
						// yes, the trigger is nested in a repeat/remove element
						
						// check if the target is in the same element.
						var scope2 = target;
						while(scope2 && scope2.tagName && scope2.tagName.toUpperCase() != 'FORM' && 
							  !wFORMS.helpers.hasClass(scope2, wFORMS.className_repeat) &&
							  !wFORMS.helpers.hasClass(scope2, wFORMS.className_delete) ) {
							scope2 = scope2.parentNode;
						}
						if(scope == scope2) {
							return true;  // target & trigger are in the same repeat/remove element		
						} else {
							return false; // target not in the same repeat/remove element,					
						}
					} else {
						return true;	  // trigger is not nested in a repeat/remove element, scope unaffected
					}
				} else 
					return true;
			}
       } // END wFORMS.behaviors['sync'] object

  	   
   }