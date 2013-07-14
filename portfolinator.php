<?php
/*
Plugin Name: Portfolinator
Plugin URI: http://www.explodinator.com/wordpress/portfolinator/
Description: Meet the portfolinator. The plugin that takes the built-in Wordpress <code>[gallery]</code> shortcode and makes it awesome. Sick and tired of all those bloated gallery plugins? Portfolinator is for you. It's simple, light-weight and easy to customize without overloading you with a shitload of options.
Version: 1.0.0
Author: Nimrod A. Abing
Author URI: http://www.explodinator.com/
License: GPL v3 or later
*/
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php');

if (is_admin()) {
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'admin.php');
} else {
    add_action('wp_enqueue_scripts', 'portfolinator_enqueue_scripts');
    add_filter('the_content', 'portfolinator_the_content', 20);
}
