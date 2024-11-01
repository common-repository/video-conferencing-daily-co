<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dailyco_API {
	/**
	 * API Base URL
	 *
	 * @var string
	 */
	const BASE_URL = 'https://api.daily.co/v1/';

	/**
	 * @var Dailyco_API
	 */
	private static $instance = null;

	/**
	 * @return Dailyco_API
	 */
	public static function loader() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Dailyco_API ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * @return string[]
	 */
	public function get_headers() {
		$settings = get_option( 'dailyco_settings' );

		return [
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . sanitize_text_field( $settings['api_key'] ),
		];
	}

	/**
	 * @param string $path
	 * @param array $payload
	 *
	 * @return array|WP_Error
	 */
	public function get( $path = '', $payload = [] ) {
		return $this->http( $path, $payload );
	}

	/**
	 * @param string $path
	 * @param array $payload
	 *
	 * @return array|WP_Error
	 */
	public function post( $path = '', $payload = [] ) {
		return $this->http( $path, $payload, 'POST' );
	}

	/**
	 * @param string $path
	 * @param array $payload
	 *
	 * @return array|WP_Error
	 */
	public function delete( $path = '', $payload = [] ) {
		return $this->http( $path, $payload, 'DELETE' );
	}

	/**
	 * @param string $room_name
	 *
	 * @return array|WP_Error
	 */
	public function get_room( $room_name = '') {
		return $this->get( sprintf( '/rooms/%s', $room_name ) );
	}

	/**
	 * @param array $payload
	 *
	 * @return array|WP_Error
	 */
	public function create_room( $payload = [] ) {
		return $this->post( '/rooms', $payload );
	}

	/**
	 * @param string $room_name
	 * @param array $payload
	 *
	 * @return array|WP_Error
	 */
	public function update_room( $room_name = '', $payload = [] ) {
		return $this->post( sprintf( '/rooms/%s', $room_name ), $payload );
	}

	/**
	 * @param string $room_name
	 * @param array $payload
	 *
	 * @return array|WP_Error
	 */
	public function delete_room( $room_name = '' ) {
		return $this->delete( sprintf( '/rooms/%s', $room_name ) );
	}

	/**
	 * @param string $path
	 * @param string $method
	 * @param array $payload
	 *
	 * @return array|WP_Error
	 */
	protected function http( $path = '', $payload = [], $method = 'GET' ) {
		$args = [
			'method'  => $method,
			'headers' => $this->get_headers(),
		];

		if ( ! empty( $payload ) ) {
			$args['body'] = json_encode( $payload );
		}

		$res = wp_remote_request( $this->get_url( $path ), $args );
		if ( is_wp_error( $res ) ) {
			return $res;
		}

		$res = json_decode( wp_remote_retrieve_body( $res ), true );

		if ( $res['error'] ) {
			return new WP_Error( $res['error'], $res['info'] );
		}

		return $res;
	}

	/**
	 * Full qualified request url
	 *
	 * @return string
	 */
	private static function get_url( $path ) {
		return self::BASE_URL . $path;
	}
}
