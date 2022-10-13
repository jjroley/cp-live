<?php

namespace CP_Live\Setup;

use CP_Live\Templates;

/**
 * Setup plugin ShortCodes initialization
 */
class ShortCodes {

	/**
	 * @var ShortCodes
	 */
	protected static $_instance;

	/**
	 * The ID for this service
	 * 
	 * @var 
	 */
	public $id;
	
	/**
	 * Only make one instance of ShortCodes
	 *
	 * @return ShortCodes
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof ShortCodes ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor
	 *
	 */
	protected function __construct() {
		add_shortcode( 'cp-live-video', [ $this, 'live_video_cb' ] );
		add_shortcode( 'cp-groups', [ $this, 'groups_cb' ] );
		add_shortcode( 'cp-groups-filter', [ $this, 'groups_filter_cb' ] );
	}

	protected function actions() {}

	/** Actions ***************************************************/

	public function live_video_cb() {
		global $wp_embed;
		$sites_live_video = get_site_option( 'cp_sites_live_video', [] );

		if ( empty( $sites_live_video ) ) {
			return __( 'No live feeds were found', 'cp-theme-default' );
		}

		$output = '<div class="cp-live-container">';
		
		$location_id = apply_filters( 'cp_live_video_location_id_default', get_query_var( 'cp_location_id' ) );
		
		if ( $location_id ) {
			if ( empty( $sites_live_video[ $location_id ] ) ) {
				return __( 'No live feeds were found', 'cp-theme-default' );
			} else {
				$output .= $wp_embed->autoembed( sprintf( 'https://youtube.com/watch?v=%s', urlencode( $sites_live_video[ $location_id ] ) ) );
			}
		} else {
			foreach( $sites_live_video as $location_id => $feed ) {
				$output .= sprintf( '<hr /><div class="cp-live-location ast-row"><div class="cp-live-location--video ast-grid-common-col ast-width-md-6">%s</div><div class="cp-live-location--info ast-width-md-6 ast-grid-common-col">%s</div></div>', 
					$wp_embed->autoembed( sprintf( 'https://youtube.com/watch?v=%s', urlencode( $feed ) ) ),
					sprintf( '<h3><a href="%s">%s</a></h3><p>%s</p>', get_permalink( $location_id ) . '/live/', get_the_title( $location_id ), do_shortcode( "[cp-location-data field=service_times location=$location_id]" ) ),
				);
			} 
		}
		
		$output .= '</div>';
		
		return $output;
	}
	
	/**
	 * Print groups
	 * 
	 * @param $atts
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function groups_cb( $atts ) {
		ob_start();
		Templates::get_template_part( "shortcodes/group-list" );
		return ob_get_clean();
	}

	/**
	 * Print groups filters
	 * 
	 * @param $atts
	 *
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function groups_filter_cb( $atts ) {
		ob_start();
		Templates::get_template_part( "shortcodes/filter" );
		return ob_get_clean();
		
	}
	
}
