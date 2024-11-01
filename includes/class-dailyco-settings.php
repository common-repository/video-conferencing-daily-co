<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles view section in the options page
 *
 * @since 1.0.0
 */
class Dailyco_Settings {
	const SLUG = 'dailyco_settings';

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function register_settings() {
		register_setting( self::SLUG, self::SLUG, [ $this, 'validation' ] );

		add_settings_section( 'dailyco-settings', __( 'Configuration', 'video-conferencing-dailyco' ), null, self::SLUG );

		add_settings_field(
			'api_key',
			__( 'API Key', 'video-conferencing-dailyco' ),
			[ $this, 'field_api_key' ],
			self::SLUG,
			'dailyco-settings',
			[
				'label_for' => 'api_key',
				'class'     => 'regular-text',
			]
		);

		add_settings_field(
			'connection_status',
			__( 'Connection Status', 'video-conferencing-dailyco' ),
			[ $this, 'field_connection_status' ],
			self::SLUG,
			'dailyco-settings',
			[
				'label_for' => 'connection_status',
				'class'     => 'regular-text',
			]
		);

		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
	}

	public function page() {
		add_submenu_page(
			'edit.php?post_type=dailyco_room',
			__( 'Settings', 'video-conferencing-dailyco' ),
			__( 'Settings', 'video-conferencing-dailyco' ),
			'manage_options',
			self::SLUG,
			[ $this, 'settings' ]
		);
	}

	public function admin_notices() {
		settings_errors( 'dailyco_errors' );
	}

	public function settings() {
		include_once DAILYCO_DIR . 'templates/admin/settings.php';
	}

	/**
	 * @param array $input
	 *
	 * @return mixed
	 */
	public function validation( array $input ) {
		return $input;
	}

	/**
	 * @param array $args
	 */
	public function field_api_key( array $args ) {
		$options = get_option( self::SLUG );
		$value   = sanitize_text_field( $options[ $args['label_for'] ] ?? '' );
		echo '<input type="text" autocomplete="off" value="' . $value . '" class="' . $args['class'] . '" name="dailyco_settings[' . $args['label_for'] . ']" id="' . $args['label_for'] . '">';
		echo '<p class="description">' . __( 'Find your API key from', 'video-conferencing-dailyco' ) . ' <a rel="nofollow" href="https://dashboard.daily.co/developers" target="_blank">' . __( 'Dashboard - Developers', 'video-conferencing-dailyco' ) . '</a></p>';
	}

	/**
	 * @param array $args
	 */
	public function field_connection_status( array $args ) {
		$options = get_option( self::SLUG );
		$api_key = sanitize_text_field( $options['api_key'] ?? '' );
		$data = '';
		$error = '';

		if ( ! empty( $api_key ) ) {
			$data = dailyco()->api->get();
			if ( is_wp_error( $data ) ) {
				$error = sprintf(
					__( 'error: %s %s', 'video-conferencing-dailyco' ),
					$data->get_error_code(),
					$data->get_error_message()
				);
			}
		}
		$style = 'style="font-weight: 700;' . ( ( is_wp_error( $data ) && $error ) ? 'color: #dc3232' : 'color: #46b450' ) . '"';
		$text  = ( is_wp_error( $data ) && $error ) ? __( 'Disconnected', 'video-conferencing-dailyco' ) : __( 'Connected', 'video-conferencing-dailyco' );
		echo '<p ' . $style . '>' . $text . '<br>' . $error . '</p>';
	}

	public function link_to_page( $args = [] ) {
		$query = http_build_query( $args );
		$path  = 'admin.php?page=' . self::SLUG . ( $query ? "&{$query}" : '' );

		return admin_url( $path );
	}
}
