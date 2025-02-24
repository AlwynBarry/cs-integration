<?php

namespace amb_dev\CSI;

/**
 * The base class of any item retrieved from the ChurchSuite JSON feed
 * There are some properties common to Events and Groups (the only two 
 * items that are currently available from the feed).  This class provides
 * sanitize methods to obtain this data from the JSON feed /stdobj returned
 * and both test and getter methods to access this sanitized data
 *
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
abstract class Cs_Item {

	/*
	 * The html tags which can appear within the description.  Used to sanitize the description
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @const    array of string => [empty] array   The tags as keys to this array of arrays
	 * 												(Array of arrays required by wp_kses() filter)
	 */
	 protected const VALID_DESCRIPTION_HTML = array( 'br' => array(), 'p' => array(), 'strong' => array(), 'i' => array(), 'b' => array() );


	protected bool $valid = false;

	/**
	 * The common attributes of all JSON returned items in the ChurchSuite JSON feed.
	 * These are set to be either '' or a sanitized string value appropriate to the
	 * data item.  They are readonly so that they cannot be changed once sanitized.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $identifier		The unique event/group identifier for this item (a-z1-9 only)
	 * @var      string    $name			The unique event/group identifier for this item (a-z1-9 only)
	 * @var      string    $image_URL		The URL to the large (1024 width) image for this item (valid URL)
	 * @var      string    $location		The name of the location for this item (A-Za-z only)
	 * @var      string    $description		The description for this item, if any (html filtered)
	 */
	protected readonly string $identifier;
	protected readonly string $name;
	protected readonly string $image_URL;
	protected readonly string $location;
	protected readonly string $description;

    /*
     * Construct the initial values, sanitising all input provided to ensure all data is valid.
	 *
 	 * @since	1.0.0
	 * @param	\stdclass	$JSON_obj	the ChurchSuite JSON object for this item
    */
    public function __construct( \stdclass $JSON_obj ) {
		if ( is_object( $JSON_obj ) ) {
			$this->identifier = $this->sanitize_identifier( $JSON_obj );
			$this->name = $this->sanitize_name( $JSON_obj );
			$this->image_URL = $this->sanitize_image_URL( $JSON_obj );
			$this->location = $this->sanitize_location( $JSON_obj );
			$this->description = $this->sanitize_description( $JSON_obj );
		} else {
			// Set all the strings to default values - usually ''
			// Has to be set here because readonly variables can only be set once
			$this->identifier = '';
			$this->name = 'Unnamed';
			$this->image_URL = '';
			$this->location = '';
			$this->description = '';
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
	 *
 	 * @since	1.0.0
	 * @param	\stdclass	$item_obj	the ChurchSuite JSON object for this item
	 * @return	string					the sanitized to a-z1-9 identifier string, or '' if invalid
	 */
	protected function sanitize_identifier( \stdclass $item_obj ) : string {
		return ( isset( $item_obj->identifier ) )
					? preg_replace( '/[^a-zA-Z0-9]+/', '', $item_obj->identifier )
					: '' ;
	}
	
	/*
	 * Return the item name from the JSON event object, or the string 'Unnamed Event' if the name is missing or malformed
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the identifier is in a different place in the new object
	 *
 	 * @since	1.0.0
	 * @param	\stdclass	$item_obj	the ChurchSuite JSON object for this item
	 * @return	string					the sanitized name of the event, or 'Unnamed' if invalid
	 */
	protected function sanitize_name( \stdclass $item_obj ) : string {
		return ( isset( $item_obj->name ) && ( $item_obj->name !== '' ) ) 
					? htmlspecialchars( $item_obj->name )
					: 'Unnamed';
	}

	/*
	 * Return the item image HTML tag from the JSON event object, or return '' if there is no image URL
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the image URL is in a different place in the new object
	 *
 	 * @since	1.0.0
	 * @param	\stdclass	$item_obj	the ChurchSuite JSON object for this item
	 * @return	string					the sanitized large image HTML tag with URL, or '' if invalid
	 */
	protected function sanitize_image_URL( \stdclass $item_obj ) : string {
		$result = '';
		// Check for a valid image URL ('images' will be null or an empty array if invalid
		if ( isset( $item_obj->images->lg->url ) && ( $item_obj->images->lg->url !== '' ) ) {
			$url = $item_obj->images->lg->url;
			if ( filter_var( $url, FILTER_VALIDATE_URL ) ) { $result = '<img src="'. $url . '">'; }
		}
		return $result;
	}

	/*
	 * Return the item location from the JSON event object, or '' if the location name is missing or malformed
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the location name is in a different place in the new object
	 *
 	 * @since	1.0.0
	 * @param	\stdclass	$item_obj	the ChurchSuite JSON object for this item
	 * @return	string					the sanitized location name of the event, or '' if invalid
	 */
	protected function sanitize_location( \stdclass $item_obj ) : string {
		return ( isset( $item_obj->location->name ) ) 
					? htmlspecialchars( $item_obj->location->name )
					: '' ;
	}

	/*
	 * Return the item description from the JSON event object, or return '' if there is no description
	 * The description is sanitized to contain only the html tags identified in VALID_DESCRIPTION_HTML.
	 * Note: the object parameter must be checked to be a valid object before this is called
	 * Developer Note: Override this function if the description is in a different place in the new object
	 *
 	 * @since	1.0.0
	 * @param	\stdclass	$item_obj	the ChurchSuite JSON object for this item
	 * @return	string					the sanitized description paragraph string, or '' if invalid
	 */
	protected function sanitize_description( \stdclass $item_obj ) : string {
		return ( isset( $item_obj->description ) && ( $item_obj->description !== '' ) )
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
	 *
 	 * @since	1.0.0
	 * @return	bool	true if the event has an identifier from which we can create an event URL
	 */
	public function is_URL() : bool { return $this->identifier !== ''; }

	/*
	 * Get the ChurchSuite URL of the item we are holding data about
	 *
 	 * @since	1.0.0
	 * @param	$cs		The ChurchSuite object that this item has come from
	 * @return	string	a valid URL to the item or '' if there is no item identifier to construct the URL from 
	 */ 
	public abstract function get_URL( ChurchSuite $cs ) : string;

	/*
	 * Get the item name as a string.
	 *
 	 * @since	1.0.0
	 * @return string		a valid event name or 'Unnamed'
	 */
	public function get_name() : string { return $this->name; }
	
	/*
	 * Check if the event has a supplied image
	 *
 	 * @since	1.0.0
	 * @return bool		true if the event has a supplied image
	 */
	public function is_image_URL() : bool { return ( $this->image_URL !== '' ); }

	/*
	 * Get the URL of the image for the event
	 *
 	 * @since	1.0.0
	 * @return string		the (sanitized) image URL or '' if no image was given for this event 
	 */
	public function get_image_URL() : string { return $this->image_URL; }

	/*
	 * Check if the item has a named location
	 *
 	 * @since	1.0.0
	 * @return bool		true if the item has a named location
	 */
	public function is_location() : bool { return ( $this->location !== '' ); }

	/*
	 * Get the named location for the item
	 *
 	 * @since	1.0.0
	 * @return string		the (sanitized) named location or '' if no location name was given for this item
	 */
	public function get_location() : string { return $this->location; }

	/*
	 * Check if the item has a description
	 *
 	 * @since	1.0.0
	 * @return	bool		true if the item has a description
	 */
	public function is_description() : bool { return ( $this->description !== '' ); }

	/*
	 * Get the description for the item
	 *
 	 * @since	1.0.0
	 * @return	string		the (sanitized) description or '' if no description was given for this item
	 */
	public function get_description() : string { return $this->description; }

}
