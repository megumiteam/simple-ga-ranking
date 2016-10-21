=== Simple GA Ranking  ===
Contributors: horike,amimotoami
Tags:  form, ranking, popular, google analytics
Requires at least: 3.6.1
Tested up to: 4.6.1
Stable tag: 2.0.9

Ranking plugin using data from google analytics.

== Description ==

Ranking plugin using data from google analytics.
The feature to work very lightweight, because it is not save ranking data in WordPress DB.

= How to use =
Please show [the official site](http://simple-ga-ranking.org/).

= Translators =
* English(en) - [megumithemes](http://profiles.wordpress.org/megumithemes/)
* Japanese(ja) - [Horike Takahiro](http://profiles.wordpress.org/horike)
* Thai(th_TH) - [TG Knowledge](http://www.xn--12cg1cxchd0a2gzc1c5d5a.com)

You can send your own language pack to me.

Please contact to me.

* @[horike37](http://twitter.com/horike37) on twitter
* [Horike Takahiro](https://www.facebook.com/horike.takahiro) on facebook

= Contributors =
* [Horike Takahiro](http://profiles.wordpress.org/horike)
* [webnist](https://profiles.wordpress.org/webnist)
* [TG Knowledge](http://www.xn--12cg1cxchd0a2gzc1c5d5a.com)

== Installation ==

1. Upload `simple-ga-ranking` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

== Changelog ==
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
= 1.3.1 =
* some fix
= 2.0 =
* OAuth2 authorization available. ClientLogin is no longer available.
= 2.0.1 =
* Add some hook.
= 2.0.2 =
* some fix.
= 2.0.3 =
* Add some hook.
= 2.0.4 =
* Bug fix.
= 2.0.5 =
* Add official site link in Plugin header.
= 2.0.7 =
* Update the limit of number to get from Google Analytics from 30 to 100.
= 2.0.8 =
* Fixed some error handling
= 2.0.9 =
* fixed error that didn't dispaly your ranking on v2.0.8
