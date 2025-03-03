<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-item.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_Item as Cs_Item;


/**
 * The base class of any group item retrieved from the ChurchSuite JSON feed
 * Constructs the group object from the supplied JSON object, sanitizing every
 * stored attribute so that only sanitized values are held.
 * Provides convenience accessor functions to obtain the group values.
 *
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
 class Cs_Group extends Cs_Item {

	/*
	 * Inherits from Cs_Item which contains the common readonly properties:
	 * @var		string	$identifier		The unique event/group identifier for this item (a-z1-9 only)
	 * @var     string	$name			The unique event/group identifier for this item (a-z1-9 only)
	 * @var     string	$image_URL		The URL to the large (1024 width) image for this item (valid URL)
	 * @var     string	$location		The name of the location for this item (A-Za-z only)
	 * @var     string	$description	The description for this item, if any (html filtered) * 
	 */

	/**
	 * The additional common attributes of all JSON group items in the ChurchSuite JSON feed.
	 * These are set to be either a santized value or a default value which can be
	 * easily tested for an 'empty' value where needed.
	 * They are readonly so that they cannot be changed once sanitized.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string	$frequency			The santized frequency string - usually 'daily', 'weekly',
	 * 										'fortnightly' or 'monthly' or '' if not supplied
	 * @var     string	$custom_frequency	The santized custom frequency string - can be any [short] string, or '' if invalid
	 * @var     string	$day_of_week		The santized day of the week string - locale sensitive short day string, or '' if invalid
	 * @var     string	$time_of_meeting	The santized time of the meeting string - in format HH:mm am/pm, or '' if invalid
	 */
	protected readonly string $frequency;
	protected readonly string $custom_frequency;
	protected readonly string $day_of_week;
	protected readonly string $time_of_meeting;

    /*
     * Construct the initial values, sanitising all input provided to ensure all data is valid.
	 *
 	 * @since	1.0.0
     * Params:	\stdclass	$group_obj	the JSON object ChurchSuite returned for a group
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
	 * 
	 * The frequency string is sanitized before it is returned.
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * 
	 * @since	1.0.0
	 * @param	\stdclass	$event_obj	the ChurchSuite JSON object for this group
	 * @return	string					the sanitized frequency string, or '' if invalid
	 */
	protected function fetch_frequency( \stdclass $group_obj ) : string {
		return ( isset( $group_obj->frequency ) )
					? htmlspecialchars( $group_obj->frequency )
					: '' ;
	}

	/*
	 * Return the custom frequency string if it is set, or '' if not
	 * 
	 * The custom frequency string is sanitized before it is returned.
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * 
	 * @since	1.0.0
	 * @param	\stdclass	$event_obj	the ChurchSuite JSON object for this group
	 * @return	string					the sanitized custom frequency string, or '' if invalid
	 */
	protected function fetch_custom_frequency( \stdclass $group_obj ) : string {
		return ( isset( $group_obj->custom_frequency ) )
						? htmlspecialchars( $group_obj->custom_frequency )
						: '' ;
	}

	/*
	 * Return the day of the week the group meets if it is set, or an empty string if not
	 * The string returned is the locale sensitive short string for the day of the week.
	 * 
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * 
	 * @since	1.0.0
	 * @param	\stdclass	$event_obj	the ChurchSuite JSON object for this group
	 * @return	string					the short sanitized day of the week string, or '' if invalid
	 */
	protected function fetch_day_of_week( \stdclass $group_obj ) : string {
		$output = '';
		if ( isset( $group_obj->day ) && is_numeric( $group_obj->day ) ) {
			$day_of_week_numeric = (int) $group_obj->day;
			if ( ( $day_of_week_numeric >= 0 ) && ( $day_of_week_numeric <= 6 ) ) {
				$output .= " on " . $dow_text = gmdate( 'l', strtotime( "Sunday +{$day_of_week_numeric} days" ) );
			}
		}
		return $output;
	}

	/*
	 * Return the time of the meeting, if it is set, or an empty string if not
	 * 
	 * The string returned is sanitised to be a valid time string.
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * 
	 * @since	1.0.0
	 * @param	\stdclass	$event_obj	the ChurchSuite JSON object for this group
	 * @return	string					the sanitized time string in HH:MM am/pm format, or '' if invalid
	 */
	protected function fetch_time_of_meeting( \stdclass $group_obj ) : string {
        return ( isset( $group_obj->time ) && ( $group_obj->time !== '' ) )
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
	 * 
	 * @since	1.0.0
	 * @param	cs		The ChurchSuite object that this group has come from
	 * @return	string	a valid URL to the item or '' if there is no item identifier to construct the URL from 
	 */ 
	public function get_URL( ChurchSuite $cs  ) : string {
		return  ( $this->is_URL() ) ? $cs->get_churchsuite_groups_url() . $this->identifier : '';
	}

	/*
	 * Check if the event has a regular meeting schedule
	 * 
	 * @since	1.0.0
	 * @return 	bool	true if there is a regular meeting schedule, not a custom schedule or no schedule
	 */
	public function is_frequency() : bool { return ( ( $this->frequency !== '' ) && ( $this->frequency !== 'custom' ) ); }

	/*
	 * Get the string giving the regular meeting schedule
	 * 
	 * @since	1.0.0
	 * @return 	string	the string description of the meeting schedule or '' if no valid frequency was set or frequency was 'custom'
	 */
	public function get_frequency() : String { return $this->frequency; }

	/*
	 * Check if the event has a custom meeting schedule - if frequency is 'custom' and a custom frequency string has been provided
	 * 
	 * @since	1.0.0
	 * @return 	bool	true if there is a custom meeting schedule
	 */
	public function is_custom_frequency() : bool { return ( ( $this->frequency == 'custom' ) && ( $this->custom_frequency !== '' ) ); }

	/*
	 * Get the string giving the custom meeting schedule
	 * 
	 * @since	1.0.0
	 * @return 	string	the string description of the meeting schedule or 'Weekly' if no custom schedule was set
	 */
	public function get_custom_frequency() : String { return $this->custom_frequency; }

	/*
	 * Check if the event has a day of the week when they meet set
	 * 
	 * @since	1.0.0
	 * @return 	bool	true if there is a weekly schedule and a day of the week set
	 */
	public function is_day_of_week() : bool { return ( $this->day_of_week !== '' ); }

	/*
	 * Get the string giving the day of the week that the group meets on
	 * 
	 * @since	1.0.0
	 * @return 	string	a valid locale sensitive short day of the week, or '' if no day of week was provided
	 */
	public function get_day_of_week() : String { return $this->day_of_week; }

	/*
	 * Check if the event has a time of meeting set
	 * 
	 * @since	1.0.0
	 * @return 	bool	true if there is a time of meeting set
	 */
	public function is_time_of_meeting() : bool { return ( $this->time_of_meeting !== '' ); }

	/*
	 * Get the string that has the time of the meeting, formatted hh:mm am/pm
	 * 
	 * @since	1.0.0
	 * @return 	string	a valid time for the meeting, or '' if no time was set
	 */
	public function get_time_of_meeting() : String { return $this->time_of_meeting; }

}
