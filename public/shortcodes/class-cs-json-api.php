<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
use amb_dev\CSI\ChurchSuite as ChurchSuite;

/**
 * Constructs and maintains a JSON API feed url, including all parameter processing
 * Works alongside the ChurchSuite class, which provides the base URL for the
 * ChurchSuite login from which we are accessing the API feed.
 * Provides basic sanitization for all supplied parameters.  For the full range of supported
 * parameters, see https://github.com/ChurchSuite/churchsuite-api/blob/master/modules/embed.md#calendar-json-feed
 * 
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
class Cs_JSON_API {

    /*
     * The parameters permitted to be given to create the feed request
     * See https://github.com/ChurchSuite/churchsuite-api/blob/master/modules/embed.md#calendar-json-feed
     * for an explanation of these params for both events and groups.
     * 
     * @since 1.0.0
     */
	protected const PERMITTED_PARAMS = array( 'merge','date_start','date_end','featured','category','categories','site','sites','event','events','q','embed_signup','public_signup','sequence','page' );

   /*
     * The default cache time for previous returned results
	 * @since	1.0.0
	 * @access	protected
	 * @const	CACHE_TIME	the preset cache time before a cached result is expired
	 */
    protected const CACHE_TIME = 4 * HOUR_IN_SECONDS;

	/*
	 * The data required from which the feed URL is created
	 * 
	 * @since   1.0.0
	 * @access  protected
	 * @var     ChurchSuite	$cs				An instance of ChurchSuite initialised with the Church Name and JSON feed desired
	 * @var     int			$num_results	Default of 0 returns all items; any non-negative value is acceptable
     * @var		array()		$params			An associative array of string keys and values
     * 										Only keys in PERMITTED_PARAMS will be stored
     * 										Values are sanitized as alphanumeric or date yyyy-mm-dd only
	 */
	protected ChurchSuite $churchsuite;
    protected int $num_results = 0;
    protected $params = array();
    
    /*
     * Construct the initial values, sanitising all input provided to ensure all data is valid.
     * 
     * @since 1.0.0
     * @param	$cs					An instance of ChurchSuite initialised with the Church Name and JSON feed desired
     * @param	int	$num_results	Default of 0 returns all items; any non-negative value is acceptable
     * @param	array() $params		An associative array of string keys and values
     * 								Only keys in PERMITTED_PARAMS will be accepted
     * 								Values are sanitized as alphanumeric or date yyyy-mm-dd only
     */
    public function __construct ( ChurchSuite $cs, int $num_results = 0, array $atts = array() ) {
		// ChurchSuite class contains the church name, feed desired and generators for the URLs required for the feeds
		$this->churchsuite = $cs;
		// Set the remaining properties.  These functions sanitize the input so only valid values are held
		$this->set_num_results( $num_results );
		$this->add_params( $atts );
	}

	/*
	 * Set any additional parameters for the JSON request to ChurchSuite (e.g. featured="1")
	 *
	 * All supplied parameters and their values are santised before being used.
	 * Only valid ChurchSuite JSON parameters are added - invalid/malformed are discarded
	 * 
	 * If a parameter is repeated, either in the parameters passed in or from previous
	 * calls to addParams() the new value will be written over the old value for that parameter
	 * (ie) the final parameter value for any parameter in the array is the one retained.
	 * 
	 * @since 1.0.0
	 * @param	array() $params		An associative array of string keys and values
     * 								Only keys in PERMITTED_PARAMS will be accepted
     * 								Values are sanitized as alphanumeric or date yyyy-mm-dd only
	 */
	public function add_params( array $atts ) : void {
		foreach ( $atts as $key => $value ) {
			$key = strtolower( trim( $key ) );
			if ( in_array( $key, \amb_dev\CSI\Cs_JSON_API::PERMITTED_PARAMS ) ) {
				$value = strtolower( trim( $value ) );
				if ( preg_match('/^[\w-]+$/', $value ) ) {
					$this->params[ $key ] = $value;
				}
			}
		}
	}

	/*
	 * Set the number of items you are asking to be returned by Churchsuite to the JSON request
	 * If set to 0 (the initial value set) there is no limit on the items to be returned
	 * 
	 * @since 1.0.0
	 * @param	int $num_results	Default of 0 returns all items; any non-negative value is acceptable
	 */ 
	public function set_num_results(int $num_results) : void {
		if ( $num_results >= 0 ) { $this->num_results = $num_results; }
	}
	
	/*
	 * Get the API URL that will be used given the current settings given to this instance
	 * 
	 * @since 1.0.0
	 * @return	string	A valid URL to call the JSON_API on churchsuite
	 * 					Note: never Null because there should always be a valid string,
	 * 						  though the church_name supplied to the $cs instance may have been incorrect
	 */ 
	protected function compose_API_URL() : string {
		$url = ( $this->churchsuite )->get_JSON_URL() . '?num_results=' . $this->num_results;
		foreach ( $this->params as $key => $value ) { $url .= '&' . $key . '=' . $value; }
		return $url;
	}

	/*
	 * Return a string that can be used as a key for transients
	 * 
	 * The key name returned is the name of the plugin preceding a sha1 encoding which
	 * uniquely identifies the JSON api request. The sha1 encoding is calculated from the
	 * api url. Thus the key will correspond to the same data being returned for the same
	 * request when if is used again.
	 * 
	 * @since 1.0.0
	 * @param 	$api_url	The URL that will be used to obtain the ChurchSuite JSON response
	 * @return	string		A transient key uniquely reflecting this plugin and the JSON URL used
	 */
	protected static function get_transient_key( string $api_url ) : string {
		return 'cs_integration_' . sha1( $api_url );
	}

	/*
	 * Request JSON data using the URL details supplied
     * Note: if the church_name given to the ChurchSuite instance was not given correctly
     * 		 or if it was an empty string the eventual JSON call will return a null result.
	 * 
	 * @since 1.0.0
	 * @return null or an array of \stdclass
	 */ 
	public function get_response() {
        // Create the API_URL
        $api_url = $this->compose_API_URL();

        // Find the transient key and check for an existing cached JSON response
        $transient_key = self::get_transient_key( $api_url );

        // Fetch the cached response, if any;  If no cached response, get a new JSON response, and
        // if JSON response is returned, convert it to objects, and store the result in the cache
		if ( false === ( $result = get_transient( $transient_key ) ) ) {
			// Fetch the JSON data from ChurchSuite using the details supplied to construct the API URL
			$json_data = @file_get_contents( $api_url );
			// Change the JSON data into objects of class \stdclass
			if ( $json_data !== false ) { $result = json_decode($json_data); }
			// Put the response into the cache if there is data to be stored
			if ( ( ! is_null( $result ) ) && ( $result !== '' ) ) { set_transient( $transient_key, $result, self::CACHE_TIME ); }
		}

		// Result will be null or an array of \stdclass objects
		return ( $result === '' ) ? null : $result;
    }
	
}

