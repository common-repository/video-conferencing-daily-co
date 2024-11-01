<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dailyco_Shortcode {
	public function __construct() {
		add_shortcode( 'dailyco_meeting', [ $this, 'dailyco_meeting' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );

		add_filter( 'script_loader_tag', [ $this, 'add_crossorigin' ], 10, 3 );
	}

	public function frontend_scripts() {
		wp_register_script( 'dailyco-js', 'https://unpkg.com/@daily-co/daily-js' );
	}

	/**
	 * Modify script tag attributes
	 *
	 * @param string $tag
	 * @param string $handle
	 * @param string $source
	 *
	 * @return string
	 */
	public function add_crossorigin( $tag, $handle, $source ) {
		if ( 'dailyco-js' === $handle ) {
			$tag = '<script type="text/javascript" src="' . $source . '" crossorigin></script>';
		}

		return $tag;
	}

	/**
	 * Dailyco Meeting room shortcode
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function dailyco_meeting( $atts ) {
		$atts = shortcode_atts( [
			'name'                => '',
			'width'               => '100%',
			'height'              => '400px',
			'show_leave_btn'      => true,
			'show_fullscreen_btn' => true,
		], $atts );

		if ( empty( $atts['name'] ) ) {
			return '';
		}

		$room = dailyco()->api->get_room( $atts['name'] );
		if ( is_wp_error( $room ) ) {
			return '';
		}

		wp_enqueue_script( 'dailyco-js' );

		$html = sprintf(
			'<iframe width="%s" height="%s" style="border: none;" id="dailyco-iframe-%s" allow="microphone; camera; autoplay"></iframe>',
			$atts['width'],
			$atts['height'],
			$room['id']
		);

		$html .= <<<SCRIPT
<script>
(function () {
    document.addEventListener( 'DOMContentLoaded', function() {
	    const callFrame = window.DailyIframe.wrap(document.querySelector('#dailyco-iframe-{$room['id']}'), {
	        showLeaveButton: {$atts['show_leave_btn']},
	        showFullscreenButton: {$atts['show_fullscreen_btn']}
	    })
	    callFrame.join({ url: "{$room['url']}" })
    })
})();
</script>
SCRIPT;

		return $html;
	}
}
