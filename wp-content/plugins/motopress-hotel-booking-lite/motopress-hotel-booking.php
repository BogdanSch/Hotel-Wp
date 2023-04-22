<?php

/*
 * Plugin Name: Hotel Booking Lite
 * Plugin URI: https://motopress.com/products/hotel-booking/
 * Description: Manage your hotel booking services. Perfect for hotels, villas, guest houses, hostels, and apartments of all sizes.
 * Version: 4.7.0
 * Requires at least: 4.7
 * Requires PHP: 7.0
 * Author: MotoPress
 * Author URI: https://motopress.com/
 * License: GPLv2 or later
 * Text Domain: motopress-hotel-booking
 * Domain Path: /languages
 */

if ( !function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

$mphbActive = is_plugin_active( 'motopress-hotel-booking/motopress-hotel-booking.php' );

if ( $mphbActive || class_exists( 'HotelBookingPlugin' ) ) { // Second check required when activating Premium version
	add_action( 'admin_notices', 'mphb_show_multiple_instances_notice' );
} else {
	define( 'MPHB_PLUGIN_FILE', __FILE__ );

	require( plugin_dir_path( __FILE__ ) . 'plugin.php' );

	function mphb_plugin_action_links( $links ){
		$links[] = '<a'
			. ' id="mphb-upgrade-plugin-link"'
			. ' href="' . esc_url( admin_url( 'admin.php?page=mphb_premium' ) ) . '"'
			. ' style="color: #008000;"'
			. '>' . __( 'Upgrade', 'motopress-hotel-booking' ) . '</a>';

		return $links;
	}
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mphb_plugin_action_links' );
}
