/**
 * Name: cs-integration
 * 
 * Descripton: Script to reveal a calendar event details when a '...' button is pressed
 * 
 * @author		Alwyn Barry
 * @copyright	2025
 * For use in	cs-integration
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @version		1.0.1
 * 
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License as published by the Free Software Foundation; either version 2 of the License, 
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 */
 
/**
 *  PRIVATE: Utility function used to ensure that we are not adding a class multiple times.
 */
function cs_addClass(obj, item) {
	const itemWithSpace = ' ' + item;
	if ((obj !== null) && (obj.className.indexOf(item) == -1)) {
		obj.className += itemWithSpace;
	}
}

/**
 * PRIVATE: Utility function used to enable safe removal of a class
 * 		If the class name provided doesn't exist in the list, no change
 * 		is made to the class list.  Otherwise the class is removed.
 * 
 * @param obj	the DOM object to remove the class from its class list
 * @param item	the class name string to be removed from the class list
 */
function cs_removeClass(obj, item) {
	const itemWithSpace = ' ' + item;
	if (obj !== null) {
		obj.className = obj.className.replace(itemWithSpace, "");
	}
}

/**
 * PRIVATE: Utility function to close all open pop-ups so only one is open at a time.
 */
function cs_closeAllEventDetails() {
	let popUps = document.getElementsByClassName("cs-event-hover-reveal"); /* Fetch the array of PopUp Event Details */
	for (i=0; i<popUps.length; i++) {
		cs_removeClass(popUps[i], "cs-event-hover-reveal");
	}
	/* Should only be called when the focus is about to be moved to a new pop-up, so no need to change the focus here */
}

/**
 * Open the modal pop-up for the details of the calendar event
 * 
 * @param obj	should be the button to open the pop up, contained within the cs-event-hover_block
 */
function cs_revealEventDetails(obj) {
	/* Close all open Event Details ... should be 0 or 1 */
	cs_closeAllEventDetails()
	/* Open the details part of an event when the '...' button is clicked */
	let parent = obj.parentNode; /* Fetch the 'cs-calendar-event' div, which contains the cs-event-hover-block */
	if (parent !== null) {
		let hover = parent.getElementsByClassName("cs-event-hover-block")
		if (hover.length > 0) {
			cs_addClass(hover[0], "cs-event-hover-reveal");
			/* Set the focus so that screen-readers go to the right place */
			hover[0].focus();
		}
	}
}


/**
 * Close the details part of an event when the 'x' button is clicked
 * 
 * @param obj	should the the button within the popup (cs-event-hover-block)
 */
function cs_hideEventDetails(obj) {
	let parent = obj.parentNode; /* Fetch the 'cs-event-hover-block' div */
	if (parent !== null) {
		cs_removeClass(parent, "cs-event-hover-reveal");
		parent = parent.parentNode; /* Fetch the 'cs-calendar-event' div which contains the event these details belong to */
		/* Take the focus back to the parent to help screen-readers */
		if (parent !== null) { parent[0].focus(); }
	}
}

