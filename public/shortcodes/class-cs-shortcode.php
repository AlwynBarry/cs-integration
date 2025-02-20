<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-json-api.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_JSON_API as Cs_JSON_API;


abstract class Cs_Shortcode {
    
    // Note - we keep these separate from the CSJsonApi acceptable keys so that we don't need to define defaults for all possible attributes 
    protected const DEFAULT_ATTS = array( 'num_results' => 0, 'church_name' => '', 'featured' => 0, 'merge' => 'sequence' );
    
    // The default cache time for previous returned results
    protected const CACHE_TIME = 1 * HOUR_IN_SECONDS;

	protected ChurchSuite $cs;
	protected Cs_JSON_API $api;
	protected $JSON_response = null;
	protected $transient_key = '';
	
	
	/*
	 * Process the supplied attributes to leave only valid parameters, create the URLs
	 * required for the JSON feed from ChurchSuite and create the means to communicate via
	 * that JSON feed.  Also, set the cache key appropriate for this query.
	 * NOTE: The constructor does NOT get the JSON response so that we can get a previous
	 *       HTML output from cache if one already exists so we can mitigate any possible
	 * 		 delay for the JSON response and the processing of that response.
	 */
	public function __construct( $atts, $JSON_base = ChurchSuite::EVENTS ) {
	    // set defaults
		$sc_atts = shortcode_atts( \amb_dev\CSI\Cs_Shortcode::DEFAULT_ATTS, $atts );

		// Get the church name parameter
		$church_name = $sc_atts[ 'church_name' ];

		// Set the events parameter from the attribute and check bounds
		$num_results = ( is_numeric( $sc_atts[ 'num_results' ] ) ) ? (int) $sc_atts[ 'num_results' ] : 3;
		$num_results = ( ( $num_results < 0 ) ? 0 : $num_results );
		
		// Create the churchsuite JSON URL and get the JSON response
		$this->cs = new ChurchSuite( $church_name, $JSON_base );
		$this->api = new Cs_JSON_API( $this->cs, $num_results, $atts );
		
		// Set the transient key to use when we want a shortcode to cache the HTML response
		$this->transient_key = ( $this->api )->get_transient_key( $this, ( $this->cs )->get_JSON_base() );
	}

	/*
	 * The constructor does NOT get the JSON response so that we can get a response from
	 * cache if one already exists.  This function checks for a response already, and if
	 * there is no existing JSON response we call the API to get a JSON response, setting
	 * the JSON_response property with the response, ready for later processing.
	 */
	protected function get_JSON_response() {
		if ( is_null( $this->JSON_response ) ) {
			$this->JSON_response = ( $this->api )->get_response();
		}
	}

	/*
	 * This is the function that will return the HTML response for the shortcode
	 * You should first call $this->get_JSON_response() to try to fetch the JSON
	 * response, and then iterate over the objects of this response to generate
	 * the required HTML.
	 * @return: a string with the HTML of the shortcode response, or '' if an error
	 */
	protected abstract function get_response() : string;
	
	/*
	 * Run the shortcode to produce the HTML output
	 * First we check the cache - if there is a cached response, return it.
	 * If there is no cached response, we get a response from the JSON API, use
	 * that to form a new HTML response string, add the HTML response string to
	 * the cache and then return the HTML response string.
	 * @return: a string with the HTML of the shortcode response or '' if an error
	 */
	public function run_shortcode() : string {
		// Check if we have a cached response - if not, get a new response and then cache it before returning
		if ( false === ( $response = get_transient( $this->transient_key ) ) ) { 
			// Create a new response - this dispatches to the shortcode subclass
			$output = $this->get_response();
			// Put the response into the cache
			if ( $output !== '' ) { set_transient( $this->transient_key, $output, Cs_Shortcode::CACHE_TIME ); }
			if ( $output === '' ) { $output = '<div>Please try again later for this information</div>'; }
			return $output;
		} else {
		    // return the cached response
			return $response;
		}
	}

}
