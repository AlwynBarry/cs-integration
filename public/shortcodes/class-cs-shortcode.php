<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-json-api.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_JSON_API as Cs_JSON_API;

/**
 * The base class of the shortcodes created for this plugin.
 * This class holds the ChurchSuite instance (which constructs and maintains
 * the URL for access to the JSON feed, and the base URL for the events and
 * groups in ChurchSuite for which it returns information), the JSON Api
 * instance, which maintains the data needed to construct the call to the
 * ChurchSuite JSON API, the response array received from the JSON API so
 * that it is only called when needed, and a constructed Key which can be
 * used to access the cache to see if there has been a recent equivalent call
 * to the JSON API whose results can be used instead of new results being created.
 * 
 * The constructor will take the attributes supplied to the shortcode, check
 * and sanitize them, and then create the ChurchSuite and JSON API details
 * required to make the JSON API request.  The JSON API request is not made,
 * however, until the run_shortcode() function is called on the object, and is
 * only made if there is no cached result which can be reused.
 * 
 * In the MVC model, the Cs_ShortCode classes are the Controller, with the
 * Event or Group classes the model and the EventView/GroupView etc classes the View.
 * 
 * To create a shortcode, extend this class and provide a get_response() function.
 * The get_response() function should firstly call $this->get_JSON_response() to
 * get the JSON data, and output the container HTML and with the container HTML
 * it should iterate over the response array of objects, using a View class to
 * output each object.  run_shortcode() will dispatch to get_response() if there is
 * no cache to re-use, and will update the cache to the new response if needed.
 *
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
abstract class Cs_Shortcode {
    
    // Note - we keep these separate from the CSJsonApi acceptable keys so that we don't need to define defaults for all possible attributes 
    protected const DEFAULT_ATTS = array( 'num_results' => 0, 'church_name' => '', 'featured' => 0, 'merge' => 'sequence' );
    
    /*
     * The default cache time for previous returned results
	 * @since	1.0.0
	 * @access	protected
	 * @const	CACHE_TIME	the preset cache time before a cached result is expired
	 */
    protected const CACHE_TIME = 1 * HOUR_IN_SECONDS;

	/**
	 * The common attributes needed to provide any shortcode created by this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      ChurchSuite    $cs				holds the constructed urls to the ChurchSuite account for the JSON feed
	 * @var      Cs_JSON_API	$api			holds the params needed to construct the JSON API call; manages the API call
	 * @var      array()		$JSON_response	Array of \stdclass objects representing events or groups returned by ChurchSuite
	 * @var      string    		$transient_key	A key to uniquely represent the HTML returned for the JSON response requested
	 * 											Used to identify the cached response for each past call based on its params
	 */
	protected ChurchSuite $cs;
	protected Cs_JSON_API $api;
	protected $JSON_response = null;
	protected string $transient_key = '';
	
	
	/*
	 * Process the supplied attributes to leave only valid parameters, create the URLs
	 * required for the JSON feed from ChurchSuite and create the means to communicate via
	 * that JSON feed.  Also, create the unique cache key appropriate for this query.
	 * 
	 * NOTE: The constructor does NOT get the JSON response so that we can get a previous
	 *       HTML output from cache if one already exists so we can mitigate any possible
	 * 		 delay for the JSON response and the processing of that response.
	 * 
	 *
 	 * @since	1.0.0
	 * @param	array() $atts		An array of strings representing the attributes of the JSON call
	 * 								Mandatory params: church_name - the ChurchSuite recognised name of the church
	 * 								See Cs_JSON_API::PERMITTED_PARAMS for the params permitted
	 * @param	string	$JSON_base	What you are requesting from the ChurchSuite JSON API - one of the
	 * 								constants ChurchSuite::EVENTS or ChurchSuite::GROUPS
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
	 * cache if one already exists.  This function checks if we have a response already, and
	 * if there is no already fetched JSON response we call the API to get a JSON response,
	 * setting the JSON_response property with the response, ready for later processing.
	 * 
	 * This should be called from within your get_response() function.
	 *
 	 * @since	1.0.0
	 */
	protected function get_JSON_response() : void {
		if ( is_null( $this->JSON_response ) ) {
			$this->JSON_response = ( $this->api )->get_response();
		}
	}

	/*
	 * This is the function the child class must implement that will return the HTML
	 * response from the JSON ChurchSuite response.
	 * It should first call $this->get_JSON_response() to fetch the JSON response
	 * and then iterate over the objects of this response to generate the required HTML,
	 * using View instances so that this function has to provide very little new HTML
	 * which merely wraps the events or groups for output.
	 * 
 	 * @since	1.0.0
	 * @return	string 	The string with the HTML of the shortcode response, or '' if an error
	 */
	protected abstract function get_response() : string;
	
	/*
	 * Run the shortcode to produce the HTML output
	 * 
	 * First we check the cache.
	 * If there is a cached HTML response to an earlier query, return it.
	 * If there is no cached response:
	 * 		Call through to get_response using get_response()
	 * 			In get_response() we expect it to get a response from the JSON API, use
	 * 				that to form a new HTML response string for display of the items returned.
	 * 		Add the HTML response string to the cache for later re-use
	 * Finally, we return the HTML response string for Wordpress to display.
	 * 
  	 * @since	1.0.0
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
