<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;


/**
 * The base class of any view on the data returned from a JSON API feed
 * A child class should implement the display() function to display a
 * single object from ChurchSuite, which will either be an Event or a Group.
 * Different view classes for an Event or a Group can be used to display
 * the same data in different ways, as appropriate for the website.
 * 
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public/shortcodes
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
 abstract class Cs_View {

	/*
	 * Common data needed to construct the view.  The ChurchSuite instance is
	 * so that URLs can be obtained to provide links the the original ChurchSuite
	 * data, for example a link to an event so you can sign up.
	 * 
	 * @since   1.0.0
	 * @access  protected
	 * @var		ChurchSuite	$cs				An instance of ChurchSuite initialised with the Church Name and JSON feed
	 */
    protected ChurchSuite $cs;
    
    /*
     * Construct the new view instance, with the ChurchSuite instance being used
     * so we can obtain URLs to link to events or groups on ChurchSuite itself
     * 
     * @since 1.0.0
	 * @param	ChurchSuite	$cs		An instance of ChurchSuite initialised with the Church Name and JSON feed
     */
    public function __construct( ChurchSuite $cs ) {
		$this->cs = $cs;
	}

	/*
	 * This function is to be implemented to output all the HTML for an Event or a Group.
	 * The instance of Cs_Event or Cs_Group will be supplied through the constructor of the child class. 
	 */
	public abstract function display() : string;

}
