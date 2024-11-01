<div class="wrap" id="dailyco-settings">
	<h1><?php _e( 'Daily.co Settings', 'video-conferencing-dailyco' ); ?></h1>
	<?php settings_errors(); ?>
	<form action="options.php" method="post">
		<?php settings_fields( Dailyco_Settings::SLUG ); ?>
		<?php do_settings_sections( Dailyco_Settings::SLUG ); ?>
		<?php submit_button(); ?>
	</form>
</div>
