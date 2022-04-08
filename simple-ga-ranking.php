<?php
/*
Plugin Name: Simple GA Ranking
Author: Digitalcube
Plugin URI: http://simple-ga-ranking.org
Description: Ranking plugin using data from google analytics.
Version: 3.0.0
Author URI: http://simple-ga-ranking.org
Domain Path: /languages
Text Domain:

Copyright 2018 - 2021 digitalcube (email : info@digitalcube.jp)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'SGA_RANKING_DOMAIN',      'sga-ranking' );
define( 'SGA_RANKING_OPTION_NAME', 'sga_ranking_options' );
load_plugin_textdomain( SGA_RANKING_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

// regist default settings
define( 'SGA_RANKING_DEFAULT', array(
    'period'        => 30,        
    'cache_expire'  => 24 * HOUR_IN_SECONDS,
    'display_count' => 10,
    'debug_mode'    => 0,
));
$defaults = array(
    'period'        => function( $default = null ){ return $default ? $default : SGA_RANKING_DEFAULT['period']; },
    'cache_expire'  => function( $default = null ){ return $default ? $default : SGA_RANKING_DEFAULT['cache_expire']; },
    'display_count' => function( $default = null ){ return $default ? $default : SGA_RANKING_DEFAULT['display_count']; },
    'debug_mode'    => function( $default = null ){ return $default ? $default : SGA_RANKING_DEFAULT['debug_mode']; },
);
foreach ( $defaults as $field_name => $callback ) {
    add_filter( 'sga_ranking_default_' . $field_name, $callback );
}

// Google Analytics API
include __DIR__ . '/vendor/autoload.php';
\Hametuha\GapiWP\Loader::load();

// Functions
include __DIR__ . '/lib/functions.php';

// Admin settings
include __DIR__ . '/admin/admin.php';

// Regist Shortcode
include __DIR__ . '/lib/shortcode.php';

// Regist Widget
if ( class_exists( 'WP_Widget' ) ) {
    include __DIR__ . '/lib/wp-widget.class.php';
}

// Regist REST API
if ( class_exists( 'WP_JSON_Posts' ) ) {
    include __DIR__ . '/lib/wp-rest-api.class.php';
}
