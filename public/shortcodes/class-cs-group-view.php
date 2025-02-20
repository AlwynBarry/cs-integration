<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-view.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-group.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_View as Cs_View;
use amb_dev\CSI\Cs_Group as Cs_Group;


Class Cs_Group_View extends Cs_View  {

	protected Cs_Group $cs_group;

	public function __construct( ChurchSuite $cs, Cs_Group $cs_group ) {
		parent::__construct( $cs );
		$this->cs_group = $cs_group;
	}

	/*
	 * Return a string representing the frequency the group meets
	 * Separated out into a function so we can display custom or regular frequency depending on settings
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
	 * All data in the $group instance is sanitized on creation and the CSGroup class doesn't permit unsanitized data to be held
	 */
	public function display() : string {
        $output = '<div class="cs-card cs-smallgroup">' . "\n";

		$output .= '  <div class="cs-smallgroup-image-area">' . "\n";
		$output .= '    ' . $this->cs_group->get_image_URL() . "\n";
		$output .= '  </div>' . "\n";
		
        $output .= '  <div class="cs-smallgroup-details-area">' . "\n";

		$output .= '  <h3>' .
					( ( $this->cs_group->is_URL() ) ? '<a href="' . $this->cs_group->get_URL( $this->cs ) . '">' : '') .
					$this->cs_group->get_name() .
					( ( $this->cs_group->is_URL() ) ? '</a>' : '' ) .
					'</h3>' . "\n";
	
		$output .= $this->display_frequency();

        $output .= ( $this->cs_group->is_location() ) ? '    <div class="cs-location"><span>' . $this->cs_group->get_location() . '</span></div>' . "\n" : '';

        $output .= ( $this->cs_group->is_time_of_meeting() ) ? '    <div class="cs-time"><span>' . $this->cs_group->get_time_of_meeting() . '</span></div>' . "\n" : '';

        $output .= ( $this->cs_group->is_description() ) ? '    <p class="cs-description">' . $this->cs_group->get_description() . '</p>' . "\n" : '';

        $output .= '  </div>' . "\n";
        $output .= '</div>' . "\n";

		return $output;
	}

}
