<?php

add_action( 'admin_menu', function () {
    add_options_page(
        __( 'Simple GA Ranking', SGA_RANKING_DOMAIN ),
        __( 'Simple GA Ranking', SGA_RANKING_DOMAIN ),
        'manage_options',
        'sga_ranking',
        'sga_ranking_options_page'
    );
});

function sga_ranking_options_page() {
    echo '<div class="wrap">';

    printf( '<h2>%s</h2>', __( 'Simple GA Ranking', SGA_RANKING_DOMAIN ) );

    echo '<form action="options.php" method="post" id="sga-post">';

    settings_fields( 'sga_ranking_options' );
    do_settings_sections( 'sga_ranking' );
    do_meta_boxes( 'sga_ranking', 'advanced', '' );

    echo '<p class="submit">';
    printf(
        '<input name="%s" type="submit" value="%s" class="button-primary" />',
        'Submit',
        __( 'save', SGA_RANKING_DOMAIN )
    );
    echo '</p>';

    echo '</form>';

    echo '</div>';
}

add_action( 'admin_init', function () {
    register_setting(
        'sga_ranking_options',
        'sga_ranking_options',
        'sga_ranking_options_validate'
    );

    add_settings_section(
        'sga_ranking_main',
        __( 'Configuration', SGA_RANKING_DOMAIN ),
        'sga_ranking_section_text',
        'sga_ranking'
    );

    $fields = array(
        'period'        => __( 'Period to get the ranking from today', SGA_RANKING_DOMAIN ),
        'display_count' => __( 'Display Count', SGA_RANKING_DOMAIN ),
        'debug_mode'    => __( 'Debug Mode', SGA_RANKING_DOMAIN ),
    );
    foreach ( $fields as $field_name => $description ) {
        add_settings_field(
            'sga_ranking_' . $field_name,
            $description,
            'sga_ranking_setting_' . $field_name,
            'sga_ranking',
            'sga_ranking_main'
        );
    }
});

function sga_ranking_section_text() {
    do_action( 'sga_ranking_section_text' );
}

function sga_ranking_setting_period() {
    $options = get_option( 'sga_ranking_options' );
    $option_name = 'period';

    printf(
        '<input id="%s" name="%s" size="%d" type="%s" value="%s" /> %s',
        "sga_ranking_{$option_name}",
        "sga_ranking_options[{$option_name}]",
        4,
        'text',
        esc_attr( $options[$option_name] ),
        __( 'day', SGA_RANKING_DOMAIN )
    );
}

function sga_ranking_setting_display_count() {
    $options = get_option( 'sga_ranking_options' );
    $option_name = 'display_count';
    
    printf(
        '<input id="%s" name="%s" size="%d" type="%s" value="%s" />',
        "sga_ranking_{$option_name}",
        "sga_ranking_options[{$option_name}]",
        4,
        'text',
        esc_attr( $options[$option_name] )
    );
}

function sga_ranking_setting_debug_mode() {
    $options = get_option( 'sga_ranking_options' );
    $option_name = 'debug_mode';
    
    printf(
        '<input id="%s" name="%s" size="%d" type="%s" value="%s" %s />',
        "sga_ranking_{$option_name}",
        "sga_ranking_options[{$option_name}]",
        4,
        'checkbox',
        '1',
        checked( $options[$option_name], 1 , false )
    );
}

function sga_ranking_options_validate( $input ) {
    $newinput['period'] = absint( $input['period'] );
    $newinput['display_count'] = absint( $input['display_count'] );
    $newinput['debug_mode'] = absint( $input['debug_mode'] );
    $newinput = apply_filters( 'sga_ranking_options_validate', $newinput, $input );

    return $newinput;
}

add_action( 'admin_notices', function () {
    $token = get_option('gapiwp_token');
    
    if ( $token == '' ) {
        printf(
            '<div class="error">Simple GA Ranking is available OAuth2 authorization. Please set on <a href="%s" >setting panel</a>. ClientLogin is no longer available. Please see <a href="%s" >this link</a></div>',
            admin_url('/options-general.php?page=gapiwp-analytics'),
            'https://developers.google.com/identity/protocols/AuthForInstalledApps'
        );
    }
});