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


class Cs_Event_Cards_Shortcode extends Cs_Shortcode {

	public function __construct( $atts ) {
		parent::__construct( $atts, ChurchSuite::EVENTS );
	}
		
	/*
	 * Get a JSON response from the API if we don't already have one, and create the HTML.
	 * For each event we return what the Cs_Event_Card returns, all within a flex div.
	 * @return: a string with the HTML to render the events in cards, or '' if the JSON response fails
	 */
	protected function get_response() : string {
		$output = '';
		$this->get_JSON_response();
		if ( ! is_null( $this->JSON_response ) ) {
			$output = '<div class="cs-event-cards cs-row">' . "\n";
			foreach ( $this->JSON_response as $event_obj ) {
			    $event_view = new Cs_Event_Card_View( $this->cs, new Cs_Event( $event_obj ) );
				$output .= $event_view->display();
			}
			$output .= '</div>' . "\n";
		}
		// Return the HTML response
		return $output;
	}

}


/*
 * Shortcode to be used in the content. Displays the featured events in nested DIVs that can be styled.
 *
 * Parameters:
 *   - feed string, REQUIRED - the church name to add to the JSON request (since 1.0.0)
 *   - events integer range 1..18, default 3 (since 1.0.0)
 *
 * @since 1.0,0
 */
function cs_event_cards_shortcode( $atts ) {
	return ( new Cs_Event_Cards_Shortcode( $atts ) )->run_shortcode();
}
	
