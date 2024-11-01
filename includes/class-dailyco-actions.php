<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dailyco_Actions {
	public function __construct() {
		add_action( 'rwmb_after_save_post', [ $this, 'create_room' ] );
		add_action( 'before_delete_post', [ $this, 'delete_room' ], 0 );
		add_action( 'admin_notices', [ $this, 'admin_error' ] );
	}

	/**
	 * Create or Update Room
	 *
	 * @param int $object_id
	 */
	public function create_room( $object_id ) {
		if ( $parent_id = wp_is_post_revision( $object_id ) ) {
			$object_id = $parent_id;
		}

		$post = get_post( $object_id );
		if ( ! $post ) {
			return;
		}

		if ( $post->post_status !== 'publish' ) {
			return;
		}

		remove_action( 'rwmb_after_save_post', [ $this, 'create_room' ] );
		$new_room = true;
		$api_room = get_post_meta( $object_id, '_dailyco_api_room', true );
		if ( $api_room ) {
			$room = dailyco()->api->get_room( $api_room['name'] );
			if ( ! is_wp_error( $room ) ) {
				$new_room = false;
			}
		}

		$name    = rwmb_get_value( 'dailyco_room_name' );
		$privacy = rwmb_get_value( 'dailyco_room_privacy' );

		$payload = [
			'privacy'    => $privacy,
			'properties' => []
		];

		if ( $new_room ) {
			$payload['name'] = $name;
		}

		foreach ( $this->get_room_properties() as $property ) {
			$property_value = rwmb_get_value( "dailyco_room_{$property}" );
			if ( ! empty( $property_value ) ) {
				$property_value = ( $property_value == '1' || $property_value == '0' )
					? (bool) $property_value
					: $property_value;

				$payload['properties'][ $property ] = $property_value;
			}
		}

		if ( $new_room ) {
			$room = dailyco()->api->create_room( $payload );
		} else {
			$room = dailyco()->api->update_room( $api_room['name'], $payload );
		}

		if ( is_wp_error( $room ) ) {
			set_transient( 'dailyco_room_error', $room, HOUR_IN_SECONDS );
			return;
		}

		delete_transient( 'dailyco_room_error' );

		update_post_meta( $object_id, '_dailyco_api_room', $room );
	}

	/**
	 * Deletes the Room
	 *
	 * @param int $object_id
	 */
	public function delete_room( $object_id ) {
		$post = get_post( $object_id );
		if ( ! $post ) {
			return;
		}

		$api_room = get_post_meta( $object_id, '_dailyco_api_room', true );
		if ( $api_room ) {
			dailyco()->api->delete_room( $api_room['name'] );
		}
	}

	public function admin_error() {
		$error = get_transient( 'dailyco_room_error' );

		if ( ! is_wp_error( $error ) ) {
			return;
		}

		$class   = 'notice notice-error';
		$message = sprintf(
			__( '%s %s', 'video-conferencing-dailyco' ),
			$error->get_error_code(),
			$error->get_error_message()
		);
		printf( '<div class="%1$s"><p><strong>Daily.co ERROR: </strong> %2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	public function add_notice_query_var( $location, $post_id ) {
		remove_filter( 'redirect_post_location', [ $this, 'add_notice_query_var' ], 99 );
		return add_query_arg( [ 'dailco_room_created' => 'ID' ], $location );
	}

	/**
	 * @return string[]
	 */
	protected function get_room_properties() {
		return [
			'nbf',
			'exp',
			'max_participants',
			'autojoin',
			'enable_knocking',
			'enable_screenshare',
			'enable_chat',
			'start_video_off',
			'start_audio_off',
			'owner_only_broadcast',
			'enable_recording',
			'eject_at_room_exp',
			'eject_after_elapsed',
			'lang',
		];
	}
}
