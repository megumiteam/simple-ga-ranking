<?php
add_filter( 'widget_text', 'do_shortcode' );
add_shortcode( 'sga_ranking', 'sga_ranking_shortcode' );

/**
 * Name: SGARanking shortcode
 */
function sga_ranking_shortcode( $atts )
{
    $ids = sga_ranking_ids( $atts );
    $output = '';
    if ( ! empty( $ids ) ) {
        $cnt = 1;
        $output = '<ol class="sga-ranking">';
        foreach( $ids as $id ) {
            $post_permalink = get_permalink( $id );
            $post_title = get_the_title( $id );
            $output .= sprintf(
                '<li class="sga-ranking-list sga-ranking-list-%d">%s<a href="%s" title="%s">%s</a>%s</li>',
                $cnt,
                apply_filters( 'sga_ranking_before_title', '', $id, $cnt ),
                esc_attr( $post_permalink ),
                esc_attr( $post_title ),
                $post_title,
                apply_filters( 'sga_ranking_after_title', '', $id, $cnt )
            );
            $cnt++;
        }
        $output .= '</ol>';
    }

    return apply_filters( 'sga_ranking_shortcode', $output, $ids );
}
