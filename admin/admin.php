<?php

add_action( 'admin_menu', function ()
{
    add_options_page(
        __( 'Simple GA Ranking', SGA_RANKING_DOMAIN ),
        __( 'Simple GA Ranking', SGA_RANKING_DOMAIN ),
        'manage_options',
        'sga_ranking',
        'sga_ranking_options_page'
    );
});

function sga_ranking_options_page()
{
    echo '<div class="wrap">';

    printf( '<h2>%s</h2>', __( 'Simple GA Ranking', SGA_RANKING_DOMAIN ) );

    echo '<form action="options.php" method="post" id="sga-post">';

    settings_fields( SGA_RANKING_OPTION_NAME );
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

add_action( 'admin_init', function ()
{
    register_setting(
        SGA_RANKING_OPTION_NAME,
        SGA_RANKING_OPTION_NAME,
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
        'cache_expire'  => __( 'Cache Expires (sec)', SGA_RANKING_DOMAIN ),
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

function sga_ranking_section_text()
{
    do_action( 'sga_ranking_section_text' );
}

function sga_ranking_setting_period()
{
    $options = get_option( SGA_RANKING_OPTION_NAME );
    $option_name = 'period';
    if ( isset( $options[$option_name] ) ) {
        $value = (int) $options[$option_name];
    } else {
        $value = (int) apply_filters( 'sga_ranking_default_' . $option_name, 30 );
    }
    printf(
        '<input id="%s" name="%s" size="%d" type="%s" value="%d" /> %s',
        "sga_ranking_{$option_name}",
        "sga_ranking_options[{$option_name}]",
        4,
        'text',
        esc_attr( $value ),
        __( 'day', SGA_RANKING_DOMAIN )
    );
}

function sga_ranking_setting_cache_expire()
{
    $options = get_option( SGA_RANKING_OPTION_NAME );
    $option_name = 'cache_expire';
    if ( isset( $options[$option_name] ) ) {
        $value = (int) $options[$option_name];
    } else {
        $value = (int) apply_filters( 'sga_ranking_default_' . $option_name, 24 * HOUR_IN_SECONDS );
    }
    $value = (int) apply_filters( 'sga_ranking_' . $option_name, $value );
    printf(
        '<input id="%s" name="%s" size="%d" type="%s" value="%d" />',
        "sga_ranking_{$option_name}",
        "sga_ranking_options[{$option_name}]",
        10,
        'text',
        esc_attr( $value )
    );
}

function sga_ranking_setting_display_count()
{
    $options = get_option( SGA_RANKING_OPTION_NAME );
    $option_name = 'display_count';
    if ( isset( $options[$option_name] ) ) {
        $value = (int) $options[$option_name];
    } else {
        $value = (int) apply_filters( 'sga_ranking_default_' . $option_name, 10 );
    }
    printf(
        '<input id="%s" name="%s" size="%d" type="%s" value="%d" />',
        "sga_ranking_{$option_name}",
        "sga_ranking_options[{$option_name}]",
        4,
        'text',
        esc_attr( $value )
    );
}

function sga_ranking_setting_debug_mode()
{
    $options = get_option( SGA_RANKING_OPTION_NAME );
    $option_name = 'debug_mode';
    if ( isset( $options[$option_name] ) ) {
        $value = (int) $options[$option_name];
    } else {
        $value = 0;
    }
    printf(
        '<input id="%s" name="%s" size="%d" type="%s" value="%s" %s />',
        "sga_ranking_{$option_name}",
        "sga_ranking_options[{$option_name}]",
        4,
        'checkbox',
        '1',
        checked( $value, 1 , false )
    );
}

function sga_ranking_options_validate( $input )
{
    $newinput['period'] = absint( $input['period'] );
    $newinput['cache_expire'] = absint( $input['cache_expire'] );
    $newinput['display_count'] = absint( $input['display_count'] );
    $newinput['debug_mode'] = absint( $input['debug_mode'] );
    $newinput = apply_filters( 'sga_ranking_options_validate', $newinput, $input );

    return $newinput;
}

add_action( 'admin_notices', function ()
{
    $token = get_option('gapiwp_token');
    $debug_mode = apply_filters( 'sga_ranking_debug_mode', false );
    if ( $token == '' && ! $debug_mode ) {
        printf(
            '<div class="error">Simple GA Ranking is available OAuth2 authorization. Please set on <a href="%s" >setting panel</a>. ClientLogin is no longer available. Please see <a href="%s" >this link</a></div>',
            admin_url('/options-general.php?page=gapiwp-analytics'),
            'https://developers.google.com/identity/protocols/AuthForInstalledApps'
        );
    }
});