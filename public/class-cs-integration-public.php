<?php

namespace amb_dev\CSI;


/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, loads the public dependencies,
 * and enqueues the public-facing stylesheet.
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/public
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
class Cs_Integration_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The Class Names of the classes for communication to the ChurchSuite JSON API,
	 * - the model classes for the data returned in the JSON responses,
	 * - the view classes for display of the data,
	 * - the classes which give the behaviour of the shortcodes,
	 *
	 * @since    1.0.0
	 * @access   private
	 * @const    array of string    $CS_CLASS_NAMES    The class names of the dependencies needed.
	 */
	private const CS_CLASS_NAMES = array(
			'ChurchSuite' => 'class-churchsuite.php',
			'Cs_JSON_API' => 'class-cs-json-api.php',
			'Cs_Item' => 'class-cs-item.php',
			'Cs_Event' => 'class-cs-event.php',
			'Cs_Group' => 'class-cs-group.php',
			'Cs_View' => 'class-cs-view.php',
			'Cs_Event_Card_View' => 'class-cs-event-card-view.php',
			'Cs_Compact_Event_View' => 'class-cs-compact-event-view.php',
			'Cs_Group_View' => 'class-cs-group-view.php',
			'Cs_Event_List_Shortcode' => 'class-cs-event-list-shortcode.php',
			'Cs_Event_Cards_Shortcode' => 'class-cs-event-cards-shortcode.php',
			'Cs_SmallGroups_Shortcode' => 'class-cs-smallgroups-shortcode.php',
		);

	/*
	 * The shortcode names and their corresponding static functions that
	 * will be called to execute the shortcodes.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @const    array of string => string   The shortcode names and their corresponding function names.
	 */
	private const SHORTCODE_FUNCTION_NAMES = array(
			'cs-event-cards' => 'cs_event_cards_shortcode',
			'cs-event-list' => 'cs_event_list_shortcode',
			'cs-smallgroups' => 'cs_smallgroups_shortcode'
		);

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->load_dependencies();

	}

	/**
	 * Load the required dependencies for the public side of this plugin.
	 *
	 * Include the following files that make up the public side of this plugin:
	 *
	 * - all the classes for communication to the ChurchSuite JSON API,
	 * - the model classes for the data returned in the JSON responses,
	 * - the view classes for display of the data,
	 * - the classes which give the behaviour of the shortcodes,
	 * - the static functions that can be called to execute the shortcodes.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Dependencies: the classes that hold and process the JSON API, it's data response and to form the views on the data
		 */
		foreach ( Cs_Integration_Public::CS_CLASS_NAMES as $class_name => $file_name) {
			require_once plugin_dir_path( dirname(__FILE__) ) . 'public/shortcodes/' . $file_name;
		}

	}
		
		
	/**
	 * Register all of the shortcodes for the public-facing functionality of the plugin
	 * Called as part of the start of the plugin execution, as we set up the plugin 
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_shortcodes() {
		
		foreach ( Cs_Integration_Public::SHORTCODE_FUNCTION_NAMES as $shortCodeName => $functionName ) {
			add_shortcode( $shortCodeName, __NAMESPACE__ . "\\" . $functionName );
		}
		
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @access	 public
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cs-integration-public.css', array(), $this->version, 'all' );

	}

	
	/**
	 * Register the JavaScript for the public-facing side of the site.
	 * For this plugin there is no JavaScript used.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cs-integration-public.js', array( 'jquery' ), $this->version, false );

	}

}
