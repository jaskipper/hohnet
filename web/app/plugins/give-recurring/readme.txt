=== Give - Recurring Donations ===
Contributors: wordimpress
Tags: donations, donation, ecommerce, e-commerce, fundraising, fundraiser, paymill, gateway
Requires at least: 4.0
Tested up to: 4.5
Stable tag: 1.1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Create powerful subscription based donations with the Give Recurring Donation Add-on.

== Description ==

This plugin requires the Give plugin activated to function properly. When activated, it adds the ability to accept recurring (subscription) donations to various payment gateways such as PayPal Standard, Stripe, PayPal Pro, and more.

== Installation ==

= Minimum Requirements =

* WordPress 4.0 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.1.1 =
* Fix: PHP fatal error for some hosting configurations "Can't use function return value in write context" - https://github.com/WordImpress/Give-Recurring-Donations/issues/192
* New: New link to plugin settings page with new base name constant - https://github.com/WordImpress/Give-Recurring-Donations/issues/190

= 1.1 =
* New: Don't require a login or registration for subscription donations when email access is enabled - https://github.com/WordImpress/Give-Recurring-Donations/issues/169
* New: Show a login form for [give_subscriptions] shortcode for non-logged-in users - https://github.com/WordImpress/Give-Recurring-Donations/issues/163
* New: Donation form option for admins to set whether subscription checkbox is checked or unchecked by default - https://github.com/WordImpress/Give-Recurring-Donations/issues/162
* Tweak: Provide Statement Descriptor when Creating Stripe Plans - https://github.com/WordImpress/Give-Recurring-Donations/issues/164
* Tweak: Don't register post status within Recurring; it's already in Core - https://github.com/WordImpress/Give-Recurring-Donations/issues/174
* UX: Added scrolling capability to subscription parent payments' metabox because it was getting too long for ongoing subscriptions - https://github.com/WordImpress/Give-Recurring-Donations/issues/130
* Fix: PHP Fatal error when Stripe event is not returned - https://github.com/WordImpress/Give-Recurring-Donations/issues/176
* Fix: PayPal Pro Gateway message issue "Something has gone wrong, please try again" response  - https://github.com/WordImpress/Give-Recurring-Donations/issues/177
* Fix: Blank notice appears when updating / saving settings in Give - https://github.com/WordImpress/Give-Recurring-Donations/issues/171

= 1.0.1 =
* Fix: Security fix added to prevent non-subscribers from seeing others subscriptions within the [give_subscriptions] shortcode

= 1.0 =
* Initial plugin release. Yippee!

