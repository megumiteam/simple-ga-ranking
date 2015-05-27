<?php
/*
Plugin Name: Simple GA Ranking
Author: Horike Takahiro
Plugin URI: https://github.com/horike37/simple-ga-ranking
Description: Ranking plugin using data from google analytics.
Version: 2.0
Author URI: https://github.com/horike37/simple-ga-ranking
Domain Path: /languages
Text Domain:

Copyright 2013 horike takahiro (email : horike37@gmail.com)

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

if ( ! defined( 'SGA_RANKING_DOMAIN' ) )
	define( 'SGA_RANKING_DOMAIN', 'sga-ranking' );

if ( ! defined( 'SGA_RANKING_PLUGIN_URL' ) )
	define( 'SGA_RANKING_PLUGIN_URL', plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ));

if ( ! defined( 'SGA_RANKING_PLUGIN_DIR' ) )
	define( 'SGA_RANKING_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ));

load_plugin_textdomain( SGA_RANKING_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages' );

require_once( SGA_RANKING_PLUGIN_DIR . '/admin/admin.php' );

include __DIR__.'/vendor/autoload.php';
\Hametuha\GapiWP\Loader::load();
$simple_ga_ranking = \Hametuha\GapiWP\Loader::analytics();

function sga_ranking_get_date( $args = array() ) {
	global $simple_ga_ranking;

	$options = get_option( 'sga_ranking_options' );
	if ( defined( 'SGA_RANKING_TEST_MODE' ) && SGA_RANKING_TEST_MODE === true || isset($options['debug_mode']) && $options['debug_mode'] == 1 ) {
		global $wpdb;
		$r = wp_parse_args( $args );
		if ( !isset($r['display_count']) )
			$r['display_count'] = 10;

		$rets = $wpdb->get_results( 'SELECT ID FROM '. $wpdb->posts. ' WHERE post_type="post" AND post_status="publish" ORDER BY RAND() LIMIT 0, '. $r['display_count'] );
		$ids = array();
		foreach ( $rets as $ret ) {
			$ids[] = $ret->ID;
		}

		return $ids;
	}

    $r = wp_parse_args( $args );

    if ( isset($r['period']) )
    	$options['period'] = $r['period'];

    if ( isset($r['display_count']) )
    	$options['display_count'] = $r['display_count'];

    if ( empty( $options['display_count'] ) )
    	$options['display_count'] = apply_filters( 'sga_ranking_default_display_count', 10 );

    if ( empty( $options['period'] ) )
    	$options['period'] = apply_filters( 'sga_ranking_default_period', 30 );

    $options['end_date'] = date_i18n( 'Y-m-d' );
    $options['start_date']   = date_i18n( 'Y-m-d', strtotime( $options['end_date'] . '-' . $options['period'] . 'day' ) );

    $transient_key = 'sga_ranking_' . $options['period'] . '_' . $options['display_count'];
    if ( !empty($r) ) {
    	if ( array_key_exists( 'post_type', $r ) )
    		$transient_key .= '_post_type_' . $r['post_type'];

    	if ( array_key_exists( 'exclude_post_type', $r ) )
    		$transient_key .= '_exclude_post_type_' . $r['exclude_post_type'];

    	foreach ( $r as $k => $v ) {
    		if ( strpos( $k, '__in' ) !== false )
    			$transient_key .= '_' . $k . '_' . $r[$k];

    		if ( strpos( $k, '__not_in' ) !== false )
    			$transient_key .= '_' . $k . '_' . $r[$k];
    	}
    }
    $filter_val = isset($r['filter']) ? $r['filter'] : '' ;
    $transient_key .= '_' . $filter_val;
    $transient_key = md5($transient_key);
    $transient_key = substr( $transient_key, 0, 30 );

    if ($id = get_transient($transient_key)) {
    	return $id;
    } else {
    
    	
    	$args = array(
    			'start-index' => 1,
				'max-results' => apply_filters( 'sga_ranking_limit_filter', 30 ),
				'dimensions'  => 'ga:pagePath',
				'sort' => '-ga:pageviews',
    	);
    	if ( isset($filter_val) && $filter_val !== '' ) {
    		$args['filters'] = $filter_val;
    	}
    	$results = $simple_ga_ranking->fetch($options['start_date'],$options['end_date'], 'ga:pageviews', $args );

    	$cnt = 0;
    	$post_ids = array();
    	if ( !is_wp_error( $results ) ) {
	    	foreach($results->rows as $result) {
	    		$max = (int)$options['display_count'];
	    		if ( $cnt >= $max )
	    			break;
	
	    		if ( strpos($result[0], 'preview=true') !== false )
	    			continue;
	
	    		$post_id = sga_url_to_postid(esc_url($result[0]));
	
	    		if ( $post_id == 0 )
	    			$post_id = url_to_postid(esc_url($result[0]));
	
	    		if ( $post_id == 0 )
	    			continue;
	
	    		if ( in_array( $post_id, $post_ids ) )
	    			continue;
	
	    		$post_obj = get_post($post_id);
	    		if ( !is_object($post_obj) || $post_obj->post_status != 'publish' )
	    			continue;
	
	    		if ( !empty($r) ) {
	    			if ( array_key_exists( 'post_type', $r ) ) {
	    				$post_type = explode(',', $r['post_type'] );
	    				if ( !in_array( get_post($post_id)->post_type, $post_type ) )
	    					continue;
	    			}
	
	    			if ( array_key_exists( 'exclude_post_type', $r ) ) {
	    				$exclude_post_type = explode(',', $r['exclude_post_type'] );
	    				if ( in_array( get_post($post_id)->post_type, $exclude_post_type ) )
	    					continue;
	    			}
	
	    			$tax_in_flg = true;
	    			foreach ( $r as $key => $val ) {
	    				if ( strpos( $key, '__in' ) !== false ) {
	    					$tax = str_replace( '__in', '', $key );
	    					$tax_in = explode(',', $r[$key] );
	    					$post_terms = get_the_terms( $post_id, $tax );
	    					$tax_in_flg = false;
	    					if ( !empty($post_terms) && is_array($post_terms) ) {
	    						foreach ( $post_terms as $post_term ) {
	    							if ( in_array( $post_term->slug, $tax_in ) )
	    								$tax_in_flg = true;
	    						}
	    					}
	    					break;
	    				}
	    			}
	    			if ( !$tax_in_flg )
	    				continue;
	
	    			$tax_not_in_flg = true;
	    			foreach ( $r as $key => $val ) {
	    				if ( strpos( $key, '__not_in' ) !== false ) {
	    					$tax = str_replace( '__not_in', '', $key );
	    					$tax_in = explode(',', $r[$key] );
	    					$post_terms = get_the_terms( $post_id, $tax );
	    					$tax_not_in_flg = false;
	    					if ( !empty($post_terms) && is_array($post_terms) ) {
	    						foreach ( $post_terms as $post_term ) {
	    							if ( !in_array( $post_term->slug, $tax_in ) )
	    								$tax_not_in_flg = true;
	    						}
	    					}
	    					break;
	    				}
	    			}
	    			if ( !$tax_not_in_flg )
	    				continue;
	    		}
	
	    		$post_ids[] = $post_id;
	    		$cnt++;
	    	}
	    } else {
	    	if ( is_super_admin() ) {
	    		echo '<pre>';
	    		var_dump($results);
	    		echo '</pre>';
	    	}
	    }
	    if ( !empty($post_ids) ) {
	    	delete_transient($transient_key);
	    	set_transient(
	    		$transient_key,
	    		$post_ids,
	   			intval(apply_filters('sga_ranking_cache_expire', 24*60*60))
	    	);
	   		return $post_ids;
	   	}
	}
}

add_filter( 'widget_text', 'do_shortcode' );
add_shortcode('sga_ranking', 'sga_ranking_shortcode');
function sga_ranking_shortcode( $atts ) {

	$ids = sga_ranking_get_date($atts);

	if ( empty( $ids ) )
		return;

	$cnt = 1;
    $output = '<ol class="sga-ranking">';
    foreach( $ids as $id ) {
    	$output .= '<li class="sga-ranking-list sga-ranking-list-'.$cnt.'">' . apply_filters( 'sga_ranking_before_title', '', $id, $cnt ) . '<a href="'.get_permalink($id).'" title="'.get_the_title($id).'">'.get_the_title($id).'</a>' . apply_filters( 'sga_ranking_after_title', '', $id, $cnt ) . '</li>';
    	$cnt++;
    }
    $output .= '</ol>';

    return $output;

}


//widget
class WP_Widget_Simple_GA_Ranking extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_simple_ga_ranking', 'description' => __( "Show ranking the data from Google Analytics", SGA_RANKING_DOMAIN ) );
		parent::__construct('simple_ga_rankig', __('Simple GA Ranking'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		echo sga_ranking_shortcode( apply_filters( 'sga_widget_shortcode_argument', array() ) );

		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = $instance['title'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

}
add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Simple_GA_Ranking");'));

function sga_url_to_postid($url)
{
	global $wp_rewrite;

	$url = apply_filters('url_to_postid', $url);

	// First, check to see if there is a 'p=N' or 'page_id=N' to match against
	if ( preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values) )	{
		$id = absint($values[2]);
		if ( $id )
			return $id;
	}

	// Check to see if we are using rewrite rules
	$rewrite = $wp_rewrite->wp_rewrite_rules();

	// Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
	if ( empty($rewrite) )
		return 0;

	// Get rid of the #anchor
	$url_split = explode('#', $url);
	$url = $url_split[0];

	// Get rid of URL ?query=string
	$url_split = explode('?', $url);
	$url = $url_split[0];

	// Add 'www.' if it is absent and should be there
	if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
		$url = str_replace('://', '://www.', $url);

	// Strip 'www.' if it is present and shouldn't be
	if ( false === strpos(home_url(), '://www.') )
		$url = str_replace('://www.', '://', $url);

	// Strip 'index.php/' if we're not using path info permalinks
	if ( !$wp_rewrite->using_index_permalinks() )
		$url = str_replace('index.php/', '', $url);

	if ( false !== strpos($url, home_url()) ) {
		// Chop off http://domain.com
		$url = str_replace(home_url(), '', $url);
	} else {
		// Chop off /path/to/blog
		$home_path = parse_url(home_url());
		$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
		$url = str_replace($home_path, '', $url);
	}

	// Trim leading and lagging slashes
	$url = trim($url, '/');

	$request = $url;
	// Look for matches.
	$request_match = $request;
	foreach ( (array)$rewrite as $match => $query) {
		// If the requesting file is the anchor of the match, prepend it
		// to the path info.
		if ( !empty($url) && ($url != $request) && (strpos($match, $url) === 0) )
			$request_match = $url . '/' . $request;

		if ( preg_match("!^$match!", $request_match, $matches) ) {
			// Got a match.
			// Trim the query of everything up to the '?'.
			$query = preg_replace("!^.+\?!", '', $query);

			// Substitute the substring matches into the query.
			$query = addslashes(WP_MatchesMapRegex::apply($query, $matches));

			// Filter out non-public query vars
			global $wp;
			parse_str($query, $query_vars);
			$query = array();
			foreach ( (array) $query_vars as $key => $value ) {
				if ( in_array($key, $wp->public_query_vars) )
					$query[$key] = $value;
			}

		// Taken from class-wp.php
		foreach ( $GLOBALS['wp_post_types'] as $post_type => $t )
			if ( $t->query_var )
				$post_type_query_vars[$t->query_var] = $post_type;

		foreach ( $wp->public_query_vars as $wpvar ) {
			if ( isset( $wp->extra_query_vars[$wpvar] ) )
				$query[$wpvar] = $wp->extra_query_vars[$wpvar];
			elseif ( isset( $_POST[$wpvar] ) )
				$query[$wpvar] = $_POST[$wpvar];
			elseif ( isset( $_GET[$wpvar] ) )
				$query[$wpvar] = $_GET[$wpvar];
			elseif ( isset( $query_vars[$wpvar] ) )
				$query[$wpvar] = $query_vars[$wpvar];

			if ( !empty( $query[$wpvar] ) ) {
				if ( ! is_array( $query[$wpvar] ) ) {
					$query[$wpvar] = (string) $query[$wpvar];
				} else {
					foreach ( $query[$wpvar] as $vkey => $v ) {
						if ( !is_object( $v ) ) {
							$query[$wpvar][$vkey] = (string) $v;
						}
					}
				}

				if ( isset($post_type_query_vars[$wpvar] ) ) {
					$query['post_type'] = $post_type_query_vars[$wpvar];
					$query['name'] = $query[$wpvar];
				}
			}
		}

			// Do the query
			$query = new WP_Query($query);
			if ( !empty($query->posts) && $query->is_singular )
				return $query->post->ID;
			else
				return 0;
		}
	}
	return 0;
}

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'json-rest-api/plugin.php' ) && ( '3.9.2' <= get_bloginfo( 'version' ) && '4.2' > get_bloginfo( 'version' ) ) ) {

	require_once( SGA_RANKING_PLUGIN_DIR . '/lib/wp-rest-api.class.php' );

	function sga_json_api_ranking_filters( $server ) {
		// Ranking
		$wp_json_ranking = new WP_JSON_SGRanking( $server );
		add_filter( 'json_endpoints', array( $wp_json_ranking, 'register_routes'    ), 1     );
	}
	add_action( 'wp_json_server_before_serve', 'sga_json_api_ranking_filters', 10, 1 );
}
