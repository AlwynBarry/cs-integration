<?php

namespace amb_dev\CSI;


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 * 
 * This plugin defines shortcodes and so does not have admin functionality.
 * This class has been left in case of future expansion / update to add admin functions.
 * 
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cs_Integration
 * @subpackage Cs_Integration/admin
 * @author     Alwyn Barry <alwyn_barry@yahoo.co.uk>
 */
class Cs_Integration_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * We currently are not using the admin area of this plugin
		 */

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cs-integration-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/*
		 * We currently are not using the admin area of this plugin
		*/
		
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cs-integration-admin.js', array( 'jquery' ), $this->version, false );

	}

}
