<?php
/**
 * Name: SGARanking Get Data
 */
function sga_ranking_get_date( $args = array() )
{
    return sga_ranking_ids( $args );
}
function sga_ranking_ids( $args = array() )
{
    $post_ids = array();
    $options  = get_option( SGA_RANKING_OPTION_NAME );

    // get args
    $r = wp_parse_args( $args );
    if ( isset($r['period']) ) {
        $options['period'] = $r['period'];
    }
    if ( empty( $options['cache_expire'] ) ) {
        $options['cache_expire'] = apply_filters( 'sga_ranking_default_cache_expire', 24 * HOUR_IN_SECONDS );
    }
    if ( isset($r['display_count']) ) {
        $options['display_count'] = $r['display_count'];
    }
    if ( empty( $options['display_count'] ) ) {
        $options['display_count'] = apply_filters( 'sga_ranking_default_display_count', 10 );
    }
    if ( empty( $options['period'] ) ) {
        $options['period'] = apply_filters( 'sga_ranking_default_period', 30 );
    }
    $force_update  = isset($r['force_update']) ? $r['force_update'] : false;
    $filter_val    = isset($r['filter']) ? $r['filter'] : '';
    $display_count = (int) $options['display_count'];

    // cache expire time
    $cache_expires = (int) apply_filters( 'sga_ranking_cache_expire', $options['cache_expire'] );

    // post limit
    $post_limit    = (int) apply_filters( 'sga_ranking_limit_filter', 100 );

    // get start date - end date
    $date_format   = 'Y-m-d';
    $end_date      = function_exists( 'wp_date' ) ? wp_date( $date_format ) : date_i18n( $date_format );
    $start_date    = strtotime( $end_date . '-' . $options['period'] . 'day' );

    $options['start_date'] =
        function_exists( 'wp_date' )
        ? wp_date( $date_format, $start_date )
        : date_i18n( $date_format, $start_date );
    $options['end_date']   = $end_date;

    // build transient key
    $transient_key = sprintf( 'sga_ranking_%d_%d', $options['period'], $display_count );
    if ( !empty($r) ) {
        if ( array_key_exists( 'post_type', $r ) ) {
            $transient_key .= sprintf( '_post_type_%s', $r['post_type'] );
        }
        if ( array_key_exists( 'exclude_post_type', $r ) ) {
            $transient_key .= sprintf( '_exclude_post_type_%s', $r['exclude_post_type'] );
        }

        foreach ( $r as $k => $v ) {
            if ( strpos( $k, '__in' ) !== false ) {
                $transient_key .= sprintf( '_%s_%s', $k , $r[$k] );
            }
            if ( strpos( $k, '__not_in' ) !== false ) {
                $transient_key .= sprintf( '_%s_%s', $k , $r[$k] );
            }
        }
    }
    $transient_key .= sprintf( '_%s', $filter_val );
    $transient_key = substr( md5( $transient_key ), 0, 30 );

    // Exclusive processing
    $processing = $force_update ? false : get_transient( "sga_ranking_{$transient_key}" );
    $ids = false !== $processing ? get_transient( $transient_key ) : false;
    if ( false === $processing || false === $ids ) {
        $date_format = 'Y-m-d H:i:s';
        set_transient(
            "sga_ranking_{$transient_key}",
            [
                'key'     => $transient_key,
                'options' => $options,
                'args'    => $r,
                'limit'   => $post_limit,
                'date'    => function_exists( 'wp_date' ) ? wp_date( $date_format ) : date_i18n( $date_format ),
                'expires' => $cache_expires,
            ],
            $cache_expires
        );
    }

    // Debug Mode
    $debug_mode = apply_filters( 'sga_ranking_debug_mode', false );
    if ( false === $ids && $debug_mode ) {
        $ids = apply_filters( 'sga_ranking_dummy_data', array(), $args, $options );
        set_transient( $transient_key, $ids, $cache_expires * 2 );
    }

    // get GA ranking Data
    if ( false !== $ids ) {
        // from cache
        $post_ids = $ids;

    } else {
        // from Google Analytics API
        $simple_ga_ranking = \Hametuha\GapiWP\Loader::analytics();
        $ga_args = array(
                'start-index' => 1,
                'max-results' => $post_limit,
                'dimensions'  => 'ga:pagePath',
                'sort'        => '-ga:pageviews',
        );
        if ( ! empty( $filter_val ) ) {
            $ga_args['filters'] = $filter_val;
        }
        $results = $simple_ga_ranking->fetch(
            $options['start_date'],
            $options['end_date'],
            'ga:pageviews',
            $ga_args
        );

        if ( ! empty( $results ) && !is_wp_error( $results ) && is_array( $results->rows ) ) {
            $post_ids = array();
            $cnt = 0;
            foreach($results->rows as $result) {
                if ( $cnt >= $display_count ) {
                    break;
                }

                // Get Post ID from URL
                $url = isset( $result[0] ) ? $result[0] : '';
                $post_id = sga_ranking_url_to_postid( esc_url( $url ) );
                if ( in_array( $post_id, $post_ids ) ) {
                    continue;
                }

                $exclude = apply_filters( 'sga_ranking_exclude_post', false, $post_id, $url );
                if ( $exclude ) {
                    continue;
                }

                $post_obj = get_post( $post_id );
                if ( !is_object($post_obj) || $post_obj->post_status != 'publish' ) {
                    continue;
                }

                if ( !empty($r) ) {
                    if ( array_key_exists( 'post_type', $r ) && is_string($r['post_type']) ) {
                        $post_type = explode(',', $r['post_type'] );
                        if ( empty($post_type) || !in_array( $post_obj->post_type, $post_type ) ){
                            continue;
                        }
                    }
    
                    if ( array_key_exists( 'exclude_post_type', $r ) ) {
                        $exclude_post_type = explode(',', $r['exclude_post_type'] );
                        if ( !empty($exclude_post_type) && in_array( $post_obj->post_type, $exclude_post_type ) ) {
                            continue;
                        }
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
                                    if ( in_array( $post_term->slug, $tax_in ) ) {
                                        $tax_in_flg = true;
                                    }
                                }
                            }
                            break;
                        }
                    }
                    if ( !$tax_in_flg ) {
                        continue;
                    }
    
                    $tax_not_in_flg = true;
                    foreach ( $r as $key => $val ) {
                        if ( strpos( $key, '__not_in' ) !== false ) {
                            $tax = str_replace( '__not_in', '', $key );
                            $tax_in = explode(',', $r[$key] );
                            $post_terms = get_the_terms( $post_id, $tax );
                            $tax_not_in_flg = false;
                            if ( !empty($post_terms) && is_array($post_terms) ) {
                                foreach ( $post_terms as $post_term ) {
                                    if ( !in_array( $post_term->slug, $tax_in ) ) {
                                        $tax_not_in_flg = true;
                                    }
                                }
                            }
                            break;
                        }
                    }
                    if ( !$tax_not_in_flg ) {
                        continue;
                    }
                }

                $post_ids[] = $post_id;
                $cnt++;
            }
            set_transient( $transient_key, $post_ids, $cache_expires * 2 );

        } else {
            $post_ids = apply_filters( 'sga_ranking_dummy_data_for_error', array(), $options);
            if ( is_super_admin() ) {
                echo '<pre>';
                var_dump( $results );
                echo '</pre>';
            }
        }
    }

    return apply_filters( 'sga_ranking_ids', $post_ids, $args, $options );
}

/**
 * Name: SGARanking Exclude post
 */
add_filter( 'sga_ranking_exclude_post', function ( $exclude = false, $post_id = 0, $url = '' )
{
    if ( false !== strpos( $url, 'preview=true' ) ) {
        $exclude = true;
    }
    if ( 0 === (int) $post_id ) {
        $exclude = true;
    }
    return $exclude;
}, 1, 3 );

/**
 * Name: SGARanking Debug Mode
 */
add_filter( 'sga_ranking_debug_mode', function ( $debug_mode = false )
{
    $options = get_option( SGA_RANKING_OPTION_NAME );
    if ( defined( 'SGA_RANKING_TEST_MODE' ) && true === SGA_RANKING_TEST_MODE ) {
        $debug_mode = true;
    }
    if ( isset($options['debug_mode']) && 1 === (int) $options['debug_mode'] ) {
        $debug_mode = true;
    }
    return $debug_mode;
}, 1 );

/**
 * Name: SGARanking Get Dummy Data
 */
add_filter( 'sga_ranking_dummy_data', 'sga_ranking_dummy_data', 1, 3 );
function sga_ranking_dummy_data ( $ids, $args = array(), $options = array() )
{
    global $wpdb;

    $display_count = apply_filters( 'sga_ranking_default_display_count', 10 );
    if ( isset( $options['display_count'] ) ) {
        $display_count = (int) $options['display_count'];
    }

    $query = $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s ORDER BY RAND() LIMIT 0, %d",
        'post',
        'publish',
        $display_count * 10
    );
    $rets = $wpdb->get_results( $query );

    $post_ids = array();
    $cnt = 0;
    foreach ( $rets as $ret ) {
        if ( $cnt >= $display_count ) {
            break;
        }
        $exclude = apply_filters( 'sga_ranking_exclude_post', false, $ret->ID, '' );
        if ( ! $exclude ) {
            $post_ids[] = $ret->ID;
            $cnt++;
        }
    }

    return $post_ids;
}

/**
 * Name: SGARanking URL to Post ID
 */
function sga_url_to_postid( $url )
{
    return sga_ranking_url_to_postid( $url );
}
function sga_ranking_url_to_postid( $url )
{
    $post_id = (int) apply_filters( 'sga_ranking_url_to_postid', $post_id, $url );
    if ( 0 === $post_id ) {
        $post_id = (int) url_to_postid( esc_url( $url ) );
    }
    return $post_id;
}
add_filter( 'sga_ranking_url_to_postid', function ( $id, $url )
{
    global $wp_rewrite;

    $url = apply_filters( 'url_to_postid', $url );
    if ( empty( $url ) ) {
        return 0;
    }

    // First, check to see if there is a 'p=N' or 'page_id=N' to match against
    if ( preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values) )    {
        $id = absint($values[2]);
        if ( $id ) {
            return $id;
        }
    }

    // Check to see if we are using rewrite rules
    $rewrite = $wp_rewrite->wp_rewrite_rules();

    // Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
    if ( empty($rewrite) ) {
        return 0;
    }

    // Get rid of the #anchor
    $url_split = explode('#', $url);
    $url = $url_split[0];

    // Get rid of URL ?query=string
    $url_split = explode('?', $url);
    $url = $url_split[0];

    // Add 'www.' if it is absent and should be there
    if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') ) {
        $url = str_replace('://', '://www.', $url);
    }

    // Strip 'www.' if it is present and shouldn't be
    if ( false === strpos(home_url(), '://www.') ) {
        $url = str_replace('://www.', '://', $url);
    }

    // Strip 'index.php/' if we're not using path info permalinks
    if ( !$wp_rewrite->using_index_permalinks() ) {
        $url = str_replace('index.php/', '', $url);
    }

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
        if ( !empty($url) && ($url != $request) && (strpos($match, $url) === 0) ) {
            $request_match = $url . '/' . $request;
        }

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
            foreach ( $GLOBALS['wp_post_types'] as $post_type => $t ) {
                if ( $t->query_var ) {
                    $post_type_query_vars[$t->query_var] = $post_type;
                }
            }

            foreach ( $wp->public_query_vars as $wpvar ) {
                if ( isset( $wp->extra_query_vars[$wpvar] ) ) {
                    $query[$wpvar] = $wp->extra_query_vars[$wpvar];
                } elseif ( isset( $_POST[$wpvar] ) ) {
                    $query[$wpvar] = $_POST[$wpvar];
                } elseif ( isset( $_GET[$wpvar] ) ) {
                    $query[$wpvar] = $_GET[$wpvar];
                } elseif ( isset( $query_vars[$wpvar] ) ) {
                    $query[$wpvar] = $query_vars[$wpvar];
                }

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
            if ( !empty($query->posts) && $query->is_singular ) {
                return $query->post->ID;
            } else {
                return 0;
            }
        }
    }
    return 0;
}, 1, 2 );