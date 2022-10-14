<?php
namespace CP_Live\Services;

use CP_Live\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Setup for custom post types
 *
 * @author costmo
 */
abstract class Service {

	public $id;
	
	/**
	 * @var self
	 */
	protected static $_instance;

	/**
	 * Only make one instance of Service
	 *
	 * @return self
	 */
	public static function get_instance() {
		$class = get_called_class();

		if ( ! self::$_instance instanceof $class ) {
			self::$_instance = new $class();
		}

		return self::$_instance;
	}

	/**
	 * Get things started
	 *
	 * @since   1.0
	 */
	protected function __construct() {
		$this->add_actions();
	}

	/**
	 * Default action-adder for this CPT-descendants of this class
	 *
	 * @return void
	 */
	public function add_actions() {
		add_action( 'cp_live_check', [ $this, 'check' ] );
		add_action( 'cp_live_settings', [ $this, 'settings' ] );
	}
	
	abstract function check();

	/**
	 * The settings for this Service
	 * 
	 * @param $cmb2 | The CMB2 object to attach the fields to
	 *
	 * @return mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	abstract function settings( $cmb2 );

	/**
	 * Get service setting
	 * 
	 * @param $key
	 * @param $default
	 *
	 * @return mixed|void
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get( $key, $default = '' ) {
		return Settings::get_service( $key, $this->id, $default );
	}

	/**
	 * Update service setting
	 * 
	 * @param $key
	 * @param $value
	 *
	 * @return mixed|void
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function update( $key, $value ) {
		return Settings::update_service( $key, $this->id, $value );
	}
}
