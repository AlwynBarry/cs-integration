<?php

namespace amb_dev\CSI;


require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-view.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-event.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_View as Cs_View;
use amb_dev\CSI\Cs_Event as Cs_Event;


/**
 * Provides a simple small view on a ChurchSuite Event suitable for lists of events.
 * It only provides the event details (no image or description) ...
 * the name of the event (potentially with a link to that event on ChurchSuite).
 * the date and time of the event (potentially also with an end time and date),
 * and the location name and address.
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
 Class Cs_Compact_Event_View extends Cs_View  {

	/*
	 * The event to be displayed, set via the constructor
	 * @since 1.0.0
	 * @access	protected
	 * @var 	Cs_Event $cs_event	The instance of Cs_Event to be displayed
	 */
	protected Cs_Event $cs_event;

    /*
     * Store the data to be displayed later - the reference to ChurchSuite so we
     * can get the URLs needed for links, and the Event data to be displayed.
     * NOTE: All data in these instances has been sanitized when set and is stored readonly
	 *
 	 * @since	1.0.0
	 * @param	ChurchSuite $cs			the ChurchSuite object from which we can get URL references
	 * @param	Cs_Event	$cs_event	the Event object which is to be displayed
    */
	public function __construct( ChurchSuite $cs, Cs_Event $cs_event ) {
		parent::__construct( $cs );
		$this->cs_event = $cs_event;
	}

	/*
	 * Return a string of HTML output representing a single event.
	 * NOTE: All data to be output has been sanitized when set and is stored readonly
	 * 
	 * @since 1.0.0
	 * @returns	string	The valid HTML to display a ChurchSuite Cs_Event instance
	 */
	 public function display() : string {
		// Display the outer wrapper, and include the event unique ID
        $output = '<div'
					. ( ( $this->cs_event->is_identifier() ) ? ' id="cs-event-' . $this->cs_event->get_identifier() . '" ' : '' )
					. ' class="cs-card cs-compact-event cs-event-status-' . $this->cs_event->get_status() . '">' . "\n";
		
		// Display the event time and the end time if provided
        if ( $this->cs_event->is_start_date() ) {
            $output .= '    <div class="cs-time"><span class="cs-start-time">' . date_format( $this->cs_event->get_start_date(), 'g:ia' ) . '</span>';
            $output .= ( $this->cs_event->is_end_date() ) ? '-' . '<span class="cs-end-time">' . date_format( $this->cs_event->get_end_date(), 'g:ia' ) . '</span>' : '';
			$output .= '</div>' . "\n";
        }

		// Display the event name in a link if a link is provided
		$output .= '    <h3 class="cs-event-name">' .
					( ( $this->cs_event->is_URL() ) ? '<a class="cs-event-link" href="' . $this->cs_event->get_URL( $this->cs ) . '">' : '' ) .
					$this->cs_event->get_name() .
					( ( $this->cs_event->is_URL() ) ? '</a>' : '' ) .
					'</h3>' . "\n";
	
		// Display the location and address if they have been provided
        $output .= ( $this->cs_event->is_location() ) ? '    <div class="cs-location"><span class="cs-location-gliph">' . $this->cs_event->get_location() . '</span></div>' . "\n" : '';
        $output .= ( $this->cs_event->is_address() ) ? '    <p class="cs-address">' . $this->cs_event->get_address() . '</p>' . "\n" :  '';

        // Close the outer wrapper
        $output .= '  </div>' . "\n";

		return $output;
	}

}
