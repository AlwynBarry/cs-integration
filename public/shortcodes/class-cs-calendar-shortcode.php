<?php

namespace amb_dev\CSI;


require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-event.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cs-calendar-event-view.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;
use amb_dev\CSI\Cs_Shortcode as Cs_Shortcode;
use amb_dev\CSI\Cs_Event as Cs_Event;
use amb_dev\CSI\Cs_Calendar_Event_View as Cs_Calendar_Event_View;


/**
 * A child of Cs_Shortcode to provide the creation of the HTML response to display
 * a monthly calendar of events with the event locatiom and description hidden to
 * be revealed with a mouse hover.
 * 
 * This class provides the logic for the display of a month calendar, calling a
 * instance of Cs_Calendar_Event_View to display each event.
 * 
 * Below the class we also provide a function which can be supplied to Wordpress to
 * run the ShortCode.  This function creates an instance of the Shortcode class and calls
 * the run_shortcode() function in the class to run the shortcode for the current month.
 * 
 * To call the shortcode, you must supply the church name used in the normal ChurchSuite
 * web url (e.g. from https://mychurch.churchsuite.com/ - 'mychurch' is the name to supply)
 * Use the church_name="mychurch" parameter to supply the church name.  You may also
 * provide a month="yyyy-mm" parameter to identify the month of the events you want to display.
 * You can also use any of the event parameters provided by the churchsuite API, listed at:
 * https://github.com/ChurchSuite/churchsuite-api/blob/master/modules/embed.md#calendar-json-feed
 *
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
 class Cs_Calendar_Shortcode extends Cs_Shortcode {

	/*
	 * Constant values created to prevent unnecessary re-creation of values used in expressions
	 */ 
	protected readonly \DateInterval $one_day;
	protected readonly \DateInterval $one_week;

	protected \DateTime $today;
	protected \DateTime $requested_date;
	protected \DateTime $month_start;
	protected \DateTime $month_end;
	protected \DateTime $date_from;
	protected \DateTime $date_to;

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
		$this->one_day = \DateInterval::createFromDateString( '1 day' );
		$this->one_week = \DateInterval::createFromDateString( '1 week' );
		$this->today = new \DateTime();
		$this->today->setTime( 0, 0 );
		$this->requested_date = ( isset( $atts[ 'date_from' ] ) ) ? $atts[ 'date_from' ] : clone $this->today;
		$this->requested_date->setTime( 0, 0 );
		$this->month_start = self::get_month_start( $this->requested_date );
		$this->month_end = self::get_month_end( $this->requested_date ); 
		$this->date_from = self::get_sunday_before_month( $this->month_start ); 
		$this->date_to = self::get_saturday_after_month( $this->month_start );
		$atts[ 'date_start' ] ??= $this->date_from->format( 'Y-m-d' );
		$atts[ 'date_end' ] ??= $this->date_to->format( 'Y-m-d' );
		$atts[ 'num_results' ] = '0';
		$atts[ 'merge' ] = 'show_all';
		parent::__construct( $atts, ChurchSuite::EVENTS );
	}

	/*
	 * Returns the number of days in a given month and year, taking into account leap years.
	 *
	 * @since 1.0.1
	 * @param	\DateTime	$date	a valid DateTime object
	 * @return	int					the number of days in the month the date is within
	 */
	protected static function days_in_month( \DateTime $date ) : int {
		$month = (int) $date->format('m');
		$year = (int) $date->format('Y');
		return $month == 2 ? ( $year % 4 ? 28 : ( $year % 100 ? 29 : ( $year % 400 ? 28 : 29 ) ) ) : ( ( $month - 1 ) % 7 % 2 ? 30 : 31 );
	}

	/*
	 * Returns the date of the first day in the month 
	 *
	 * @since 1.0.1
	 * @param	\DateTime	$date	a valid DateTime object
	 * @return	\DateTime			the date of the start of the month
	 */
	protected static function get_month_start( \DateTime $date ) : \DateTime {
		return new  \DateTime( $date->format('Y-m') . '-01' );
	}

	/*
	 * Returns the date of the last day in the month 
	 *
	 * @since 1.0.1
	 * @param	\DateTime	$date	a valid DateTime object
	 * @return	\DateTime			the date of the last day of the month
	 */
	protected static function get_month_end( \DateTime $date ) : \DateTime {
		return new \DateTime( $date->format('Y-m') . '-' . self::days_in_month( $date ) );
	}

	/*
	 * Returns the date of the Sunday before the first day in the month 
	 *
	 * @since 1.0.1
	 * @param	\DateTime	$date	a valid DateTime object
	 * @return	\DateTime			the date of the Sunday before the first day in the month
	 */
	protected static function get_sunday_before_month( \DateTime $date ) : \DateTime {
		return ( self::get_month_start( $date ) )->modify( 'last sunday' );
	}
	
	/*
	 * Returns the date of the Saturday after the first day in the month 
	 *
	 * @since 1.0.1
	 * @param	\DateTime	$date	a valid DateTime object
	 * @return	\DateTime			the date of the Saturday after the last day in the month
	 */
	protected static function get_saturday_after_month( \DateTime $date ) : \DateTime {
		return ( self::get_month_end( $date ) )->modify( 'next saturday' );
	}
	
	/*
	 * Returns the true if the date passed in is the month being requested 
	 *
	 * @since 1.0.1
	 * @param	\DateTime	$date	a valid DateTime object
	 * @return	bool				true if the date passed in is in the month
	 */
	protected function is_date_in_month( $date ) {
		return ( ( $date >= $this->month_start ) && ( $date <= $this->month_end ) );
	}

	/*
	 * Returns the true if the date passed in is equal to today's date 
	 *
	 * @since 1.0.1
	 * @param	\DateTime	$date	a valid DateTime object
	 * @return	bool				true if the date passed in is the same as today's date
	 */
	protected function is_date_today( $date ) {
		return ( $date == $this->today );
	}

	/*
	 * Returns the top of the month table with the month name and the day headers 
	 *
	 * @since 1.0.1
	 * @param	\DateTime	$date	a valid DateTime object
	 * @return	\string				a string that gives the top of the month table
	 */
	protected function get_month_table_top( \DateTime $date ) : string {
		// Output the month header - the locale sensitive name of the month
		$output = '<div class="cs-calendar-month-header">' . $date->format( 'F' ) . '</div>' . "\n";
		$output .= '<div class="cs-calendar-table">' . "\n";
		$output .= '  <table class="cs-responsive-table">' . "\n";
		$output .= '    <thead>' . "\n";
		$output .= '      <tr class="cs-calendar-days-header">' . "\n";
		
		// Add the day headers for the table using the week within which is the supplied date
		// Doing this computationally ensures that we have localised day names produced.
		$sunday_date = self::get_sunday_before_month( $date );
		$saturday_date = clone $sunday_date;
		$saturday_date->add( $this->one_week );
		$period = new \DatePeriod( $sunday_date, $this->one_day, $saturday_date );
		foreach ( $period as $day ) {
			$output .= '        <th class="cs-day-header">' . $day->format( 'D' ) . '</th>' . "\n";
		}

		// Output the end of the table row and header
		$output .= '      </tr>' . "\n";
		$output .= '    </thead>'. "\n";
        return $output;
	}

	/*
	 * Returns the bottom of the month table 
	 *
	 * @since 1.0.1
	 * @return	\string				a string that gives the top of the month table
	 */
	protected function get_month_table_bottom() : string {
		$output = '    </tbody>' . "\n";
		$output .= '  </table>' . "\n";
		$output .= '</div>' . "\n";
		return $output;
	}

	/*
	 * Returns the top of a day cell 
	 *
	 * @since 1.0.1
	 * @return	\string				a string that gives the top of a day cell
	 */
	protected function get_day_top( \DateTime $date, bool $in_month, bool $is_today ) : string {
		// Output the start of the table cell to display one day in the calendar
		$output = '<td class="cs-calendar-date-cell'
		                . ( ( $in_month ) ? ' cs-calendar-in-month' : ' cs-calendar-outside-month' )
		                . ( ( $is_today ) ? ' cs-calendar-today' : '' )
		                . '">' . "\n";
		// Output the date.  Many of these fields are not displayed, but it allows styling choices
		$day = (int) $date->format( 'j' );
	    $output .= '<div class="cs-date' . ( ( $day === 1 ) ? ' cs-first-day' : '' ) . '">';
		$output .= '<span class="cs-day">' . $date->format( 'D' ) . '</span>';
		$output .= '<span class="cs-date-number">' . $day . '</span>';
		$output .= '<span class="cs-month">' . $date->format( 'F' ) . '</span>';
		$output .= '<span class="cs-year">' . $date->format( 'Y' ) . '</span>';
		$output .= '</div>';
		// Output the start of the  div containing the details of the events on this date
		$output .= '	<div class="cs-calendar-date-cell-inner">' . "\n";
		$output .= '		<div class="cs-day-content">' . "\n";
		return $output;
	}

	/*
	 * Returns the bottom of a day cell 
	 *
	 * @since 1.0.1
	 * @return	\string				a string that gives the bottom of a day cell
	 */
	protected function get_day_bottom() : string {
		$output = '		    </div>' . "\n";
		$output .= '	</div>' . "\n";
		$output .= '</td>' . "\n";
		return $output;
	}

	/*
	 * Use the JSON response to create the HTML to display the Events.
	 * 
	 * For each event we return what the Cs_Event_Card returns, all within a flex div.
	 * 
 	 * @since	1.0.1
 	 * @param	string	$JSON_response	the array of \stdclass objects from the JSON response
 	 * 									from which the HTML will be created for the shortcode response.
	 * @return	string					the HTML to render the events in cards, or '' if the JSON response fails
	 */
	protected function get_HTML_response( array $JSON_response ) : string {
		$output = '';
		if ( ! is_null( $JSON_response ) ) {
			$output = '<div class="cs-calendar">' . "\n";
			$output .= $this->get_month_table_top( $this->requested_date );
			$day_count = 0;
			$date = clone $this->date_from;
			// Output the start of the first row of the table, for the first week
			$output .= '<tr class="cs-calendar-row">' . "\n";
			$output .= self::get_day_top( $date, $this->is_date_in_month( $date ), $this->is_date_today( $date ) );
			// Iterate over all events in the month; If none, the later loop will print out the blank month
			foreach ( $JSON_response as $event_obj ) {
				$event = new Cs_Event( $event_obj );
				$event_date = clone $event->get_start_date();
				$event_date->setTime( 0, 0 );
				// Fill in any empty dates before this event, up to the event date or the end of the month displayed
				while ( ( $date < $event_date ) && ( $date < $this->date_to ) ) {
					$output .= $this->get_day_bottom();
					$date->add( $this->one_day );
					$day_count++;
					// Output a new row for each new week
					$output .= ( ( $day_count % 7 ) == 0 ) ? '</tr>' . "\n" . '<tr class="cs-calendar-row">' . "\n" : '';
					$output .= self::get_day_top( $date, $this->is_date_in_month( $date ), $this->is_date_today( $date ) );
				}
				// We should now be in the date of the event, but check just in case
				if ( ( $date == $event_date ) && ( $date <= $this->date_to ) ) {
					$event_view = new Cs_Calendar_Event_View( $this->cs, $event );
					$output .= $event_view->display();
					// clear the event and view objects as we go so that we keep memory usage low
					unset( $event_view );
					unset( $event );
				}
			}
			$output .= $this->get_day_bottom();
			$date->add( $this->one_day );
			$day_count++;
			$output .= ( ( $day_count % 7 ) == 0 ) ? '</tr>' . "\n" . '<tr class="cs-calendar-row">' . "\n" : '';
			// Fill in any empty dates after the final event
			while ( $date <= $this->date_to ) {
				// Output a new cell for each new day
				$output .= self::get_day_top( $date, $this->is_date_in_month( $date ), $this->is_date_today( $date ) );
				$date->add( $this->one_day );
				$day_count++;
				// Output a new row for each new week
				if ( ( $day_count % 7 ) == 0 ) {
					$output .= '</tr>' . "\n";
					if ( $date <= $this->date_to ) { $output .= '<tr  class="cs-calendar-row">' . "\n"; }
				}
			}
			$output .= $this->get_month_table_bottom();
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
function cs_calendar_shortcode( $atts ) {
	return ( new Cs_Calendar_Shortcode( $atts ) )->run_shortcode();
}
