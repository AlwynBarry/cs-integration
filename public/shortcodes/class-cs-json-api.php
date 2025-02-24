<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
use amb_dev\CSI\ChurchSuite as ChurchSuite;


class Cs_JSON_API {

    /*
     * See https://github.com/ChurchSuite/churchsuite-api/blob/master/modules/embed.md#calendar-json-feed for an explanation of these params
     */
	protected const PERMITTED_PARAMS = array( 'merge','date_start','date_end','featured','category','categories','site','sites','event','events','q','embed_signup','public_signup','sequence','page' );

	protected ChurchSuite $churchsuite;
    protected int $num_results = 0;
    protected $params = array();
    
    /*
     * Construct the initial values, sanitising all input provided to ensure all data is valid.
     * Params:
     * churchSuite - An instance of ChurchSuite initialised with the Church Name and JSON feed desired
     * num_results - default of 0 returns all items; any non-negative value is acceptable
     * params - An associative array of keys and values; Only keys in PERMITTED_PARAMS will be accepted. Values are alphanumeric or date yyyy-mm-dd only
     * If the churchName is not given correctly or is an empty string the eventual JSON call will return a null result.
     */
    public function __construct ( ChurchSuite $cs, int $num_results = 0, array $atts = array() ) {
		// ChurchSuite class contains the church name, feed desired and generators for the URLs required for the feeds
		$this->churchsuite = $cs;
		// Set the remaining properties.  These functions sanitize the input so only valid values are held
		$this->set_num_results( $num_results );
		$this->add_params( $atts );
	}

	/*
	 * Set any additional parameters for the JSON request to ChurchSuite (e.g. featured=1)
	 *
	 * All supplied parameters and their values are santised before being used.
	 * Only valid ChurchSuite JSON parameters are added - invalid/malformed are discarded
	 * 
	 * If a parameter is repeated, either in the parameters passed in or from previous
	 * calls to addParams() the new value will be written over the old value for that parameter
	 * (ie) the final parameter value for any parameter in the array is the one retained.
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
	 */ 
	public function set_num_results(int $num_results) : void {
		if ( $num_results >= 0 ) { $this->num_results = $num_results; }
	}
	
	/*
	 * Get the API URL that will be used given the current settings given to this instance
	 * Return: string (never Null because there should always be a valid string, though the churchName may be incorrect)
	 */ 
	private function compose_API_URL() : string {
		$url = ( $this->churchsuite )->get_JSON_URL() . '?num_results=' . $this->num_results;
		foreach ( $this->params as $key => $value ) { $url .= '&' . $key . '=' . $value; }
		return $url;
	}

	/*
	 * Return a string that can be used as a key for transients
	 * 
	 * The key name returned is the name of the plugin preceding a sha1 encoding which
	 * uniquely identifies the JSON request. The sha1 encoding is calculated from the
	 * class of the calling object, the api string being used, and the param keys and
	 * values with special characters and spaces removed. Thus the key will correspond
	 * to the same data being returned for the same request from that object if it were
	 * called again - i.e. we can rely on the cached copy being what would be received anew.
	 * 
	 * @param $obj the calling object so we can add its class name to the transient key - usually $this
	 * @param $name the called API from ChurchSuite::BASE_ITEM_URLS
	 */
	public function get_transient_key( $obj, string $apiName ) : string {
		// Strip the leading namespace from the classname (not needed) and add the api being called
		$result = substr(get_class( $obj) , ($p = strrpos(static::class, '\\')) !== false ? $p + 1 : 0) . $apiName;
		// Add the num_results parameter, since if this changed a new request is needed
		$result .= 'num_results' . $this->num_results;
		// Add all the other parameters, since changes in them would flag up a new request is needed
		foreach ( $this->params as $key => $value ) { $result .= $key . $value; }
		// Remove any spaces or special characters, since they don't change the uniqueness
		$result = preg_replace( "/[^a-zA-Z0-9]/", '', $result );
		// Return the name of the plugin prepended to the sha1 encoding of the constructed string
		return 'cs_integration_' . sha1( $result );
	}

	/*
	 * Request JSON data using the URL details supplied
	 * 
	 * @return null or an array of \stdclass
	 */ 
	public function get_response() {
        // … construct and get request
        $result = null;
        // Fetch the JSON data from ChurchSuite
		$json_data = @file_get_contents( $this->compose_API_URL() ); 
		// Change the JSON data into objects of class \stdclass      
		if ( $json_data !== false ) { $result = json_decode($json_data); }
		// Result will be null or an array of objects
		return $result;
    }
	
}

