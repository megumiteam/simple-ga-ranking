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

    add_filter( 'sga_ranking_section_period', function( $input_tag ){
        return $input_tag . ' ' . __( 'day', SGA_RANKING_DOMAIN );
    });

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

function sga_ranking_setting( $options, $option_name, $size = 4, $type = 'text', $input_value = false )
{
    $value = null;
    if ( isset( $options[$option_name] ) ) {
        $value = (int) $options[$option_name];
    } else {
        $value = (int) apply_filters( 'sga_ranking_default_' . $option_name, $value );
    }
    $input_tag = sprintf(
        '<input id="%s" name="%s" size="%d" type="%s" value="%d" %s />',
        "sga_ranking_{$option_name}",
        "sga_ranking_options[{$option_name}]",
        $size,
        $type,
        esc_attr( $input_value ? $input_value : $value ),
        'checkbox' !== $type ? '' : checked( $value, 1 , false )
    );

    return apply_filters( 'sga_ranking_section_' . $option_name, $input_tag );
}

function sga_ranking_setting_period( $options = null )
{
    if ( ! $options ) {
        $options = get_option( SGA_RANKING_OPTION_NAME );
    }
    echo sga_ranking_setting( $options, 'period' );
}

function sga_ranking_setting_cache_expire( $options = null )
{
    if ( ! $options ) {
        $options = get_option( SGA_RANKING_OPTION_NAME );
    }
    echo sga_ranking_setting( $options, 'cache_expire', 10 );
}

function sga_ranking_setting_display_count( $options = null )
{
    if ( ! $options ) {
        $options = get_option( SGA_RANKING_OPTION_NAME );
    }
    echo sga_ranking_setting( $options, 'display_count' );
}

function sga_ranking_setting_debug_mode( $options = null )
{
    if ( ! $options ) {
        $options = get_option( SGA_RANKING_OPTION_NAME );
    }
    echo sga_ranking_setting( $options, 'debug_mode', 4, 'checkbox', 1 );
}

function sga_ranking_options_validate( $input )
{
    $newinput = array(
        'period'        => absint( $input['period'] ),
        'cache_expire'  => absint( $input['cache_expire'] ),
        'display_count' => absint( $input['display_count'] ),
        'debug_mode'    => ( isset( $input['debug_mode'] ) ) ? absint( $input['debug_mode'] ) : 0,
    );
    return apply_filters( 'sga_ranking_options_validate', $newinput, $input );
}

add_action( 'admin_notices', function ()
{
    $token = get_option('gapiwp_token');
    $debug_mode = apply_filters( 'sga_ranking_debug_mode', false );
    if ( $token == '' && ! $debug_mode ) {
        echo '<div class="error">';
        echo __( 'Simple GA Ranking is available OAuth2 authorization.', SGA_RANKING_DOMAIN ) . ' ';
        printf(
            __( 'Please set on <a href="%s" >setting panel</a>.', SGA_RANKING_DOMAIN ) . ' ',
            admin_url('/options-general.php?page=gapiwp-analytics')
        );
        echo __( 'ClientLogin is no longer available.', SGA_RANKING_DOMAIN ) . ' ';
        printf(
            __( 'Please see <a href="%s" >this link</a>', SGA_RANKING_DOMAIN ),
            'https://developers.google.com/identity/protocols/AuthForInstalledApps'
        );
        echo '</div>';
    }
});