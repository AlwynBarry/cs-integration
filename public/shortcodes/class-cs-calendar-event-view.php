<?php

namespace amb_dev\CSI;


require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-view.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-event.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_View as Cs_View;
use amb_dev\CSI\Cs_Event as Cs_Event;


/**
 * Provides a small view on a ChurchSuite Event suitable for inclusion in an
 * on-screen calendar.  The main view provides the event time and name, with
 * the location name, address and description 'hidden' in a pop-up.
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
 Class Cs_Calendar_Event_View extends Cs_View  {

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
		// Display the event wrapper, and include the event unique ID, the event
		// status and the event category as classes to be styled, if these are set
        $output = '<div'
					. ( ( $this->cs_event->is_identifier() ) ? ' id="cs-event-' . $this->cs_event->get_identifier() . '" ' : '' )
					. ' class="cs-calendar-event'
					. ' cs-event-status-' . $this->cs_event->get_status()
					. ( ( $this->cs_event->is_category() ) ? ' ' . $this->cs_event->get_category_as_html_class() : '' )
					. '">' . "\n";
		
		// Display the caret link to reveal the hidden event details
		$output .= '<button class="cs-clickable-caret" aria-label="Open Modal" onclick="cs_revealEventDetails(this)">' . "\n"
			. '  <svg fill="none" viewBox="0 0 16 16" height="16" width="16" stroke="currentColor">' . "\n"
			. '    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.4 4.5a1 1 0 0 0-.8.6 1 1 0 0 0 .2 1.2l4.5 5c.4.4 1 .4 1.4 0l4.5-5c.3-.3.4-.7.2-1a1 1 0 0 0-.8-.7z" fill="#000" style="stroke-width: 1;"/>' . "\n"
			. '  </svg>' . "\n"
			. '</button>' . "\n";

		// Display the event time and the end time if provided
		$event_time = '';
        if ( $this->cs_event->is_start_date() ) {
            $event_time .= '    <div class="cs-time"><span class="cs-start-time">' . date_format( $this->cs_event->get_start_date(), 'g:ia' ) . '</span>';
            $event_time .= ( $this->cs_event->is_end_date() ) ? '-' . '<span class="cs-end-time">' . date_format( $this->cs_event->get_end_date(), 'g:ia' ) . '</span>' : '';
			$event_time .= '</div>' . "\n";
        }
		if ( $event_time !== '' ) { $output .= $event_time; }
		
		// Display the event name in a link if a link is provided
		$event_name = '    <div class="cs-event-name">' .
					   ( ( $this->cs_event->is_URL() ) ? '<a class="cs-event-link" target="_blank" href="' . $this->cs_event->get_URL( $this->cs ) . '">' : '' ) .
					   $this->cs_event->get_name() .
					   ( ( $this->cs_event->is_URL() ) ? '</a>' : '' ) .
					   '</div>' . "\n";
		$output .= $event_name;
		
		// Display the 'hidden' event details to be shown when hovering over the event
		$output .= '<div class="cs-event-hover-block">'  . "\n";
		$output .= '<button class="cs-clickable-caret" aria-label="Close Modal" onclick="cs_hideEventDetails(this)">' . "\n"
			. '  <svg fill="none" viewBox="0 0 16 16" height="16" width="16" stroke="currentColor">' . "\n"
			. '    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m 3,3 l 10,10"/>' . "\n"
			. '    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m 3,13 l 10,-10"/>' . "\n"
			. '  </svg>' . "\n"
			. '</button>'  . "\n";
		$output .= $event_time . $event_name;
        $output .= ( $this->cs_event->is_location() ) ? '    <div class="cs-location"><span class="cs-location-gliph">' . $this->cs_event->get_location() . '</span></div>' . "\n" : '';
        $output .= ( $this->cs_event->is_address() ) ? '    <div class="cs-address">' . $this->cs_event->get_address() . '</div>' . "\n" :  '';
        $output .= ( $this->cs_event->is_description() ) ? '    <div class="cs-description">' . $this->cs_event->get_description() . '</div>' . "\n" :  '';
		$output .= '</div>' . "\n";
		
        // Close the outer wrapper
        $output .= '  </div>' . "\n";

		return $output;
	}

}
