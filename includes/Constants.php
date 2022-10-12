<?php
/**
 * Plugin constants
 */

/**
 * Setup/config constants
 */
if( !defined( 'CP_LIVE_PLUGIN_FILE' ) ) {
	 define ( 'CP_LIVE_PLUGIN_FILE',
	 	dirname( dirname( __FILE__ ) ) . "/cp-live.php"
	);
}
if( !defined( 'CP_LIVE_PLUGIN_DIR' ) ) {
	 define ( 'CP_LIVE_PLUGIN_DIR',
	 	plugin_dir_path( CP_LIVE_PLUGIN_FILE )
	);
}
if( !defined( 'CP_LIVE_PLUGIN_URL' ) ) {
	 define ( 'CP_LIVE_PLUGIN_URL',
	 	plugin_dir_url( CP_LIVE_PLUGIN_FILE )
	);
}
if( !defined( 'CP_LIVE_PLUGIN_VERSION' ) ) {
	 define ( 'CP_LIVE_PLUGIN_VERSION',
	 	'1.0.0'
	);
}
if( !defined( 'CP_LIVE_INCLUDES' ) ) {
	 define ( 'CP_LIVE_INCLUDES',
	 	plugin_dir_path( dirname( __FILE__ ) ) . 'includes'
	);
}
if( !defined( 'CP_LIVE_PREFIX' ) ) {
	define ( 'CP_LIVE_PREFIX',
		'cpc'
   );
}
if( !defined( 'CP_LIVE_TEXT_DOMAIN' ) ) {
	 define ( 'CP_LIVE_TEXT_DOMAIN',
		'cp-live'
   );
}
if( !defined( 'CP_LIVE_DIST' ) ) {
	 define ( 'CP_LIVE_DIST',
		CP_LIVE_PLUGIN_URL . "/dist/"
   );
}

/**
 * Licensing constants
 */
if( !defined( 'CP_LIVE_STORE_URL' ) ) {
	 define ( 'CP_LIVE_STORE_URL',
	 	'https://churchplugins.com'
	);
}
if( !defined( 'CP_LIVE_ITEM_NAME' ) ) {
	 define ( 'CP_LIVE_ITEM_NAME',
	 	'Church Plugins - Live'
	);
}

/**
 * App constants
 */
if( !defined( 'CP_LIVE_APP_PATH' ) ) {
	 define ( 'CP_LIVE_APP_PATH',
	 	plugin_dir_path( dirname( __FILE__ ) ) . 'app'
	);
}
if( !defined( 'CP_LIVE_ASSET_MANIFEST' ) ) {
	 define ( 'CP_LIVE_ASSET_MANIFEST',
	 	plugin_dir_path( dirname( __FILE__ ) ) . 'app/build/asset-manifest.json'
	);
}
