<?php

namespace CP_Live\Services;

use ChurchPlugins\Models\Log;

class YouTube extends Service{

	public $id = 'youtube';
	
	public function add_actions() {
		parent::add_actions();
	}
	
	/**
	 * Check the status of a channel, update the meta if live
	 * 
	 * @return void
	 * @since  1.0.1
	 *
	 * @author Tanner Moushey
	 */
	public function check() {

		// if we are live and already have a video url, break early
		if ( $this->is_live() && $this->get('video_url') ) {
			return;
		}
		
		$channel_id = $this->get( 'channel_id' );
		$api_key    = $this->get( 'api_key' );
		$type       = $this->get( 'video_type', 'live' );
		
		if ( empty( $channel_id ) || empty( $api_key ) ) {
			return;
		}
		
		$args = [
			'part'      => 'snippet',
			'type'      => 'video',
			'eventType' => $type,
			'channelId' => $channel_id,
			'key'       => $api_key,
		];

		$url = 'https://www.googleapis.com/youtube/v3/search';

		$search   = add_query_arg( $args, $url );
		$response = wp_remote_get( $search );

		Log::insert( [
			'object_type' => 'service-youtube',
			'action'      => 'check',
			'data'        => serialize( $response ),
		] );
	
		// if we don't have a valid body, bail early
		if ( ! $body = wp_remote_retrieve_body( $response ) ) {
			return;
		}

		$body = json_decode( $body );

		// make sure we have items
		if ( empty( $body->items ) ) {
			return;
		}
		
		$url = sprintf( 'https://youtube.com/watch?v=%s', urlencode( $body->items[0]->id->videoId ) );
		
		$this->update( 'video_url', $url );
		
		$this->set_live();
	}

	/**
	 * Return the embed for YouTube
	 * 
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_embed() {
		global $wp_embed;

		if ( ! $video_url = $this->get( 'video_url' ) ) {
			return '';
		}
		
		return $wp_embed->autoembed( $video_url );
	}

	/**
	 * YouTube Settings
	 * 
	 * @param $cmb
	 *
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function settings( $cmb ) {
		// add prefix to fields if we are not in the global context. Other services may use the same id.
		$prefix = 'global' != $this->context ? $this->id . '_' : '';

		$cmb->add_field( [
			'name'        => __( 'YouTube Channel ID', 'cp-live' ),
			'id'          => $prefix . 'channel_id',
			'type'        => 'text',
			'description' => __( 'The ID of the channel to check.', 'cp-live' ),
		] );		

		$cmb->add_field( [
			'name'        => __( 'YouTube API Key', 'cp-live' ),
			'id'          => $prefix . 'api_key',
			'type'        => 'text',
			'description' => __( 'Used to connect to the YouTube API.', 'cp-live' ),
		] );

		$cmb->add_field( [
			'name'        => __( 'YouTube API Key', 'cp-live' ),
			'id'          => $prefix . 'api_key',
			'type'        => 'text',
			'description' => __( 'Used to connect to the YouTube API.', 'cp-live' ),
		] );

		$cmb->add_field( [
			'name'        => __( 'Video Type', 'cp-live' ),
			'id'          => $prefix . 'video_type',
			'type'        => 'radio_inline',
			'description' => __( 'Whether to pull a currently active broadcast (Live) or the next scheduled broadcast (Upcoming).', 'cp-live' ),
			'default'     => 'live',
			'options'     => [
				'live'     => __( 'Live', 'cp-live' ),
				'upcoming' => __( 'Upcoming', 'cp-live' ),
			],
		] );
		
		$cmb->add_field( [
			'name'        => __( 'Video URL', 'cp-live' ),
			'id'          => $prefix . 'video_url',
			'type'        => 'text_url',
			'description' => __( 'The URL of the most recent or currently live video.', 'cp-live' ),
		] );
		
		parent::settings( $cmb );
	}
	
}