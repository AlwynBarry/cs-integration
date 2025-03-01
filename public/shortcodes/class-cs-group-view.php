<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-view.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-group.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_View as Cs_View;
use amb_dev\CSI\Cs_Group as Cs_Group;


/**
 * Provides a card-based view on a ChurchSuite Group.
 * The card has an image section and a details section, with the details
 * being the name of the group (potentially with a link to that group on ChurchSuite).
 * the frequency of meeting of the group, day and time,
 * and the location name (but not the address for confidentiality reasons).
 * The description for the group is also provided.
 * All these details are placed within a div that can be styled, and each
 * of the elements can also be styled.
 * 
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
 Class Cs_Group_View extends Cs_View  {

	/*
	 * The group to be displayed, set via the constructor
	 * @since 1.0.0
	 * @access	protected
	 * @var		Cs_Group $cs_group	The instance of Cs_Group to be displayed
	 */
	protected Cs_Group $cs_group;

    /*
     * Store the data to be displayed later - the reference to ChurchSuite so we
     * can get the URLs needed for links, and the Group data to be displayed.
     * NOTE: All data in these instances has been sanitized when set and is stored readonly
	 *
 	 * @since	1.0.0
	 * @param	ChurchSuite $cs			the ChurchSuite object from which we can get URL references
	 * @param	Cs_Group	$cs_group	the Group object which is to be displayed
    */
	public function __construct( ChurchSuite $cs, Cs_Group $cs_group ) {
		parent::__construct( $cs );
		$this->cs_group = $cs_group;
	}

	/*
	 * Return a string representing the frequency the group meets
	 * Separated out into a function so we can display custom or regular frequency depending on settings
	 * 
	 * @since 1.0.0
	 * @return	string	an HTML representation of the frequency of meeting
	 */
	protected function display_frequency() : string {
		$output = '';
		if ( ( $this->cs_group->is_frequency() ) || ( $this->cs_group->is_custom_frequency() ) ) {
			$output .= '<div class="cs-calendar"><span>';
			// If there is a frequency, and it is not 'custom', then output that frequency
			if ( ( $this->cs_group->is_frequency() ) && ( ! $this->cs_group->is_custom_frequency() ) ) {
				$output .= ucfirst( $this->cs_group->get_frequency() );
				if ( $this->cs_group->is_day_of_week() ) { $output .= $this->cs_group->get_day_of_week(); }
			} else {
				// If there is a custom frequency, then output the custom frequency
				if ( $this->cs_group->is_custom_frequency() ) {
					$output .= $this->cs_group->get_custom_frequency();
				}
			}
			$output .= '</span></div>' . "\n";
		}
		return $output;
	}

	/*
	 * Return a string of HTML output representing a single group.
	 * NOTE: All data to be output has been sanitized when set and is stored readonly
	 * 
	 * @since 1.0.0
	 * @returns	string	The valid HTML to display a ChurchSuite Cs_Group instance
	 */
	public function display() : string {
 		// Display the group card, and include the group unique ID
        $output = '<div'
					. ( ( $this->cs_group->is_identifier() ) ? ' id="cs-group-' . $this->cs_group->get_identifier() . '"' : '' )
					. ' class="cs-card cs-group">' . "\n";

		// Display the image area
		$output .= '  <div class="cs-group-image-area">' . "\n";
		$output .= '    ' . $this->cs_group->get_image_URL() . "\n";
		$output .= '  </div>' . "\n";
		
		// Display the details area
        $output .= '  <div class="cs-group-details-area">' . "\n";

		// Display the group name in a link if a link is provided
		$output .= '  <div class="cs-group-name">' .
					( ( $this->cs_group->is_URL() ) ? '<a class="cs-group-link" href="' . $this->cs_group->get_URL( $this->cs ) . '">' : '') .
					$this->cs_group->get_name() .
					( ( $this->cs_group->is_URL() ) ? '</a>' : '' ) .
					'</div>' . "\n";
	
		// Display frequency of meeting, location and time and day of meeting, if provided
		$output .= $this->display_frequency();

        $output .= ( $this->cs_group->is_location() ) ? '    <div class="cs-location"><span class="cs-location-gliph">' . $this->cs_group->get_location() . '</span></div>' . "\n" : '';

        $output .= ( $this->cs_group->is_time_of_meeting() ) ? '    <div class="cs-time"><span class="cs-time-gliph">' . $this->cs_group->get_time_of_meeting() . '</span></div>' . "\n" : '';

		// Display the description of the group
        $output .= ( $this->cs_group->is_description() ) ? '    <div class="cs-description">' . $this->cs_group->get_description() . '</div>' . "\n" : '';

		// Close the details area
        $output .= '  </div>' . "\n";
        
        // Close the group card
        $output .= '</div>' . "\n";

		return $output;
	}

}
