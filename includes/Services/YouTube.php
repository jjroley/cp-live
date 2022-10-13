<?php

namespace CP_Live\Services;

class YouTube extends Service{

	public $id = 'youtube';
	
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

		$sites_live_video = get_site_option( 'cp_sites_live_video', [] );
		$sites_live       = get_site_option( 'cp_sites_live', [] );
		
		foreach( $this->sites_to_check() as $site_id => $data ) {
			$live_duration = $data['live_duration'] ?? 6;
			
			if ( empty( $live_duration ) ) {
				$live_duration = 6;
			}
			
			$live_duration = $live_duration * HOUR_IN_SECONDS;
			
			if ( isset( $sites_live[ $site_id ] ) && isset( $sites_live[ $site_id ]['started'] ) ) {
				$duration = time() - $sites_live[ $site_id ]['started'];
				
				// keep live if we are within the live duration window
				if ( $duration < $live_duration ) {
					continue;
				} 
			}
			
			$sites_live[ $site_id ] = false;
			
			// return early if the channel is not set or we don't pass the time check
			if ( empty( $data['api_key'] ) || empty( $data['channel'] ) || ! $this->time_check( $data['times'] ) ) {
				continue;
			}
			
			$video_id = $this->get_channel_status( $data['channel'], $data['api_key'] );

			// if we don't have a video, bail early
			if ( ! $video_id ) {
				continue;
			}

			$sites_live[ $site_id ] = [ 'video_id' => $video_id, 'started' => time() ];
			$sites_live_video[ $site_id ] = $video_id;
		}
	
		// live_video doesn't get overwritten with null values, so it will always have the latest video
		update_site_option( 'cp_sites_live', $sites_live );
		update_site_option( 'cp_sites_live_video', $sites_live_video );
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
			'name'        => __( 'YouTube Channel ID', 'cp-live' ),
			'id'          => 'youtube_channel_id',
			'type'        => 'text',
			'description' => __( 'The ID of the channel to check.', 'cp-live' ),
		], 5 );		

		$cmb->add_field( [
			'name'        => __( 'YouTube API Key', 'cp-live' ),
			'id'          => 'youtube_api_key',
			'type'        => 'text',
			'description' => __( 'Used to connect to the YouTube API.', 'cp-live' ),
		], 5 );

		$cmb->add_field( [
			'name'        => __( 'Live Video Duration', 'cp-live' ),
			'id'          => 'live_video_duration',
			'type'        => 'text',
			'default'     => '6',
			'description' => __( 'How many hours to show this channel as live once the video has started.', 'cp-live' ),
			'attributes'  => array(
				'type'    => 'number',
				'pattern' => '\d*',
			),
		], 5 );

		$cmb->add_field( [
			'name'    => __( 'Channel Status', 'cp-live' ),
			'id'      => 'channel_live',
			'type'    => 'radio_inline',
			'options' => [ 1 => __( 'Live', 'cp-live' ), 0 => __( 'Not Live', 'cp-live' ) ],
			'default' => 0,
		], 5 );

		$cmb->add_field( [
			'name'        => __( 'Video URL', 'cp-live' ),
			'id'          => 'video_url',
			'type'        => 'text_url',
			'description' => __( 'The URL of the most recent or currently live video.', 'cp-live' ),
		], 5 );
	}
	
}