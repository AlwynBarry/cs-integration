<?php

namespace amb_dev\CSI;


abstract class Cs_Item {

	protected const VALID_DESCRIPTION_HTML = array( 'br' => array(), 'p' => array(), 'strong' => array(), 'i' => array(), 'b' => array() );

	protected bool $valid = false;
	protected readonly string $identifier;
	protected readonly string $name;
	protected readonly string $image_URL;
	protected readonly string $location;
	protected readonly string $description;

    /*
     * Construct the initial values, sanitising all input provided to ensure all data is valid.
     * Params: jsonObj - the ChurchSuite JSON object for this item
     */
    public function __construct( \stdclass $JSON_obj ) {
		if ( is_object( $JSON_obj ) ) {
			$this->identifier = $this->fetch_identifier( $JSON_obj );
			$this->name = $this->fetch_name( $JSON_obj );
			$this->image_URL = $this->fetch_image_URL( $JSON_obj );
			$this->location = $this->fetch_location( $JSON_obj );
			$this->description = $this->fetch_description( $JSON_obj );
		}
	}
	
	/*
	 * ================================================================
	 *     CONSTRUCTOR HELPER FUNCTIONS
	 * ================================================================
	 */


	/*
	 * Return the identifier from the JSON event object
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the identifier is in a different place in the new object
	 */
	protected function fetch_identifier( \stdclass $item_obj ) : string {
		return ( property_exists( $item_obj,'identifier' ) && ( ! is_null( $item_obj->identifier ) ) )
					? preg_replace( '/[^a-zA-Z0-9]+/', '', $item_obj->identifier )
					: '' ;
	}
	
	/*
	 * Return the item name from the JSON event object, or the string 'Unnamed Event' if the name is missing or malformed
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the identifier is in a different place in the new object
	 */
	protected function fetch_name( \stdclass $item_obj ) : string {
		return ( property_exists( $item_obj,'name' ) && ( ! is_null($item_obj->name) ) && ( $item_obj->name != '' ) ) 
					? htmlspecialchars($item_obj->name)
					: 'Unnamed';
	}

	/*
	 * Return the item image URL from the JSON event object, or return '' if there is no image URL
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the image URL is in a different place in the new object
	 */
	protected function fetch_image_URL( \stdclass $item_obj ) : string {
		$result = '';
		// Check for a valid image URL ('images' will be null or an empty array if invalid
		if ( property_exists( $item_obj, 'images' ) && ( ! is_null( $item_obj->images ) ) && ( ! is_array($item_obj->images) )
				&& property_exists( $item_obj->images, 'lg' ) && ( ! is_null( $item_obj->images->lg ) )
				&& property_exists( $item_obj->images->lg, 'url' ) && ( ! is_null ( $item_obj->images->lg->url ) )
				&& ( $item_obj->images->lg->url !== '' ) ) {
			$url = $item_obj->images->lg->url;
			if ( filter_var($url, FILTER_VALIDATE_URL) ) { $result = '<img src="'. $url . '">'; }
		}
		return $result;
	}

	/*
	 * Return the item location from the JSON event object, or '' if the location name is missing or malformed
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the location name is in a different place in the new object
	 */
	protected function fetch_location( \stdclass $item_obj ) : string {
		return ( property_exists( $item_obj, 'location' ) && ( ! is_null($item_obj->location ) ) &&
				 property_exists( $item_obj->location, 'name' ) && ( ! is_null( $item_obj->location->name ) ) ) 
					? htmlspecialchars( $item_obj->location->name )
					: '' ;
	}

	/*
	 * Return the item description from the JSON event object, or return '' if there is no description
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the description is in a different place in the new object
	 */
	protected function fetch_description( \stdclass $item_obj ) : string {
		return ( property_exists( $item_obj, 'description' ) && ( ! is_null( $item_obj->description ) ) && ( $item_obj->description !== '' ) )
					? wp_kses( nl2br( trim( $item_obj->description ) ), self::VALID_DESCRIPTION_HTML )
					: '' ;
	}


	/*
	 * ================================================================
	 *   THE PUBLIC INTERFACE
	 * ================================================================
	 */


	/*
	 * Check if the item has the details from which we can construct a ChurchSuite URL
	 * @return bool - true if the event has an identifier from which we can create an event URL
	 */
	public function is_URL() : bool { return $this->identifier !== ''; }

	/*
	 * Get the ChurchSuite URL of the item we are holding data about
	 * Parameter: CS - The ChurchSuite object that this item has come from
	 * @return string - a valid URL to the item or '' if there is no item identifier to construct the URL from 
	 */ 
	public abstract function get_URL( ChurchSuite $cs ) : string;

	/*
	 * Get the item name as a string.
	 * @return string - a valid event name or 'Unnamed'
	 */
	public function get_name() : string { return $this->name; }
	
	/*
	 * Check if the event has a supplied image
	 * @return bool - true if the event has a supplied image
	 */
	public function is_image_URL() : bool { return ( $this->image_URL !== '' ); }

	/*
	 * Get the URL of the image for the event
	 * @return String - the (sanitized) image URL or '' if no image was given for this event 
	 */
	public function get_image_URL() : string { return $this->image_URL; }

	/*
	 * Check if the item has a named location
	 * @return bool - true if the item has a named location
	 */
	public function is_location() : bool { return ( $this->location !== '' ); }

	/*
	 * Get the named location for the item
	 * @return String - the (sanitized) named location or '' if no location name was given for this item
	 */
	public function get_location() : string { return $this->location; }

	/*
	 * Check if the item has a description
	 * @return bool - true if the item has a description
	 */
	public function is_description() : bool { return ( $this->description !== '' ); }

	/*
	 * Get the description for the item
	 * @return String - the (sanitized) description or '' if no description was given for this item
	 */
	public function get_description() : string { return $this->description; }

}
