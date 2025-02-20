<?php

namespace amb_dev\CSI;


require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-group.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-group-view.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_Shortcode as Cs_Shortcode;
use amb_dev\CSI\Cs_Group as Cs_Group;
use amb_dev\CSI\Cs_Group_View as Cs_Group_View;


class CS_Smallgroups_Shortcode extends Cs_Shortcode {

	public function __construct( $atts ) {
		parent::__construct( $atts, ChurchSuite::GROUPS );
	}
		
	/*
	 * Get a JSON response from the API if we don't already have one, and create the HTML.
	 * For each small group we return what the CS_Group_View returns, all within a flex div.
	 * @return: a string with the HTML to render the event list, or '' if the JSON response fails
	 */
	protected function get_response() : string {
		$output = '';
		$this->get_JSON_response();
		if ( ! is_null( $this->JSON_response ) ) {
			$output = '<div class="cs-smallgroups cs-row">' . "\n";
			foreach ( $this->JSON_response as $group_obj ) {
			    $group_view = new Cs_Group_View( $this->cs, new Cs_Group( $group_obj ) );
				$output .= $group_view->display();
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
 *
 * @since 1.0,0
 */
function cs_smallgroups_shortcode( $atts ) {
	return ( new Cs_Smallgroups_Shortcode( $atts ) )->run_shortcode();
}
	
