<?php

add_action( 'admin_menu', 'sga_ranking_admin_menu' );

function sga_ranking_admin_menu() {
	add_options_page( __( 'Simple GA Ranking', SGA_RANKING_DOMAIN ), __( 'Simple GA Ranking', SGA_RANKING_DOMAIN ), 'manage_options', 'sga_ranking', 'sga_ranking_options_page');
}

function sga_ranking_options_page() {
?>
<div class="wrap">
<?php screen_icon(); ?>

<h2><?php _e( 'Simple GA Ranking', SGA_RANKING_DOMAIN ); ?></h2>

<form action="options.php" method="post">
<?php settings_fields( 'sga_ranking_options' ); ?>
<?php do_settings_sections( 'sga_ranking' ); ?>

<p class="submit"><input name="Submit" type="submit" value="<?php _e( 'save', SGA_RANKING_DOMAIN ) ?>" class="button-primary" /></p>
</form>

</div>
<?php
}

add_action( 'admin_init', 'sga_ranking_admin_init' );

function sga_ranking_admin_init() {
	register_setting( 'sga_ranking_options', 'sga_ranking_options', 'sga_ranking_options_validate' );

	add_settings_section( 'sga_ranking_main', __( 'Configuration', SGA_RANKING_DOMAIN ), 'sga_ranking_section_text', 'sga_ranking' );

	add_settings_field( 'sga_ranking_email', __( 'E-Mail', SGA_RANKING_DOMAIN ), 'sga_ranking_setting_email',
		'sga_ranking', 'sga_ranking_main' );

	add_settings_field( 'sga_ranking_pass', __( 'Password', SGA_RANKING_DOMAIN ), 'sga_ranking_setting_pass',
		'sga_ranking', 'sga_ranking_main' );

	add_settings_field( 'sga_ranking_profile_id',  __( 'Profile ID', SGA_RANKING_DOMAIN ), 'sga_ranking_setting_profile_id',
		'sga_ranking', 'sga_ranking_main' );

//	add_settings_field( 'sga_ranking_start_date', __( 'Start Date', SGA_RANKING_DOMAIN ), 'sga_ranking_setting_start_date',
//		'sga_ranking', 'sga_ranking_main' );
		
//	add_settings_field( 'sga_ranking_end_date', __( 'End Date', SGA_RANKING_DOMAIN ), 'sga_ranking_setting_end_date',
//		'sga_ranking', 'sga_ranking_main' );

//	add_settings_field( 'sga_ranking_domain', __( 'Domain', SGA_RANKING_DOMAIN ), 'sga_ranking_setting_domain',
//		'sga_ranking', 'sga_ranking_main' );

//	add_settings_field( 'sga_ranking_pagePath', __( 'pagePath', SGA_RANKING_DOMAIN ), 'sga_ranking_setting_pagePath',
//		'sga_ranking', 'sga_ranking_main' );

	add_settings_field( 'sga_ranking_period', __( 'Period to get the ranking from today', SGA_RANKING_DOMAIN ), 'sga_ranking_setting_period',
		'sga_ranking', 'sga_ranking_main' );

	add_settings_field( 'sga_ranking_display_count', __( 'Display Count', SGA_RANKING_DOMAIN ), 'sga_ranking_setting_display_count',
		'sga_ranking', 'sga_ranking_main' );
		
	add_settings_field( 'sga_ranking_debug_mode', __( 'Debug Mode', SGA_RANKING_DOMAIN ), 'sga_ranking_setting_debug_mode',
		'sga_ranking', 'sga_ranking_main' );

}

function sga_ranking_section_text() {
}

function sga_ranking_setting_email() {
	$options = get_option( 'sga_ranking_options' );

	echo '<input id="sga_ranking_email" name="sga_ranking_options[email]" size="40" type="text" value="' . esc_attr( $options['email'] ) . '" />';
}

function sga_ranking_setting_pass() {
	$options = get_option( 'sga_ranking_options' );

	echo '<input id="sga_ranking_pass" name="sga_ranking_options[pass]" size="40" type="password" value="' . esc_attr( $options['pass'] ) . '" />';
}

function sga_ranking_setting_profile_id() {
	$options = get_option( 'sga_ranking_options' );

	echo '<input id="sga_ranking_user_profile_id" name="sga_ranking_options[profile_id]" size="40" type="text" value="' . esc_attr( $options['profile_id'] ) . '" />';
}

function sga_ranking_setting_start_date() {
	$options = get_option( 'sga_ranking_options' );
	
	echo '<input id="sga_ranking_start_date" name="sga_ranking_options[start_date]" size="40" type="text" value="' . esc_attr( $options['start_date'] ) . '" /> (YYYY-MM-DD)';
}

function sga_ranking_setting_end_date() {
	$options = get_option( 'sga_ranking_options' );
	
	echo '<input id="sga_ranking_end_date" name="sga_ranking_options[end_date]" size="40 type="text" value="' . esc_attr( $options['end_date'] ) . '" /> (YYYY-MM-DD)';
}

function sga_ranking_setting_domain() {
	$options = get_option( 'sga_ranking_options' );
	
	echo 'http://<input id="sga_ranking_domain" name="sga_ranking_options[domain]" size="40" type="text" value="' . esc_attr( $options['domain'] ) . '" />';
}

function sga_ranking_setting_pagePath() {
	$options = get_option( 'sga_ranking_options' );
	
	echo '<input id="sga_ranking_pagePath" name="sga_ranking_options[pagePath]" size="40" type="text" value="' . esc_attr( $options['pagePath'] ) . '" />';
}

function sga_ranking_setting_period() {
	$options = get_option( 'sga_ranking_options' );
	
	echo '<input id="sga_ranking_period" name="sga_ranking_options[period]" size="4" type="text" value="' . esc_attr( $options['period'] ) . '" /> ' . __( 'day', SGA_RANKING_DOMAIN );
}

function sga_ranking_setting_display_count() {
	$options = get_option( 'sga_ranking_options' );
	
	echo '<input id="sga_ranking_display_count" name="sga_ranking_options[display_count]" size="4" type="text" value="' . esc_attr( $options['display_count'] ) . '" />';
}

function sga_ranking_setting_debug_mode() {
	$options = get_option( 'sga_ranking_options' );
	
	echo '<input id="sga_ranking_debug_mode" name="sga_ranking_options[debug_mode]" size="4" type="checkbox" value="1" ' . checked( $options['debug_mode'], 1 , false ) . '" />';
}

function sga_ranking_options_validate( $input ) {
	$newinput['email'] = trim( $input['email'] );
	$newinput['pass'] = trim( $input['pass'] );
	$newinput['profile_id'] = trim( $input['profile_id'] );
	$newinput['start_date'] = trim( $input['start_date'] );
	$newinput['end_date'] = trim( $input['end_date'] );
	$newinput['domain'] = trim( $input['domain'] );
	$newinput['pagePath'] = trim( $input['pagePath'] );
	$newinput['period'] = absint( $input['period'] );
	$newinput['display_count'] = absint( $input['display_count'] );
	$newinput['debug_mode'] = absint( $input['debug_mode'] );

	return $newinput;
}

?>