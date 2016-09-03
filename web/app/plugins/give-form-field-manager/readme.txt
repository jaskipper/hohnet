=== Give - Form Field Manager ===
Contributors: wordimpress, dlocc, webdevmattcrom
Tags: donations, donation, ecommerce, e-commerce, fundraising, fundraiser, paymill, gateway
Requires at least: 4.0
Tested up to: 4.5.2
Stable tag: 1.1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==

Easily add and control Give's form fields using an easy-to-use interface

== Installation ==

= Minimum Requirements =

* WordPress 3.8 or greater
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
* Tweak: Moved the transaction's "Custom Form Fields" metabox above "Payment Notes" so it's more easily accessible to admins - https://github.com/WordImpress/Give-Form-Field-Manager/issues/40
* Fix: Compatibility issues with custom form fields and floating labels functionality https://github.com/WordImpress/Give-Form-Field-Manager/issues/66
* Fix: No form fields, set as empty meta so no blank fields leftover
* Fix: PHP7 produces fatal error with WP_DEBUG and SCRIPT_DEBUG set to true - https://github.com/WordImpress/Give-Form-Field-Manager/issues/67

= 1.1 =
* New: Added a new {all_custom_fields} email to to output all custom field data from a donation form submission
* Fix: When a user sets up a donation form with the "Reveal Upon Click" option and wants the Custom Form Fields to display in those hidden fields they were displaying rather than being hidden. https://github.com/WordImpress/Give-Form-Field-Manager/issues/59

= 1.0 =
* Initial plugin release. Yippee!