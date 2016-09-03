=== Give - Stripe Gateway ===
Contributors: wordimpress
Tags: donations, donation, ecommerce, e-commerce, fundraising, fundraiser, stripe, gateway
Requires at least: 4.0
Tested up to: 4.5
Stable tag: 1.3.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Stripe Gateway Add-on for Give

== Description ==

This plugin requires the Give plugin activated to function properly. When activated, it adds a payment gateway for stripe.com.

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

= 1.3.1 =
* Fix: Statement descriptor not properly being set for single time donations - https://github.com/WordImpress/Give-Stripe/issues/26

= 1.3 =
* New: Added the ability to disable "Billing Details" fieldset for Stripe to optimize donations forms with the least amount of fields possible - https://github.com/WordImpress/Give-Stripe/issues/11
* New: Stripe Preapproved Payments functionality - Admins are now notified when a new donation is made and it needs to be approved
* Fix: Payments fail if donation form has no title; now provides a fallback title "Untitle Donation Form" - https://github.com/WordImpress/Give-Stripe/issues/9
* Tweak: Register scripts prior to enqueuing
* Tweak: Removed "(MM/YY)" from the Expiration field label
* Tweak: Removed unused Recurring Donations functionality from Stripe Gateway Add-on in preparation for release of the actual Add-on

= 1.2 =
* Fix: Preapproved Stripe payments updated to properly show buttons within the Transactions' "Preapproval" column
* Fix: Increased statement_descriptor value limit from 15 to 22 characters

= 1.1 =
* New: Plugin activation banner with links to important links such as support, docs, and settings
* New: CC expiration field updated to be a singular field rather than two select fields
* Improved code organization and inline documentation
* Improved admin donation form validation
* Improved i18n (internationalization)
* Fix: Bug with Credit Cards with an expiration date more than 10 years
* Fix: Remove unsupported characters from statement_descriptor.
* Fix: Error refunding charges directly from within the transaction "Update Payment" modal

= 1.0 =
* Initial plugin release. Yippee!