<?php
/*
Plugin Name: Simple GA Ranking
Author: Horike Takahiro
Plugin URI: http://simple-ga-ranking.org
Description: Ranking plugin using data from google analytics.
Version: 2.1.3
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
define( 'SGA_RANKING_PLUGIN_URL',  plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ) );
define( 'SGA_RANKING_PLUGIN_DIR',  WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) );
define( 'SGA_RANKING_OPTION_NAME', 'sga_ranking_options' );
load_plugin_textdomain( SGA_RANKING_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages' );

// Google Analytics API
include __DIR__.'/vendor/autoload.php';
\Hametuha\GapiWP\Loader::load();

// Functions
require_once( SGA_RANKING_PLUGIN_DIR . '/lib/functions.php' );

// Admin settings
require_once( SGA_RANKING_PLUGIN_DIR . '/admin/admin.php' );

// Regist Shortcode
require_once( SGA_RANKING_PLUGIN_DIR . '/lib/shortcode.php' );

// Regist Widget
require_once( SGA_RANKING_PLUGIN_DIR . '/lib/wp-widget.class.php' );

// Regist REST API
require_once( SGA_RANKING_PLUGIN_DIR . '/lib/wp-rest-api.class.php' );
