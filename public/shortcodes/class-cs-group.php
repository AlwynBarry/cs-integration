<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-item.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_Item as Cs_Item;


class Cs_Group extends Cs_Item {

	/*
	 * NOTE: All these values are sanitized before they are set, and cannot then be changed
	 */
	protected readonly string $frequency;
	protected readonly string $custom_frequency;
	protected readonly string $day_of_week;
	protected readonly string $time_of_meeting;

    /*
     * Construct the initial values, sanitising all input provided to ensure all data is valid.
     * Params: $group_obj - the JSON object ChurchSuite returned for a group
     */
    public function __construct( \stdclass $group_obj ) {
		parent::__construct( $group_obj );
		if ( is_object( $group_obj ) ) {
			$this->frequency = $this->fetch_frequency( $group_obj );
			$this->custom_frequency = $this->fetch_custom_frequency( $group_obj );
			$this->day_of_week = $this->fetch_day_of_week( $group_obj );
			$this->time_of_meeting = $this->fetch_time_of_meeting( $group_obj );
		} else {
			$this->frequency = '';
			$this->custom_frequency = '';
			$this->day_of_week = '';
			$this->time_of_meeting = '';
		}
	}


	/*
	 * ================================================================
	 *     CONSTRUCTOR HELPER FUNCTIONS
	 * ================================================================
	 */


	/*
	 * Return the frequency string if it is set, or '' if not
	 * The frequency string is sanitized before it is returned.
	 * Note: the object parameter must be checked to be a valid object before this is called
	 */
	protected function fetch_frequency( \stdclass $group_obj ) : string {
		return ( property_exists( $group_obj, 'frequency' ) && ( ! is_null( $group_obj->frequency ) ) )
					? htmlspecialchars( $group_obj->frequency )
					: '' ;
	}

	/*
	 * Return the custom frequency string if it is set, or '' if not
	 * The custom frequency string is sanitized before it is returned.
	 * Note: the object parameter must be checked to be a valid object before this is called
	 */
	protected function fetch_custom_frequency( \stdclass $group_obj ) : string {
		return ( property_exists( $group_obj, 'custom_frequency' ) && ( ! is_null( $group_obj->custom_frequency ) ) )
						? htmlspecialchars( $group_obj->custom_frequency )
						: '' ;
	}

	/*
	 * Return the day of the week the group meets if it is set, or an empty string if not
	 * The string returned is a day of the week - it is sanitised to be a valid day string.
	 * Note: the object parameter must be checked to be a valid object before this is called
	 */
	protected function fetch_day_of_week( \stdclass $group_obj ) : string {
		$output = '';
		if ( property_exists( $group_obj, 'day' ) && ( ! is_null( $group_obj->day ) ) && is_numeric( $group_obj->day ) ) {
			$day_of_week_numeric = (int) $group_obj->day;
			if ( ( $day_of_week_numeric >= 0 ) && ( $day_of_week_numeric <= 6 ) ) {
				$output .= " on " . $dow_text = date( 'l', strtotime( "Sunday +{$day_of_week_numeric} days" ) );
			}
		}
		return $output;
	}

	/*
	 * Return the time of the meeting, if it is set, or an empty string if not
	 * The string returned is sanitised to be a valid time string.
	 * Note: the object parameter must be checked to be a valid object before this is called
	 */
	protected function fetch_time_of_meeting( \stdclass $group_obj ) : string {
        return ( property_exists( $group_obj, 'time' ) && ( ! is_null( $group_obj->time ) ) && ( $group_obj->time !== '' ) )
			? date_format( date_create( $group_obj->time ), 'g:ia' )
            : '' ;
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
	public function get_URL( ChurchSuite $cs  ) : string {
		return  ( $this->is_URL() ) ? $cs->get_churchsuite_groups_url() . $this->identifier : '';
	}

	/*
	 * Check if the event has a regular meeting schedule
	 * @return bool - true if there is a regular meeting schedule, not a custom schedule or no schedule
	 */
	public function is_frequency() : bool { return ( ( $this->frequency !== '' ) && ( $this->frequency !== 'custom' ) ); }

	/*
	 * Get the string giving the regular meeting schedule
	 * @return String - the string description of the meeting schedule or '' if no valid frequency was set or frequency was 'custom'
	 */
	public function get_frequency() : String { return $this->frequency; }

	/*
	 * Check if the event has a custom meeting schedule - if frequency is 'custom' and a custom frequency string has been provided
	 * @return bool - true if there is a custom meeting schedule
	 */
	public function is_custom_frequency() : bool { return ( ( $this->frequency == 'custom' ) && ( $this->custom_frequency !== '' ) ); }

	/*
	 * Get the string giving the custom meeting schedule
	 * @return String - the string description of the meeting schedule or 'Weekly' if no custom schedule was set
	 */
	public function get_custom_frequency() : String { return $this->custom_frequency; }

	/*
	 * Check if the event has a day of the week when they meet set
	 * @return bool - true if there is a weekly schedule and a day of the week set
	 */
	public function is_day_of_week() : bool { return ( $this->day_of_week !== '' ); }

	/*
	 * Get the string giving the day of the week that the group meets on
	 * @return String - a valid day of the week in the current language, or '' if no day of week was provided
	 */
	public function get_day_of_week() : String { return $this->day_of_week; }

	/*
	 * Check if the event has a time of meeting set
	 * @return bool - true if there is a time of meeting set
	 */
	public function is_time_of_meeting() : bool { return ( $this->time_of_meeting !== '' ); }

	/*
	 * Get the string that has the time of the meeting, formatted hh:mm am/pm
	 * @return String - a valid time for the meeting, or '' if no time was set
	 */
	public function get_time_of_meeting() : String { return $this->time_of_meeting; }

}
