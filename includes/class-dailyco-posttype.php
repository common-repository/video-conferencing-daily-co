<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dailyco_Posttype {
	public function __construct() {
		add_action( 'init', [ $this, 'register' ], 0 );

		add_filter( 'rwmb_meta_boxes', [ $this, 'meta_boxes' ] );
	}

	public function register() {
		$labels = [
			'name'                  => _x( 'Rooms', 'Rooms Post type', 'video-conferencing-dailyco' ),
			'singular_name'         => _x( 'Room', 'Rooms Post type', 'video-conferencing-dailyco' ),
			'menu_name'             => __( 'Daily.co', 'video-conferencing-dailyco' ),
			'name_admin_bar'        => __( 'Dailyco Room', 'video-conferencing-dailyco' ),
			'archives'              => __( 'Room Archives', 'video-conferencing-dailyco' ),
			'attributes'            => __( 'Room Attributes', 'video-conferencing-dailyco' ),
			'parent_item_colon'     => __( 'Parent Room:', 'video-conferencing-dailyco' ),
			'all_items'             => __( 'Rooms', 'video-conferencing-dailyco' ),
			'add_new_item'          => __( 'Create a New Room', 'video-conferencing-dailyco' ),
			'add_new'               => __( 'Add New Room', 'video-conferencing-dailyco' ),
			'new_item'              => __( 'New Room', 'video-conferencing-dailyco' ),
			'edit_item'             => __( 'Edit Room', 'video-conferencing-dailyco' ),
			'update_item'           => __( 'Update Room', 'video-conferencing-dailyco' ),
			'view_item'             => __( 'View Room', 'video-conferencing-dailyco' ),
			'view_items'            => __( 'View Rooms', 'video-conferencing-dailyco' ),
			'search_items'          => __( 'Search Room', 'video-conferencing-dailyco' ),
			'not_found'             => __( 'Not found', 'video-conferencing-dailyco' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'video-conferencing-dailyco' ),
			'featured_image'        => __( 'Featured Image', 'video-conferencing-dailyco' ),
			'set_featured_image'    => __( 'Set featured image', 'video-conferencing-dailyco' ),
			'remove_featured_image' => __( 'Remove featured image', 'video-conferencing-dailyco' ),
			'use_featured_image'    => __( 'Use as featured image', 'video-conferencing-dailyco' ),
			'insert_into_item'      => __( 'Insert into item', 'video-conferencing-dailyco' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'video-conferencing-dailyco' ),
			'items_list'            => __( 'Rooms list', 'video-conferencing-dailyco' ),
			'items_list_navigation' => __( 'Rooms list navigation', 'video-conferencing-dailyco' ),
			'filter_items_list'     => __( 'Filter items list', 'video-conferencing-dailyco' ),
		];

		$args = [
			'label'               => __( 'Room', 'video-conferencing-dailyco' ),
			'description'         => __( 'Daily.co Rooms', 'video-conferencing-dailyco' ),
			'labels'              => $labels,
			'supports'            => [ 'title' ],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
			'show_in_rest'        => false,
			'menu_icon'           => DAILYCO_URL . 'assets/images/icon.png',
		];

		register_post_type( 'dailyco_room', $args );
	}

	public function meta_boxes( $meta_boxes ) {
		$post_id  = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		$api_room = get_post_meta( $post_id, '_dailyco_api_room', true );

		$room = null;
		if ( $api_room ) {
			$room = dailyco()->api->get_room( $api_room['name'] );
			if ( is_wp_error( $room ) ) {
				$room = null;
			}
		}

		$meta_boxes[] = [
			'title'      => __( 'General Settings', 'video-conferencing-dailyco' ),
			'post_types' => 'dailyco_room',

			'fields'     => [
				[
					'name'       => __( 'Room name', 'video-conferencing-dailyco' ),
					'desc'       => sprintf(
						'%s<br>%s',
						__( 'If left blank, a random room name will be generated. Room name cannot be changed after creation.', 'video-conferencing-dailyco' ),
						__( 'Character limitations: Max 34 characters. Use: A-Z, a-z, 0-9, -, _', 'video-conferencing-dailyco' )
					),
					'id'         => 'dailyco_room_name',
					'type'       => 'text',
					'std'        => $room ? $room['name'] : '',
					'attributes' => [
						'disabled' => $room ? true : false
					],
					'sanitize_callback' => function( $value, $field, $old_value, $object_id ) {
						if ( ! $object_id ) {
							return $value;
						}

						$api_room = get_post_meta( $object_id, '_dailyco_api_room', true );

						$room = null;
						if ( $api_room ) {
							$room = dailyco()->api->get_room( $api_room['name'] );
							if ( ! is_wp_error( $room ) ) {
								$value = $room['name'];
							}
						}

						return $value;
					}
				],
				[
					'name'    => __( 'Privacy', 'video-conferencing-dailyco' ),
					'desc'    => __( 'Limit who can access the room. Room owners can always join any room they create.', 'video-conferencing-dailyco' ),
					'id'      => 'dailyco_room_privacy',
					'type'    => 'radio',
					'std'     => 'public',
					'options' => [
						'public'  => __( 'Public: anyone with the link can join', 'video-conferencing-dailyco' ),
						'org'     => __( 'Org: only team members can join', 'video-conferencing-dailyco' ),
						'private' => __( 'Private: requires a token to join', 'video-conferencing-dailyco' ),
					],
					'inline'  => false,
				],
				[
					'name'    => __( 'Cameras on start', 'video-conferencing-dailyco' ),
					'desc'    => __( 'Set whether users join with their cameras on or off. Users can still control their cameras manually.', 'video-conferencing-dailyco' ),
					'id'      => 'dailyco_room_start_video_off',
					'type'    => 'radio',
					'std'     => 'true',
					'options' => [
						'true'  => __( 'Off', 'video-conferencing-dailyco' ),
						'false' => __( 'On', 'video-conferencing-dailyco' ),
					],
				],
				[
					'name'    => __( 'Microphones on start', 'video-conferencing-dailyco' ),
					'desc'    => __( 'Set whether users join with their microphones on or off. Users can still control their microphones manually.', 'video-conferencing-dailyco' ),
					'id'      => 'dailyco_room_start_audio_off',
					'type'    => 'radio',
					'std'     => 'true',
					'options' => [
						'true'  => __( 'Off', 'video-conferencing-dailyco' ),
						'false' => __( 'On', 'video-conferencing-dailyco' ),
					],
				],
				[
					'name'    => __( 'Language', 'video-conferencing-dailyco' ),
					'desc'    => __( 'Set the language for this room\'s video call UI.', 'video-conferencing-dailyco' ),
					'id'      => 'dailyco_room_lang',
					'type'    => 'radio',
					'std'     => 'en',
					'options' => [
						'nl'   => __( 'Dutch', 'video-conferencing-dailyco' ),
						'en'   => __( 'English (default)', 'video-conferencing-dailyco' ),
						'fr'   => __( 'French', 'video-conferencing-dailyco' ),
						'fi'   => __( 'Finnish', 'video-conferencing-dailyco' ),
						'de'   => __( 'German', 'video-conferencing-dailyco' ),
						'pt'   => __( 'Portuguese', 'video-conferencing-dailyco' ),
						'user' => __( 'Use browser setting', 'video-conferencing-dailyco' ),
					],
					'inline'  => false,
				],
			],
			'validation' => [
				'rules' => [
					'dailyco_room_privacy' => [
						'required' => true,
					],
					'dailyco_room_name'    => [
						'maxlength' => 34
					]
				],
			],
		];

		$meta_boxes[] = [
			'title'      => __( 'Advanced Settings', 'video-conferencing-dailyco' ),
			'post_types' => 'dailyco_room',

			'fields' => [
				[
					'name' => __( 'Maximum participants', 'video-conferencing-dailyco' ),
					'desc' => __( 'Set the maximum number of participants allowed in a room at the same time.', 'video-conferencing-dailyco' ),
					'id'   => 'dailyco_room_max_participants',
					'type' => 'number',
				],
				[
					'name' => __( 'Eject after', 'video-conferencing-dailyco' ),
					'desc' => sprintf(
						'%s<br>%s',
						__( 'Eject participants this many seconds after they join the meeting.', 'video-conferencing-dailyco' ),
						__( 'Enter time in seconds (i.e. 3600).', 'video-conferencing-dailyco' )
					),
					'id'   => 'dailyco_room_eject_after_elapsed',
					'type' => 'number',
				],
				[
					'name'    => __( 'Screen sharing', 'video-conferencing-dailyco' ),
					'desc'    => __( 'Participants can share their screens during calls.', 'video-conferencing-dailyco' ),
					'id'      => 'dailyco_room_enable_screenshare',
					'type'    => 'radio',
					'std'     => 'true',
					'options' => [
						'true'  => __( 'On', 'video-conferencing-dailyco' ),
						'false' => __( 'Off', 'video-conferencing-dailyco' ),
					],
				],
				[
					'name'    => __( 'Text chat', 'video-conferencing-dailyco' ),
					'desc'    => __( 'Participants can send text chat during calls.', 'video-conferencing-dailyco' ),
					'id'      => 'dailyco_room_enable_chat',
					'type'    => 'radio',
					'std'     => 'false',
					'options' => [
						'true'  => __( 'On', 'video-conferencing-dailyco' ),
						'false' => __( 'Off', 'video-conferencing-dailyco' ),
					],
				],
				[
					'name'    => __( 'Owner only broadcast', 'video-conferencing-dailyco' ),
					'desc'    => __( 'Only meeting owners can screen share, record and use their camera/mic.', 'video-conferencing-dailyco' ),
					'id'      => 'dailyco_room_owner_only_broadcast',
					'type'    => 'radio',
					'std'     => 'false',
					'options' => [
						'true'  => __( 'On', 'video-conferencing-dailyco' ),
						'false' => __( 'Off', 'video-conferencing-dailyco' ),
					],
				],
				[
					'name'    => __( 'Auto join', 'video-conferencing-dailyco' ),
					'desc'    => __( 'Choose whether to display the join page.', 'video-conferencing-dailyco' ),
					'id'      => 'dailyco_room_autojoin',
					'type'    => 'radio',
					'std'     => 'false',
					'options' => [
						'true'  => __( 'Display join page', 'video-conferencing-dailyco' ),
						'false' => __( 'Skip join page', 'video-conferencing-dailyco' ),
					],
					'inline'  => false,
				],
				[
					'name'      => __( 'NBF (Not before)', 'video-conferencing-dailyco' ),
					'desc'      => __( 'Participants cannot join before this time (UTC).', 'video-conferencing-dailyco' ),
					'id'        => 'dailyco_room_nbf',
					'type'      => 'datetime',
					'timestamp' => false,
				],
				[
					'name'      => __( 'EXP (Expires)', 'video-conferencing-dailyco' ),
					'desc'      => __( 'Participants cannot join after this time (UTC).', 'video-conferencing-dailyco' ),
					'id'        => 'dailyco_room_exp',
					'type'      => 'datetime',
					'timestamp' => false,
				],
				[
					'name'    => __( 'Eject on EXP', 'video-conferencing-dailyco' ),
					'desc'    => __( 'Eject participants when the EXP (Expires) time is reached.', 'video-conferencing-dailyco' ),
					'id'      => 'dailyco_room_eject_at_room_exp',
					'type'    => 'radio',
					'std'     => 'false',
					'options' => [
						'true'  => __( 'On', 'video-conferencing-dailyco' ),
						'false' => __( 'Off', 'video-conferencing-dailyco' ),
					],
				],
			],
		];

		if ( $room ) {

			$generated_shortcode = sprintf(
				'[dailyco_meeting name="%s" width="100%%" height="400px" show_leave_btn="true" show_fullscreen_btn="true"]',
				$room['name']
			);

			$meta_boxes[] = [
				'title'      => __( 'Room Information', 'video-conferencing-dailyco' ),
				'post_types' => 'dailyco_room',
				'context'    => 'side',
				'fields'     => [
					[
						'name' => __( 'ID', 'video-conferencing-dailyco' ),
						'id'   => 'dailyco_room_created_id',
						'type' => 'custom_html',
						'std'  => '<div class="rwmb-input"><input size="30" value="' . $room['id'] . '" readonly type="text" id="dailyco_room_created_id" class="rwmb-text" onclick="this.select()"></div>',
					],
					[
						'name' => __( 'Name', 'video-conferencing-dailyco' ),
						'id'   => 'dailyco_room_created_name',
						'type' => 'custom_html',
						'std'  => '<div class="rwmb-input"><input size="30" value="' . $room['name'] . '" readonly type="text" id="dailyco_room_created_name" class="rwmb-text" onclick="this.select()"></div>',
					],
					[
						'name' => __( 'URL', 'video-conferencing-dailyco' ),
						'id'   => 'dailyco_room_created_url',
						'type' => 'custom_html',
						'std'  => '<div class="rwmb-input"><input size="30" value="' . $room['url'] . '" readonly type="text" id="dailyco_room_created_url" class="rwmb-text" onclick="this.select()"></div>',
					],
					[
						'name' => __( 'Shortcode', 'video-conferencing-dailyco' ),
						'id'   => 'dailyco_room_created_shortcode',
						'type' => 'custom_html',
						'std'  => '<div class="rwmb-input"><input size="30" value="' . esc_attr( $generated_shortcode ) . '" readonly type="text" id="dailyco_room_created_shortcode" class="rwmb-text" onclick="this.select()"></div>',
					],
				],
			];

		}

		return $meta_boxes;
	}
}
