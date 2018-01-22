=== Merchant Center product feed importer ===
Contributors: lsoltys
Tags: XML, Product feed, Google Merchant Center, Google Shopping, e-commerce, RSS
Requires at least: 4.1
Tested up to: 4.8.1
Stable tag: 1.15
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Fetch and display products from your e-commerce Google Merchant Center product XML feed on your wordpress site.

== Description ==
Fetch and display information about products from Google Merchant Center product XML (Google Shopping) feed on your wordpress site. All you need is valid feed URL and plugin will generate shortcodes to display product cards on your site.

Feed structure and specification are explained in [Google Merchant Center Help](https://support.google.com/merchants/answer/7052112?visit_id=1-636317402488791740-723275688&hl=en&rd=1).

== Frequently Asked Questions ==

= Does it supports other product feed types? =

No. This is plugin is only Google Shopping feed specific.

= How can I change look of product cards? =

Currently you can change price background color of all cards, by using color picker in "Price background color" option on settings page. You can control the size of the cards in two ways. First by setting height of the product image and second, by changing witdh of cards. Both values are in pixels.

= Is there any way to add UTM parameters to product url? =

Yes. On settings page you can provide UTM source, medium and campagin parameters. Term and content parameters are set automatically and are based on product variables.

= What is cache livespan option? =

Your feed is copied locally to your plugin directory. With "cache lifespan" you can set interval for refreshing local file. Time is in minutes. Usually few hours should be okay.

= Why changing feed url will break shortcodes? =

Plugin uses <g:id> parameter from feed to generate product specific shortcode. Changing feed url will also change database for products IDs therefore break shortcodes.

== Screenshots ==
1. mcpfi1.png

== Changelog ==

= 0.1.0 =
   *Initial release

= 0.1.1 =
   * Added: option for changing image height
   * Added: better product id handling
   * Fixed: removed global styles in favor to inline
   * Fixed: some CSS rendering issues
   * Fixed: better html markup for product boxes

= 0.1.15 =
   * Added: setting for card width
   * Added: additional field validation
   * Added: additional WP related, security check
   * Added: option to disable UTM
   * Fixed: input validation sanitization for backend settings
   * Fixed: UTM url sanitization
   * Fixed: escaping data from xml
   * Fixed: minor layout fixes for settings page
