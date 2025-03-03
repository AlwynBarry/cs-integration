<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-item.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_Item as Cs_Item;


/**
 * The base class of any event item retrieved from the ChurchSuite JSON feed
 * Constructs the event object from the supplied JSON object, sanitizing every
 * stored attribute so that only sanitized values are held.
 * Provides convenience accessor functions to obtain the event values.
 *
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
 class Cs_Event extends Cs_Item {

	/*
	 * Inherits from Cs_Item which contains the common readonly properties:
	 * @var		string	$identifier		The unique event/group identifier for this item (a-z1-9 only)
	 * @var     string	$name			The unique event/group identifier for this item (a-z1-9 only)
	 * @var     string	$image_URL		The URL to the large (1024 width) image for this item (valid URL)
	 * @var     string	$location		The name of the location for this item (A-Za-z only)
	 * @var     string	$description	The description for this item, if any (html filtered) * 
	 */
	 

	/*
	 * The ChurchSuite-defined constants for the valid status of any event in the calendar
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @const    array of string 	The lowercase valid status of any event, ordered by commonality
	 */
	protected const EVENT_STATUS = array( 'confirmed', 'cancelled', 'pending' );

	/**
	 * The additional common attributes of all JSON returned event items in the ChurchSuite JSON feed.
	 * These are set to be either a santized value or a default value which can be
	 * easily tested for an 'empty' value where needed.
	 * They are readonly so that they cannot be changed once sanitized.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     \DateTime	$start_date	The santized start date and time of an event or null if no start date/time
	 * @var		\DateTime	$end_date	The sanitized end date and time of an event or null if no end date/time
	 * @var		string		$address	The sanitized address where the event is happening, or '' if not supplied
	 * @var		string		$status		The status of the event - one of the items from the EVENT_STATUS array
     * @var		string		$category	The sanitized name of the category of this event, or '' if not supplied
	 */
	protected readonly \DateTime $start_date;
	protected readonly \DateTime $end_date;
	protected readonly string $address;
	protected readonly string $status;
	protected readonly string $category;

    /*
     * Construct the initial values, sanitising all input provided to ensure all data is valid.
	 *
 	 * @since	1.0.0
	 * @param	\stdclass	$event_obj	the ChurchSuite JSON object for this event
     */
    public function __construct( \stdclass $event_obj ) {
		parent::__construct( $event_obj );
		if ( is_object( $event_obj ) ) {
			$this->start_date = $this->sanitize_start_date( $event_obj );
			$this->end_date = $this->sanitize_end_date( $event_obj );
			$this->address = $this->sanitize_address( $event_obj );
			$this->status = $this->sanitize_status( $event_obj );
			$this->category = $this->sanitize_category( $event_obj );
		} else {
			// Set all the strings to default values - usually ''
			// Has to be set here because readonly variables can only be set once
			$this->start_date = null;
			$this->end_date = null;
			$this->address = '';
			$this->status = 'cancelled';
			$this->category = '';
		}
	}


	/*
	 * ================================================================
	 *     CONSTRUCTOR HELPER FUNCTIONS
	 * ================================================================
	 */


	/*
	 * Return the start date and time of the event as a Date object, or null if it is not provided
	 * 
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the start date is in a different place or has a different name in the new object
	 * 
	 * @since	1.0.0
	 * @param	\stdclass	$event_obj	the ChurchSuite JSON object for this event
	 * @return	\DateTime				the date and time of the event in the current
	 * 									locale, or null if invalid or no supplied date/time
	 */
	protected function sanitize_start_date( \stdclass $event_obj ) : \DateTime {
		return ( isset( $event_obj->datetime_start ) )
					? date_create( $event_obj->datetime_start )
					: null;
	}

	/*
	 * Return the end date and time of the event as a Date object, or null if it is not provided
	 * 
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the end date is in a different place or has a different name in the new object
	 * 
	 * @since	1.0.0
	 * @param	\stdclass	$event_obj	the ChurchSuite JSON object for this event
	 * @return	\DateTime				the date and time of the end of the event in the
	 * 									current locale, or null if invalid or no supplied date/time
	 */
	protected function sanitize_end_date( \stdclass $event_obj) : \DateTime {
		return ( isset( $event_obj->datetime_end ) ) 
					? date_create( $event_obj->datetime_end )
					: null;
	}

	/*
	 * Return the event location from the JSON event object, or '' if the location name is missing or malformed
	 * 
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the location name is in a different place in the new object
	 * 
	 * @since	1.0.0
	 * @param	\stdclass	$event_obj	the ChurchSuite JSON object for this event
	 * @return	string					the sanitized address string, or '' if invalid
	 */
	protected function sanitize_address( \stdclass $event_obj ) : string {
		return ( isset( $event_obj->location->address ) ) 
					? htmlspecialchars( $event_obj->location->address )
					: '';
	}

	/*
	 * Return the event status from the JSON event object, or 'confirmed' if the status is missing or malformed
	 * 
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the status is in a different place in the new object
	 * 
	 * @since	1.0.0
	 * @param	\stdclass	$event_obj	the ChurchSuite JSON object for this event
	 * @return	string					the sanitized status - one of the EVENT_STATUS values; 'cancelled' if invalid
	 */
	protected function sanitize_status( \stdclass $event_obj ) : string {
		// Default is 'confirmed' even if the data in the object is malformed
		$result = \amb_dev\CSI\Cs_Event::EVENT_STATUS[0];
		if ( isset( $event_obj->status ) ) {
			$result = strtolower( $event_obj->status );
			$result = ( in_array( $result, \amb_dev\CSI\Cs_Event::EVENT_STATUS ) ) ? $result : \amb_dev\CSI\Cs_Event::EVENT_STATUS[0];
		}
		return $result;
	}

	/*
	 * Return the event category name from the JSON event object, or '' if the category name is missing or malformed
	 * The category string is sanitized to remove any html special characters, and to trim leading and trailing spaces
	 * 
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the category name is in a different place in the new object
	 * 
	 * @since	1.0.0
	 * @param	\stdclass	$event_obj	the ChurchSuite JSON object for this event
	 * @return	string					the category name string, or '' if invalid
	 */
	protected function sanitize_category( \stdclass $event_obj ) : string {
		return ( isset( $event_obj->category->name ) ) 
					? trim( strip_tags( $event_obj->category->name ) )
					: '';
	}

	/*
	 * ================================================================
	 *   THE PUBLIC INTERFACE
	 * ================================================================
	 */

	/*
	 * Get the ChurchSuite URL of the item we are holding data about
	 * 
	 * @since	1.0.0
	 * @param	cs		The ChurchSuite object that this item has come from
	 * @return	string	A valid URL to the item or '' if there is no item identifier to construct the URL from 
	 */ 
	public function get_URL( ChurchSuite $cs ) : string {
		return ( $this->is_URL() ) ? $cs->get_churchsuite_events_URL() . $this->identifier : '';
	}

	/*
	 * Check if the event has a start date and time
	 * 
	 * @since	1.0.0
	 * @return 	bool	true if the event has a start date / time
	 */
	public function is_start_date() : bool { return ( ! is_null( $this->start_date ) ); }

	/*
	 * Get the start date/time
	 * 
	 * @since	1.0.0
	 * @return	\DateTime	the start date/time in the current locale or NULL if none supplied
	 */
	public function get_start_date() : \DateTime { return $this->start_date; }

	/*
	 * Check if the event has an end date and time
	 * 
	 * @since	1.0.0
	 * @return	bool	true if the event has an end date / time
	 */
	public function is_end_date() : bool { return (! is_null( $this->end_date) ); }

	/*
	 * Get the end date/time
	 * 
	 * @since	1.0.0
	 * @return	\DateTime	the end date/time in the current locale or NULL if none supplied
	 */
	public function get_end_date() : \DateTime { return $this->end_date; }
		
	/*
	 * Check if the event has a supplied address
	 * 
	 * @since	1.0.0
	 * @return	bool	true if the event has a supplied address
	 */
	public function is_address() : bool { return ( $this->address !== '' ); }

	/*
	 * Get the address for the event
	 * 
	 * @since	1.0.0
	 * @return	string	the (sanitized) address or '' if no address was given for this event 
	 */
	public function get_address() : string { return $this->address; }

	/*
	 * Get the status of the event
	 * 
	 * @since	1.0.0
	 * @return	string	the (sanitized) status or 'cancelled' if invalid status or a null JSON object given 
	 */
	public function get_status() : string { return $this->status; }
	
	/*
	 * Is the event confirmed? - A convenience function to check for the confirmed value
	 * 
	 * @since	1.0.0
	 * @return	bool	true if status is 'confirmed' 
	 */
	public function is_confirmed() : string { return $this->status === 'confirmed'; }

	/*
	 * Has the event been cancelled? - A convenience function to check for the cancelled value
	 * 
	 * @since	1.0.0
	 * @return	bool	true if status is 'cancelled' 
	 */
	public function is_cancelled() : string { return $this->status === 'cancelled'; }

	/*
	 * Is the event merely pending? - A convenience function to check for the pending value
	 * 
	 * @since	1.0.0
	 * @return	bool	true if status is 'pending' 
	 */
	public function is_pending() : string { return $this->status === 'pending'; }

	/*
	 * Check if the event has a supplied category
	 * 
	 * @since	1.0.0
	 * @return	bool	true if the event has a supplied category
	 */
	public function is_category() : bool { return ( $this->category !== '' ); }

	/*
	 * Get the category name for the event
	 * 
	 * @since	1.0.0
	 * @return	string	the (sanitized) category name or '' if no category was given for this event 
	 */
	public function get_category() : string { return $this->category; }
	
	/*
	 * Get the category name for the event modified so it could be used as a HTML class name
	 * Note: The returned category is modified to return only the A-Za-z0-9 and hyphen characters
	 * 		 All underscores become hyphens, all non alpha-numeric are discarded, and consecutive hyphens
	 * 		 are removed to become a single hyphen.  This allows the category name to be used as a class name
	 * 
	 * @since	1.0.0
	 * @return	string	the category name modified to add a leading 'cs-' and to replace non alpha-numeric
	 * 					with single hyphen.  Return '' if no category name was given for this event. 
	 */
	public function get_category_as_html_class() : string {
		$result = $this->get_category();
		if ( $this->is_category() ) {
			// Remove leading and trailing spaces
			$result = trim( $result );
			// Replace all non-alphanumeric with hyphens
			$result = preg_replace( '#[^a-z0-9-]#i', '-', $result );
			// Replace consecutive hyphens with single hyphen
			$result = preg_replace('#[ -]+#', '-', $result );
			// Remove starting and trailing hyphens
			$result = 'cs-' . trim( rtrim( $result, '-'), '-');
			// Uppercase to lowercase
			$result = strtolower( $result ); 
		}
		return $result;
	}


}
