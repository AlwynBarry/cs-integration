<?php

namespace amb_dev\CSI;


require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-event.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-compact-event-view.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_Shortcode as Cs_Shortcode;
use amb_dev\CSI\Cs_Event as Cs_Event;
use amb_dev\CSI\Cs_Compact_Event_View as Cs_Compact_Event_View;


class Cs_Event_List_Shortcode extends Cs_Shortcode {
	
	protected \DateInterval $interval;

	public function __construct( $atts ) {
		parent::__construct( $atts, ChurchSuite::EVENTS );
		$this->interval = \DateInterval::createFromDateString( '1 day' );
	}

	/*
	 * A helper function to display the date split up into separate spans so it can be styled
	 */
	protected function display_event_date( \DateTime $event_date ) : string {
	    $result = '<div class="cs-date">';
		$result .= '<span class="cs-day">' . $event_date->format( 'D' ) . '</span>';
		$result .= '<span class="cs-date-number">' . $event_date->format( 'd' ) . '</span>';
		$result .= '<span class="cs-month">' . $event_date->format( 'M' ) . '</span>';
		$result .= '<span class="cs-year">' . $event_date->format( 'Y' ) . '</span>';
		$result .= '</div>';
		return $result;
	}

	/*
	 * Get a JSON response from the API if we don't already have one, and create the HTML.
	 * For each date there is only one date output in a left hand column, styled, and then
	 * in the corresponding right hand columns we have each event on that date.
	 * @return: a string with the HTML to render the event list, or '' if the JSON response fails
	 */
	protected function get_response() : string {
		$output = '';
		$this->get_JSON_response();
		if ( ! is_null( $this->JSON_response ) ) {
			$output = '<div class="cs-event-list">';
			$current_date = new \DateTime();
			$current_date->setTime( 0, 0 );
			$current_date->sub( $this->interval );
			foreach ( $this->JSON_response as $event_obj ) {
				// All events are displayed - use the CSS class to hide events you don't want displayed
				$event = new Cs_Event( $event_obj );
				$output .= '  <div class="cs-event-row">' . "\n";
				$output .= '    <div class="cs-date-column">' . "\n";
				$event_date = ( $event->is_start_date() ) ? $event->get_start_date() : $current_date;
				if ( $event_date->diff( $current_date )->d > 0 ) {
					$output .= $this->display_event_date( $event_date );
					$current_date = $event_date;
					$current_date->setTime( 0, 0 );
				}
				$output .= '  </div>' . "\n";
				$output .= '  <div class="cs-event-column">' . "\n";				
				$event_view = new Cs_Compact_Event_View( $this->cs, $event );
				$output .= $event_view->display();
				$output .= '  </div>' . "\n";
				$output .= '</div>' . "\n";
			}
			$output .= '</div>';
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
function cs_event_list_shortcode( $atts ) {
	return ( new Cs_Event_List_Shortcode( $atts ) )->run_shortcode();
}
	
