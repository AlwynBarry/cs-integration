<?php

namespace amb_dev\CSI;


require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-view.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-event.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_View as Cs_View;
use amb_dev\CSI\Cs_Event as Cs_Event;


Class Cs_Event_Card_View extends Cs_View  {

	protected Cs_Event $cs_event;

	public function __construct( ChurchSuite $cs, Cs_Event $cs_event ) {
		parent::__construct( $cs );
		$this->cs_event = $cs_event;
	}

	/*
	 * Return a string of HTML output representing a single event.
	 * All data in the $event instance is sanitized on creation and the Cs_Event class doesn't permit unsanitized data to be held
	 */
	public function display() : string {
        $output = '<div class="cs-card cs-featured">' . "\n";

		$output .= '  <div class="cs-featured-image-area">' . "\n";
		$output .= '    ' . $this->cs_event->get_image_URL() . "\n";
		$output .= '  </div>' . "\n";
		
        $output .= '  <div class="cs-featured-details-area">' . "\n";

		$output .= '<h3>' .
					( ( $this->cs_event->is_URL() ) ? '<a href="' . $this->cs_event->get_URL( $this->cs ) . '">' : '' ) .
					$this->cs_event->get_name() .
					( ( $this->cs_event->is_URL() ) ? '</a>' : '' ) .
					'</h3>' . "\n";
	
        if ( $this->cs_event->is_start_date() ) {
            $output .= '    <div class="cs-calendar"><span>' . date_format( $this->cs_event->get_start_date(),'M jS, Y' ) . '</span></div>' . "\n";
            $output .= '    <div class="cs-time"><span>' . date_format( $this->cs_event->get_start_date(), 'g:ia' );
            $output .= ( $this->cs_event->is_end_date() ) ? '-' . date_format( $this->cs_event->get_end_date(), 'g:ia' ) : '';
			$output .= '</span></div>' . "\n";
        }

        $output .= ( $this->cs_event->is_location() ) ? '    <div class="cs-location"><span>' . $this->cs_event->get_location() . '</span></div>' . "\n" : '';
        $output .= ( $this->cs_event->is_address() ) ? '    <p class="cs-address">' . $this->cs_event->get_address() . '</p>' . "\n" :  '';

        $output .= '  </div>' . "\n";
        $output .= '</div>' . "\n";

		return $output;
	}

}
