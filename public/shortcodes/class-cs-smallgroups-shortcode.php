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


/**
 * A child of Cs_Shortcode to provide the creation of the HTML response for SmallGroups.
 * This class only provides the external 'wrapper' HTML for the groups, calling a
 * instance of Cs_Group_View to display each group in a card-like style with group photo
 * and group meeting time and location details.
 * 
 * Below the class we also provide a function which can be supplied to Wordpress to
 * run the ShortCode.  This function creates an instance of the Shortcode class and calls
 * the run_shortcode() function in the class to run the shortcode with the atts supplied.
 * 
 * To call the shortcode, you must supply the church name used in the normal ChurchSuite
 * web url (e.g. from https://mychurch.churchsuite.com/ - 'mychurch' is the name to supply)
 * Use the church_name="mychurch" parameter to supply the church name.  You can also use
 * any of the group parameters provided by the churchsuite API, listed at:
 * https://github.com/ChurchSuite/churchsuite-api/blob/master/modules/embed.md#calendar-json-feed
 *
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
class CS_Smallgroups_Shortcode extends Cs_Shortcode {

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
		parent::__construct( $atts, ChurchSuite::GROUPS );
	}
		
	/*
	 * Get a JSON response from the API if we don't already have one, and create the HTML.
	 * For each small group we return what the CS_Group_View returns, all within a flex div.
	 * 
 	 * @since	1.0.0
	 * @return	string	the HTML to render the group list, or '' if the JSON response fails
	 */
	protected function get_response() : string {
		$output = '';
		$this->get_JSON_response();
		if ( ! is_null( $this->JSON_response ) ) {
			$output = '<div class="cs-smallgroups cs-row">' . "\n";
			foreach ( $this->JSON_response as $group_obj ) {
				$group = new Cs_Group( $group_obj );
			    $group_view = new Cs_Group_View( $this->cs, $group );
				$output .= $group_view->display();
				// clear the group and view objects as we go so that we keep memory usage low
				unset( $group_view );
				unset( $group );
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
 * @since 1.0.0
 * @param	array()	$atts	Array supplied by Wordpress of params to the shortcode
 * 							church_name="mychurch" is required - with "mychurch" replaced with your church name
 */
function cs_smallgroups_shortcode( $atts ) {
	return ( new Cs_Smallgroups_Shortcode( $atts ) )->run_shortcode();
}
	
