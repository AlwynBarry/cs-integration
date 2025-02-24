<?php

namespace amb_dev\CSI;


/**
 * ChurchSuite class constructs and maintains valid URLs to access the
 * churchsuite events and groups, and the church JSON feed for a particular
 * church, identified by its church name. The church name is that which you
 * use to access ChurchSuite - e.g. from https://mychurch.churchsuite.com/
 * 'mychurch' is the name used to access your ChurchSuite account.
 * 
 * The class sanitizes all input so that only valid URLs can be obtained.
 *
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
final class ChurchSuite {
	
	/*
	 * The root url for churchsuite, minus the church name.
	 * Provided as a const so it can be easily changed if ChurchSuite changes in the future.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @const    string 	The root url for churchsuite, minus the churchname
	 */
	private const CHURCHSUITE_URL = '.churchsuite.com/';

	/*
	 * The various available public ChurchSuite JSON feed types
	 * If more feeds are added in future, edit here and add an appropriate getter function
	 * 
	 * @since    1.0.0
	 * @access   private
	 * @const    string 	The feed names which can be added to the URL to access a JSON feed
	 */
	public const EVENTS = 'events';
	public const GROUPS = 'groups';

	/*
	 * The various available public ChurchSuite JSON feed url tails
	 * If more feeds are added in future, edit here and add an appropriate getter function
	 * 
	 * @since    1.0.0
	 * @access   private
	 * @const    array(key=>string) 	The feed url tails for each feed type to access a ChurchSuite JSON feed
	 */
    private const BASE_JSON_URLS = array(
		ChurchSuite::EVENTS => 'embed/calendar/json',
		ChurchSuite::GROUPS => 'embed/smallgroups/json',
	);

	/*
	 * A const array to use to check that the correct feed type parameter has been supplied
	 * Just provided to optimise the check so that we don't need to call a function to obtain the keys of an array
	 *
	 * @since	1.0.0
	 * @access	public
	 * @const	array(string)
	 */
	public const BASE_ITEM_URLS = array(
		ChurchSuite::EVENTS => 'events',
		ChurchSuite::GROUPS => 'groups',
	);

	/*
	 * The sanitized church name string and JSON base URL strings.
	 * Kept private and readonly so it cannot be changed once sanitized
	 * 
	 * @since	1.0.0
	 * @access	private
	 */
    private readonly string $church_name;
    private readonly string $JSON_base;

	/*
	 * Construct the ChurchSuite URLs from the supplied church name and kind of feed required
	 * 
	 * @since	1.0.0
	 * @param	string	church_name		any string of [a-zA-z]+; all other characters will be removed
	 * 									The church name is that which you use to access ChurchSuite
	 * 									e.g. from https://mychurch.churchsuite.com/ 'mychurch' is the
	 * 										 name used to access your ChurchSuite account.
	 * @param	string	json_base		must be either ChurchSuite::EVENTS or ChurchSuite::GROUPS
	 */
	public function __construct( string $church_name, string $JSON_base = ChurchSuite::EVENTS ) {
		$this->church_name = \amb_dev\CSI\ChurchSuite::sanitize_alpha_only( $church_name );
		$JSON_base = \amb_dev\CSI\ChurchSuite::sanitize_alpha_only( $JSON_base );
		$this->JSON_base = ( array_key_exists( $JSON_base, ChurchSuite::BASE_JSON_URLS ) ) ? $JSON_base : ChurchSuite::EVENTS;
	}

	/*
	 * Helper function to sanitize a string to contain only A-Za-z and then change all to lower case
	 * 
	 * @since 1.0.0
	 * @param	string	any string value
	 * @return	string	the supplied string sanitized to a-zA-Z and then transformed to lowercase
	 */
	private static function sanitize_alpha_only( string $str ) : string {
		return strtolower( preg_replace( "/[^a-zA-Z]+/", "", $str ) );
	}

	/*
	 * Return the sanitized URL for the ChurchSuite groups JSON feed.
	 * Although santizied, don't use this to construct your own feed url; instead use
	 * the supplied feed getter functions to ensure a sanitized url is always provided.
	 * 
	 * @since 1.0.0
	 * @return	string	the sanitized church name
	 */
	public function get_church_name() : string {
		return $this->church_name;
	}

	/*
	 * Return the sanitized base of the chosen ChurchSuite JSON feed
	 * 
	 * @since 1.0.0
	 * @return string	which will be either ChurchSuite::EVENTS or ChurchSuite::GROUPS
	 */
	public function get_JSON_base() : string {
		return $this->JSON_base;
	}

	/*
	 * Return the Churchsuite URL for this ChurchSuite site - i.e. the site you'd normally log in to
	 * 
	 * @since 1.0.0
	 * @return string	the normal churchsuite URL for the supplied church name
	 */
	public function get_churchsuite_URL() : string {
 		return 'https://' . $this->church_name . ChurchSuite::CHURCHSUITE_URL;
	}
	
	/*
	 * Return the sanitized URL for the ChurchSuite JSON feed requested in the constructor
	 * 
	 * @since 1.0.0
	 * @return string	the base JSON API url for the supplied church name
	 */
	public function get_JSON_URL() : string {
		return $this->get_churchsuite_URL() . ChurchSuite::BASE_JSON_URLS[ $this->JSON_base ];
	}
	
	/*
	 * Return the sanitized URL for the ChurchSuite events JSON feed, regardless of what feed was originally set
	 * 
	 * @since 1.0.0
	 * @return string	the base JSON API url for the event feed, regardless of what feed was originally requested
	 */
	public function get_event_JSON_URL() : string {
		return $this->get_churchsuite_URL() . ChurchSuite::BASE_JSON_URLS[ ChurchSuite::EVENTS ];
	}
	
	/*
	 * Return the sanitized URL for the ChurchSuite groups JSON feed, regardless of what feed was originally set
	 * 
	 * @since 1.0.0
	 * @return string	the base JSON API url for the group feed, regardless of what feed was originally requested
	 */
	public function get_groups_JSON_URL() : string {
 		return $this->get_churchsuite_URL() . ChurchSuite::BASE_JSON_URLS[ ChurchSuite::GROUPS ];
	}
	
	/*
	 * Return the Churchsuite Events URL for this ChurchSuite site.
	 * Use this to construct the link to a specific event by adding an event identifier.
	 * 
	 * @since 1.0.0
	 * @return string	the base Events URL to which an event identifier can be added to link to an event
	 */
	public function get_churchsuite_events_URL() : string {
 		return $this->get_churchsuite_URL() . ChurchSuite::BASE_ITEM_URLS[ ChurchSuite::EVENTS ] . '/';
	}
	
	/*
	 * Return the Churchsuite Groups URL for this ChurchSuite site.
	 * Use this to construct a link to a specific group by adding the group identifier.
	 * 
	 * @since 1.0.0
	 * @return string	the base Groups URL to which a group identifier can be added to link to a group
	 */
	public function get_churchsuite_groups_URL() : string {
 		return $this->get_churchsuite_URL() . ChurchSuite::BASE_ITEM_URLS[ ChurchSuite::GROUPS ] . '/';
	}
	

}
