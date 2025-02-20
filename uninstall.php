<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * No uninstall function provided because nothing added to the Wordpress Database
 * 
 * @link       https://https://github.com/AlwynBarry
 * @since      1.0.0
 *
 * @package    Cs_Integration
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
