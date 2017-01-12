<?php
/*
Plugin Name:  Allow Widget Shortcodes
Plugin URI:   https://skipperinnovations.com
Description:  Allow Widget Shortcodes in my blog
Version:      1.0.0
Author:       Jason Skipper
Author URI:   https://skipperinnovations.com
License:      MIT License
*/

add_filter('widget_text','do_shortcode');
?>
