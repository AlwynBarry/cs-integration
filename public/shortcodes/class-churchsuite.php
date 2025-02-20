<?php

namespace amb_dev\CSI;


final class ChurchSuite {

	private const CHURCHSUITE_URL = '.churchsuite.com/';

	/*
	 * The various available public ChurchSuite JSON feeds
	 * If more feeds are added in future, edit here and add an appropriate getter function
	 * NOTE: accessed only via getter function so we can be sure the feed URL remains sanitized
	 */
	public const EVENTS = 'events';
	public const GROUPS = 'groups';

    private const BASE_JSON_URLS = array(
		ChurchSuite::EVENTS => 'embed/calendar/json',
		ChurchSuite::GROUPS => 'embed/smallgroups/json',
	);
	
	public const BASE_ITEM_URLS = array(
		ChurchSuite::EVENTS => 'events',
		ChurchSuite::GROUPS => 'groups',
	);

	/*
	 * The sanitized church name string.  Kept private and readonly so it cannot be changed once sanitized
	 */
    private readonly string $church_name;
    private readonly string $JSON_base;

	/*
	 * Construct the ChurchSuite object with the supplied churchName
	 * @param churchName : string can be any string of [a-zA-z]+; all other characters will be removed
	 * @param jsonBase : string which must be const ChurchSuite::EVENTS or const ChurchSuite::GROUPS
	 * 					 A malformed string will cause the default param value to be set
	 */
	public function __construct( string $church_name, string $JSON_base = ChurchSuite::EVENTS ) {
		$this->church_name = \amb_dev\CSI\ChurchSuite::sanitize_alpha_only( $church_name );
		$JSON_base = \amb_dev\CSI\ChurchSuite::sanitize_alpha_only( $JSON_base );
		$this->JSON_base = ( array_key_exists( $JSON_base, ChurchSuite::BASE_JSON_URLS ) ) ? $JSON_base : ChurchSuite::EVENTS;
	}

	/*
	 * Sanitize a string to contain only A-Za-z and then change all to lower case
	 * @return the churchName sanitized to a-zA-Z and then transformed to a-z
	 */
	private static function sanitize_alpha_only( string $str ) : string {
		return strtolower( preg_replace( "/[^a-zA-Z]+/", "", $str ) );
	}

	/*
	 * Return the sanitized URL for the ChurchSuite groups JSON feed.
	 * Although santizied, don't use this to construct your own feed url;
	 * instead use the supplied feed getter functions to ensure a sanitized url is always provided.
	 */
	public function get_church_name() : string {
		return $this->church_name;
	}

	/*
	 * Return the sanitized base of the chosen ChurchSuite JSON feed
	 */
	public function get_JSON_base() : string {
		return $this->JSON_base;
	}

	/*
	 * Return the Churchsuite URL for this ChurchSuite site
	 */
	public function get_churchsuite_URL() : string {
 		return 'https://' . $this->church_name . ChurchSuite::CHURCHSUITE_URL;
	}
	
	/*
	 * Return the sanitized URL for the ChurchSuite JSON feed requested in the constructor
	 */
	public function get_JSON_URL() : string {
		return $this->get_churchsuite_URL() . ChurchSuite::BASE_JSON_URLS[ $this->JSON_base ];
	}
	
	/*
	 * Return the sanitized URL for the ChurchSuite events JSON feed
	 */
	public function get_event_JSON_URL() : string {
		return $this->get_churchsuite_URL() . ChurchSuite::BASE_JSON_URLS[ ChurchSuite::EVENTS ];
	}
	
	/*
	 * Return the sanitized URL for the ChurchSuite groups JSON feed
	 */
	public function get_groups_JSON_URL() : string {
 		return $this->get_churchsuite_URL() . ChurchSuite::BASE_JSON_URLS[ ChurchSuite::GROUPS ];
	}
	
	/*
	 * Return the Churchsuite Events URL for this ChurchSuite site
	 */
	public function get_churchsuite_events_URL() : string {
 		return $this->get_churchsuite_URL() . ChurchSuite::BASE_ITEM_URLS[ ChurchSuite::EVENTS ] . '/';
	}
	
	/*
	 * Return the Churchsuite Groups URL for this ChurchSuite site
	 */
	public function get_churchsuite_groups_URL() : string {
 		return $this->get_churchsuite_URL() . ChurchSuite::BASE_ITEM_URLS[ ChurchSuite::GROUPS ] . '/';
	}
	

}
