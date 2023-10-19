<?php
/**
 *
 * @class       WPGenius_Events_API
 * @author      Team WPGenius (Makarand Mane)
 * @category    Admin
 * @package     multisite-media-export-import/includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPGenius_Events_API{

	private function __construct(){
		
	} // END public function __construct.
	
	/**
	*	Convert time to another timezone
	*	Require- Source time
	*	Require- Destination timezone
	*	Require- Source timezone Defaults to UTC0
	*	Require- format Defaults to Y-m-d H:i:s
	*	Returns- Converted time
	*/
	public function convert_tz( $time, $to_tz = 'UTC', $from_tz = 'UTC', $format = 'Y-m-d H:i:s' ){	
		$date = new DateTime( $time, new DateTimeZone( $from_tz ) );
		if( $format != "U") $date->setTimezone( new DateTimeZone( $to_tz ) );
		return $date->format( $format );		
	}

	protected function get_timezone_to_timestamp( $timezone, $datetime ){
		global $wpdb;
		$timezone = $wpdb->get_col( $wpdb->prepare( "SELECT timezone_country FROM $wpdb->wgec_timezones WHERE timezone_id=%d ", $timezone ) );
		return $this->convert_tz( $datetime, '', $timezone[0], 'U' );
	}
	
	/**
	*	Convert time to another timezone
	*	Require- Source time
	*	Require- Destination timezone
	*	Require- format Defaults to Y-m-d H:i:s
	*	Returns- Converted time
	*/
	public function date( $timestamp, $to_tz, $format = 'Y-m-d H:i:s' ){	
		$date = new DateTime( '@'.$timestamp );
		$date->setTimezone( new DateTimeZone( $to_tz ) );
		return $date->format( $format );		
	}
	
	public function get_export( $action ){
		
	}	
	
	protected function security_check( $action ){
		if ( isset( $_REQUEST['security'] ) && wp_verify_nonce( $_REQUEST['security'], $action ) ){
			return true;
		}
		wp_send_json_error( array( 'msg'=> __('Invalid security token sent.', 'multisite-media-export-import' ) ) );
	}
	


} // END class WPGenius_Events_API