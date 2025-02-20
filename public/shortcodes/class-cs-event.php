<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-item.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_Item as Cs_Item;


class Cs_Event extends Cs_Item {

	private const EVENT_STATUS = array( 'confirmed', 'cancelled', 'pending' );

	protected readonly \DateTime $start_date;
	protected readonly \DateTime $end_date;
	protected readonly string $address;
	protected readonly string $status;

    /*
     * Construct the initial values, sanitising all input provided to ensure all data is valid.
     * Params:
     * 
     */
    public function __construct( \stdclass $event_obj ) {
		parent::__construct( $event_obj );
		if ( is_object( $event_obj ) ) {
			$this->start_date = $this->fetch_start_date( $event_obj );
			$this->end_date = $this->fetch_end_date( $event_obj );
			$this->address = $this->fetch_address( $event_obj );
			$this->status = $this->fetch_status( $event_obj );
		} else {
			$this->start_date = null;
			$this->end_date = null;
			$this->address = '';
			$this->status = 'cancelled';
		}
	}


	/*
	 * ================================================================
	 *     CONSTRUCTOR HELPER FUNCTIONS
	 * ================================================================
	 */


	/*
	 * Return the start date and time of the event as a Date object, or null if it is not provided
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the start date is in a different place or has a different name in the new object
	 */
	protected function fetch_start_date( \stdclass $event_obj ) : \DateTime {
		return ( property_exists( $event_obj,'datetime_start' ) && ( ! is_null($event_obj->datetime_start ) ) )
					? date_create( $event_obj->datetime_start )
					: null;
	}

	/*
	 * Return the end date and time of the event as a Date object, or null if it is not provided
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the end date is in a different place or has a different name in the new object
	 */
	protected function fetch_end_date( \stdclass $event_obj) : \DateTime {
		return ( property_exists( $event_obj,'datetime_end' ) && (! is_null( $event_obj->datetime_end )) ) 
					? date_create( $event_obj->datetime_end )
					: null;
	}

	/*
	 * Return the event location from the JSON event object, or '' if the location name is missing or malformed
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the location name is in a different place in the new object
	 */
	protected function fetch_address( \stdclass $event_obj ) : string {
		return ( property_exists( $event_obj, 'location' ) && (! is_null( $event_obj->location )) &&
				 property_exists( $event_obj->location, 'address' ) && ( ! is_null( $event_obj->location->address ) ) ) 
					? htmlspecialchars( $event_obj->location->address )
					: '';
	}

	/*
	 * Return the event status from the JSON event object, or 'confirmed' if the status is missing or malformed
	 * Note: the object parameter must be checked to be a valid object before this is called
	 */
	protected function fetch_status( \stdclass $event_obj ) : string {
		// Default is 'confirmed' even if the data in the object is malformed
		$result = \amb_dev\CSI\Cs_Event::EVENT_STATUS[0];
		if ( property_exists( $event_obj, 'status' ) && (! is_null( $event_obj->status )) ) {
			$result = strtolower( $event_obj->status );
			$result = ( in_array( $result, \amb_dev\CSI\Cs_Event::EVENT_STATUS ) ) ? $result : \amb_dev\CSI\Cs_Event::EVENT_STATUS[0];
		}
		return $result;
	}

	/*
	 * ================================================================
	 *   THE PUBLIC INTERFACE
	 * ================================================================
	 */

	/*
	 * Get the ChurchSuite URL of the item we are holding data about
	 * Parameter: CS - The ChurchSuite object that this item has come from
	 * @return string - a valid URL to the item or '' if there is no item identifier to construct the URL from 
	 */ 
	public function get_URL( ChurchSuite $cs ) : string {
		return ( $this->is_URL() ) ? $cs->get_churchsuite_events_URL() . $this->identifier : '';
	}

	/*
	 * Check if the event has a start date and time
	 * @return bool - true if the event has a start date / time
	 */
	public function is_start_date() : bool { return ( ! is_null( $this->start_date ) ); }

	/*
	 * Get the start date/time
	 * @return Date - the start date/time or NULL
	 */
	public function get_start_date() : \DateTime { return $this->start_date; }

	/*
	 * Check if the event has an end date and time
	 * @return bool - true if the event has an end date / time
	 */
	public function is_end_date() : bool { return (! is_null( $this->end_date) ); }

	/*
	 * Get the end date/time
	 * @return Date - the end date/time or NULL
	 */
	public function get_end_date() : \DateTime { return $this->end_date; }
		
	/*
	 * Check if the event has a supplied address
	 * @return bool - true if the event has a supplied address
	 */
	public function is_address() : bool { return ( $this->address !== '' ); }

	/*
	 * Get the address for the event
	 * @return String - the (sanitized) address or '' if no address was given for this event 
	 */
	public function get_address() : string { return $this->address; }

	/*
	 * Get the status of the event
	 * @return String - the (sanitized) status or 'confirmed' if no valid status, or 'cancelled' if a null JSON object was not given 
	 */
	public function get_status() : string { return $this->status; }
	
	/*
	 * Is the event confirmed?
	 * @return bool - true is status is 'confirmed' 
	 */
	public function is_confirmed() : string { return $this->status === 'confirmed'; }

	/*
	 * Has the event been cancelled?
	 * @return bool - true is status is 'cancelled' 
	 */
	public function is_cancelled() : string { return $this->status === 'cancelled'; }

	/*
	 * Is the event merely pending?
	 * @return bool - true is status is 'pending' 
	 */
	public function is_pending() : string { return $this->status === 'pending'; }

}
