<?php
/**
 * Plugin Name: Video Conferencing Daily.co
 * Description: Ship 1-click video chat faster and smarter
 * Version: 1.0.0
 * Author: qfnetwork, rahilwazir
 * Author URI: https://www.qfnetwork.org
 * License: GPLv3
 * License URI: https://opensource.org/licenses/gpl-3.0.html
 * Text Domain: video-conferencing-dailyco
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dailyco {

	/**
	 * @var null|Dailyco_API
	 */
	public $api = null;

	/**
	 * @var Dailyco
	 */
	private static $instance = null;

	private function __construct() {
		load_plugin_textdomain( 'video-conferencing-dailyco', false, basename( dirname( __FILE__ ) ) . '/languages/' );

		$this->setup_constants();
		$this->includes();
		$this->hooks();
	}

	private function hooks() {
		// add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'settings_link' ], 10, 1 );
	}

	private function includes() {
		require __DIR__ . '/vendor/autoload.php';
		require __DIR__ . '/vendor/wpmetabox/meta-box/meta-box.php';

		include_once 'includes/class-dailyco-api.php';
		include_once 'includes/class-dailyco-posttype.php';
		include_once 'includes/class-dailyco-settings.php';
		include_once 'includes/class-dailyco-actions.php';
		include_once 'includes/class-dailyco-shortcode.php';
	}

	private function setup_constants() {
		define( 'DAILYCO_DIR', plugin_dir_path( __FILE__ ) );
		define( 'DAILYCO_URL', plugin_dir_url( __FILE__ ) );
	}

	public static function loader() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Dailyco ) ) {
			self::$instance = new self;
		}

		self::$instance->api      = Dailyco_API::loader();
		self::$instance->posttype = new Dailyco_Posttype;
		self::$instance->settings = new Dailyco_Settings;
		self::$instance->actions  = new Dailyco_Actions;
		self::$instance->shortcode  = new Dailyco_Shortcode;

		return self::$instance;
	}

	/**
	 * Add settings link on plugin page
	 *
	 * @return void
	 */
	public function settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'edit.php?post_type=dailyco_room&page=dailyco_settings' ) . '">' . __( 'Settings', 'video-conferencing-dailyco' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}
}

function dailyco() {
	if ( version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
		return Dailyco::loader();
	}

	add_action(
		'admin_notices',
		function () {
			$class   = 'notice notice-error';
			$message = __( 'You need to upgrade your PHP to atleast version 7.0.0. Currently %s is used. To resolve the upgrade please contact your server host.', 'video-conferencing-dailyco' );
			printf( '<div class="%1$s"><p><strong>%2$s:</strong> %3$s</p></div>', esc_attr( $class ), 'Daily.co', esc_html( sprintf( $message, PHP_VERSION ) ) );
		}
	);
}

dailyco();
