# Simple GA Ranking 
Contributors: horike,megumithemes
Tags:  form, ranking, popular, google analytics
Requires at least: 3.6.1
Tested up to: 4.1
Stable tag: 1.3

Ranking plugin using data from google analytics.

# Description

Ranking plugin using data from google analytics.

= How use =
1. Set your Google analytics account and so on at the Simple GA Ranking's option page (Settings->Simple GA Ranking).

2. Put the short code at where you'd like to show the ranking.

3. You can use many kind of filters by post-type or taxonomy. For example, you can show only posts and pages by `[sga_ranking post_type="post,page"]`. Also, showing posts in only WordPress category by `[sga_ranking post_type="post" category__in="wordpress"]`.

4. The short code supports parameters as follows: `post_type`, `exclude_post_type`, `'taxonomy_slug'__in`, `'taxonomy_slug'__not_in`, filter. All parameters except “filter” need to be set the slug(s).

- post_type:  set the attribute of the slug of the post-type you’d like to show. You can set multiple sulgs separeted by comma.

- exclude_post_type: set the attribute of the post-type you’d NOT like to show. You can set multiple sulgs separeted by comma.

- 'taxonomy_slug'__in: set the attribute of the taxonomy you’d like to show. You can set multiple terms separeted by comma.

- 'taxonomy_slug'__not_in: set the attribute of the taxonomy you’d NOT like to show. You can set multiple terms separeted by comma.

- filter:  You can use the filter parameter as same as the fileter parameter of Google Analytics API.
[https://developers.google.com/analytics/devguides/reporting/core/v3/reference](https://developers.google.com/analytics/devguides/reporting/core/v3/reference)

For example, suppose you have a multi-languages site with direcotry based multisite, and take the statistic of all sites of the multisite having with the URL structure as follows by one GA account.
http://example.com/ja
http://example.com/en
http://example.com/cn

When you’d like to take the statistic of each site, revise like below.
[sga_ranking filter="pagePath=~^/ja/"]
[sga_ranking filter="pagePath=~^/en/"]
[sga_ranking filter="pagePath=~^/cn/"]

Please try ohter parameters using the below sites as a reference.
[https://support.google.com/analytics/answer/1034324?hl=en](https://support.google.com/analytics/answer/1034324?hl=en)
[https://developers.google.com/analytics/devguides/reporting/core/v3/reference?hl=en](https://developers.google.com/analytics/devguides/reporting/core/v3/reference?hl=en)

You can use JSON REST API Endpoint. Require plugin [JSON REST API](https://wordpress.org/plugins/json-rest-api/)
`http://example.com/wp-json/ranking`


# Translators
* English(en) - [megumithemes](http://profiles.wordpress.org/megumithemes/)
* Japanese(ja) - [Horike Takahiro](http://profiles.wordpress.org/horike)
* Thai(th_TH) - [TG Knowledge](http://www.xn--12cg1cxchd0a2gzc1c5d5a.com)

You can send your own language pack to me.

Please contact to me.

* @[horike37](http://twitter.com/horike37) on twitter
* [Horike Takahiro](https://www.facebook.com/horike.takahiro) on facebook

# Contributors
* [Horike Takahiro](http://profiles.wordpress.org/horike)
* [webnist](https://profiles.wordpress.org/webnist)

# Installation

1. Upload `simple-ga-ranking` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

# Changelog
= 1.0 =
* first release. 
= 1.1 =
* Apply widget.
= 1.2.2 =
* Metric change visitors to pageviews.
* fixed a bug that custom post type.
= 1.2.3 =
* Add filter parameter.
= 1.2.4 =
* Add test mode.`define('SGA_RANKING_TEST_MODE', true);` on wp-config.php.
= 1.2.5 =
* fixed a bug that ver 1.2.4
= 1.2.6 =
* test mode can set on option page.
= 1.2.9 =
* Update readme.txt.
= 1.2.12 =
* add filter `sga_widget_shortcode_argument`.
= 1.2.14 =
* Thai support
= 1.2.15 =
* some fix
= 1.2.16 =
* some fix
= 1.3 =
* add JSON REST API Endpoint
