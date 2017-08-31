=== Merchant Center product feed importer ===
Contributors: lsoltys
Tags: XML, Product feed, Google Merchant Center, Google Shopping, e-commerce, RSS
Requires at least: 4.1
Tested up to: 4.8.1
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Fetch and display products from your e-commerce Google Merchant Center product XML feed on your wordpress site.

== Description ==
Fetch and display information about products from Google Merchant Center product XML (Google Shopping) feed on your wordpress site. All you need is valid feed URL and plugin will generate shortcodes to display product cards on your site.

Feed structure and specification are explained in [Google Merchant Center Help](https://support.google.com/merchants/answer/7052112?visit_id=1-636317402488791740-723275688&hl=en&rd=1).

== Frequently Asked Questions ==

= Does it supports other product feed types? =

No. This is plugin is only Google Shopping feed specific.

= How can I change look od product cards? =

Currently you can change price background color of all cards, by using color picker in "Box Color" option on settings page.

= Is there any way to add UTM parameters to product url? =

Yes. On settings page you can provide UTM source, medium and campagin parameters. Term and content parameters are set automatically and are based on product variables.

= What is cache livespan option? =

Your feed is copied locally to your plugin directory. With "cache lifespan" you can set interval for refreshing local file. Time is in minutes. Usually few hours should be okay.

= Why changing feed url will break shortcodes? =

Plugin uses <g:id> parameter from feed to generate product specific shortcode. Changing feed url will also change database for products IDs therefore break shortcodes.

== Screenshots ==
1. png1.png

== Changelog ==

= 0.1.0 =
*Initial release
