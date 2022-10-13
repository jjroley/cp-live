<?php
namespace CP_Live\Services;

use CP_Live\Admin\Settings;

/**
 * Provides the global $cp_live object
 *
 * @author costmo
 */
class _Init {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Store the active service instances
	 * 
	 * @array
	 */
	public $active;

	/**
	 * Only make one instance of _Init
	 *
	 * @return _Init
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof _Init ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor: Add Hooks and Actions
	 *
	 */
	protected function __construct() {
		$this->actions();
	}

	protected function actions() {
		add_action( 'plugins_loaded', [ $this, 'load_services' ] );
	}
	
	/** Actions Methods **************************************/

	/**
	 * Return all available services
	 * 
	 * @return mixed|void
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_available_services() {
		return apply_filters( 'cp_live_available_services', [
			'youtube' => [ 'label' => 'YouTube', 'class' => YouTube::class, 'enabled' => 1 ],
			'resi'    => [ 'label' => 'Resi', 'class' => Resi::class, 'enabled' => 0 ],
		] );
	}

	/**
	 * Return all active services
	 * 
	 * @return mixed|void
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_active_services() {
		$services = [];
		
		foreach( $this->get_available_services() as $service => $data ) {
			if ( Settings::get_advanced( $service . '_enabled', $data['enabled'] ) ) {
				$services[ $service ] = $data;
			}
		}
		
		return apply_filters( 'cp_live_active_services', $services );
	}

	/**
	 * Load the active services
	 * 
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function load_services() {
		foreach( $this->get_active_services() as $service => $data ) {
			$this->active[ $service ] = $data['class']::get_instance();
		}
	}
	
}
