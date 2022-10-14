<?php

namespace CP_Live\Setup;

use CP_Live\Admin\Settings;
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

		// legacy shortcode for locations
		if ( Settings::get_advanced( 'cp_locations_enabled', false ) ) {
			add_shortcode( 'cp-live-video', [ $this, 'live_video_cb' ] );
		}

		add_shortcode( 'cp-live', [ $this, 'live_cb' ] );
	}

	protected function actions() {}

	/** Actions ***************************************************/

	public function live_video_cb() {
		ob_start();
		Templates::get_template_part( "shortcodes/live-video" );
		return ob_get_clean();
	}
	
	/**
	 * Print live video shortcode
	 * 
	 * @param $atts
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function live_cb( $atts ) {
		ob_start();
		Templates::get_template_part( "shortcodes/live" );
		return ob_get_clean();
	}
	
}
