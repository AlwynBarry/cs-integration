<?php

namespace amb_dev\CSI;


require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-event.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-event-card-view.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_Shortcode as Cs_Shortcode;
use amb_dev\CSI\Cs_Event as Cs_Event;
use amb_dev\CSI\Cs_Event_Card_View as Cs_Event_Card_View;


/**
 * A child of Cs_Shortcode to provide the creation of the HTML response for small lists
 * of events (likely less than 12, or potentially only 3 in a single line) as 'cards'
 * with the event image and the event details (without description) provided.
 * 
 * This class only provides the external 'wrapper' HTML for the events, calling a
 * instance of Cs_Event_View to display each event in a card-like style with event photo
 * and event date, time and location details.
 * 
 * Below the class we also provide a function which can be supplied to Wordpress to
 * run the ShortCode.  This function creates an instance of the Shortcode class and calls
 * the run_shortcode() function in the class to run the shortcode with the atts supplied.
 * 
 * To call the shortcode, you must supply the church name used in the normal ChurchSuite
 * web url (e.g. from https://mychurch.churchsuite.com/ - 'mychurch' is the name to supply)
 * Use the church_name="mychurch" parameter to supply the church name.  You can also use
 * any of the event parameters provided by the churchsuite API, listed at:
 * https://github.com/ChurchSuite/churchsuite-api/blob/master/modules/embed.md#calendar-json-feed
 * Also you should supply the parameter num_results="3" (or whatever number you need) to
 * reduce the time of the processing of the call - otherwise you would have returned _all_
 * future events on your ChurchSuite site!
 *
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
 class Cs_Event_Cards_Shortcode extends Cs_Shortcode {

	/*
	 * Process the supplied attributes to leave only valid parameters, create the URLs
	 * required for the JSON feed from ChurchSuite and create the means to communicate via
	 * that JSON feed.  Also, create the unique cache key appropriate for this query.
	 *
 	 * @since	1.0.0
	 * @param	array() $atts		An array of strings representing the attributes of the JSON call
	 * 								Mandatory params: church_name - the ChurchSuite recognised name of the church
	 */
	public function __construct( $atts ) {
		parent::__construct( $atts, ChurchSuite::EVENTS );
	}
		
	/*
	 * Use the JSON response to create the HTML to display the Events.
	 * 
	 * For each event we return what the Cs_Event_Card returns, all within a flex div.
	 * 
 	 * @since	1.0.0
 	 * @param	string	$JSON_response	the array of \stdclass objects from the JSON response
 	 * 									from which the HTML will be created for the shortcode response.
	 * @return	string					the HTML to render the events in cards, or '' if the JSON response fails
	 */
	protected function get_HTML_response( array $JSON_response ) : string {
		$output = '';
		if ( ! is_null( $JSON_response ) ) {
			$output = '<div class="cs-event-cards cs-row">' . "\n";
			foreach ( $JSON_response as $event_obj ) {
				$event = new Cs_Event( $event_obj );
			    $event_view = new Cs_Event_Card_View( $this->cs, $event );
				$output .= $event_view->display();
				// clear the event and view objects as we go so that we keep memory usage low
				unset( $event_view );
				unset( $event );
			}
			$output .= '</div>' . "\n";
		}
		// Return the HTML response
		return $output;
	}

}


/*
 * Shortcode to be used in the content. Displays the requested events as 'cards' that can be styled.
 *
 * @since 1.0.0
 * @param	array()	$atts	Array supplied by Wordpress of params to the shortcode
 * 							church_name="mychurch" is required - with "mychurch" replaced with your church name
 *							num_results="3" is strongly advised - int range 0..; 0=all, 1.. = number of events specificed
 */
function cs_event_cards_shortcode( $atts ) {
	return ( new Cs_Event_Cards_Shortcode( $atts ) )->run_shortcode();
}
	
