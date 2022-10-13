<?php

namespace CP_Live\Services;

class Resi extends Service{
	
	public $id = 'resi';
	
	public function add_actions() {
		parent::add_actions();
	}

	/**
	 * Check the sites to see if any of them are live
	 * 
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function check() {
		$stream = $this->get( 'stream_url', false );

		if ( ! empty( $stream ) ) {
			$xml = simplexml_load_file( $stream );

			if ( ! empty( $xml ) ) {
				$status = $xml->attributes()->{'type'};

			} else {
				$status = 'stream_empty';
			}
		} else {
			$status = 'stream_missing';
		}

		// TODO: maybe create sermon when video goes live

		$this->update( 'status', $status );
		$this->update( 'channel_live', ( 'dynamic' === $status ) );
	}

	/**
	 * Check the status of a channel, return the video_id if live
	 * 
	 * @param $channel_id
	 * @param $api_key
	 *
	 * @return false
	 * @since  1.0.1
	 *
	 * @author Tanner Moushey
	 */
	protected function get_channel_status( $channel_id, $api_key ) {
		$args = [
			'part'      => 'snippet',
			'type'      => 'video',
			'eventType' => 'live',
			'channelId' => $channel_id,
			'key'       => $api_key,
		];

		$url = 'https://www.googleapis.com/youtube/v3/search';

		$search   = add_query_arg( $args, $url );
		$response = wp_remote_get( $search );

		// if we don't have a valid body, bail early
		if ( ! $body = wp_remote_retrieve_body( $response ) ) {
			return false;
		}

		$body = json_decode( $body );

		// make sure we have items
		if ( empty( $body->items ) ) {
			return false;
		}
		
		return $body->items[0]->id->videoId;
	}

	/**
	 * Get the sites to check for a live feed
	 * 
	 * @return array|mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	protected function sites_to_check() {
		if ( ! function_exists( 'cp_locations' ) ) {
			return [];
		}
		
		if ( $sites = get_site_transient( 'cp_sites_to_check' ) ) {
			return $sites;
		}
		
		$sites = [];
		
		do_action( 'cploc_multisite_switch_to_main_site' );

		$locations = \CP_Locations\Models\Location::get_all_locations(true);
		
		foreach( $locations as $location ) {
			if ( $channel_id = get_post_meta( $location->ID, 'youtube_channel_id', true ) ) {
				$sites[ $location->ID ] = [
					'channel'  => $channel_id,
					'times'    => get_post_meta( $location->ID, 'service_times', true ),
					'duration' => get_post_meta( $location->ID, 'live_video_duration', true ),
					'api_key'  => get_post_meta( $location->ID, 'youtube_api_key', true ),
				];
			}
		}
		
		do_action( 'cploc_multisite_restore_current_blog');
		
		set_site_transient( 'cp_sites_to_check', $sites );
		return $sites;
	}

	/**
	 * Check if the provided times fall within the required window.
	 * 
	 * @param $times
	 *
	 * @since  1.0.0
	 * @return bool
	 *
	 * @author Tanner Moushey
	 */
	protected function time_check( $times ) {
		$day       = strtolower( date( 'l', current_time( 'timestamp' ) ) );
		$timestamp = current_time( 'timestamp' );
		$buffer    = 8 * MINUTE_IN_SECONDS; // start watching 15 minutes before the start time
		$duration  = 12 * MINUTE_IN_SECONDS; // how long we'll keep checking after the service should have started. Allow for the initial 15 min. 
		
		if ( empty( $times ) ) {
			return false;
		}
		
		foreach( $times as $time ) {
			if ( $day !== $time['day'] ) {
				continue;
			}

			$start = strtotime( 'today ' . $time['time'], current_time( 'timestamp' ) ) - $buffer;
			$end   = $start + $duration + $buffer;
			
			// if we fall in the window, continue with the check
			if ( $timestamp > $start && $timestamp < $end ) {
				return true;
			}
		}
		
		return false;
	}
	
	public function settings( $cmb ) {
		
		$cmb->add_field( [
			'name'            => __( 'Reusable Embed ID', 'cp-live' ),
			'desc'            => __( 'Add the Embed ID found in the Resi Web Channel embed code.', 'cp-live' ),
			'id'              => 'embed_id',
			'type'            => 'text',
			'sanitization_cb' => 'sanitize_key',
			'escape_cb'       => 'sanitize_key',
		] );

		$cmb->add_field( [
			'name' => __( 'Resi Stream URL', 'cp-live' ),
			'desc' => __( 'Add the Stream URL from Resi Web Channel Profile that ends in Manifest.mpd', 'cp-live' ),
			'id'   => 'stream_url',
			'type' => 'text_url'
		] );

		$cmb->add_field( [
			'name'    => __( 'Channel Status', 'cp-live' ),
			'id'      => 'channel_live',
			'type'    => 'radio_inline',
			'options' => [ 1 => __( 'Live', 'cp-live' ), 0 => __( 'Not Live', 'cp-live' ) ],
			'default' => 0,
		], 5 );
		
	}
	
}