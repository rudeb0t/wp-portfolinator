<?php
$wp_install_dir = dirname(__FILE__);
$wp_include_dir = $wp_install_dir . '/wp-includes';
while (strlen($wp_install_dir)) {
	if (is_dir($wp_install_dir . '/wp-includes')) {
		$wp_include_dir = $wp_install_dir . '/wp-includes/';
		$wp_install_dir = '/';
		break;
	}
	$wp_install_dir = dirname($wp_install_dir);
}

require_once($wp_include_dir . 'plugin.php');
require_once($wp_include_dir . 'formatting.php');

list($w, $h) = explode('x', $_GET['tn']);

if ($w < 1) {
	$w = 150;
}
if ($h < 1) {
	$h = 150;
}
$wrap_class = sanitize_html_class($_GET['wrap_class']);
$item_class = sanitize_html_class($_GET['item_class']);
if (strlen($wrap_class) == 0) {
	$wrap_class = 'portfolinator_wrap';
}
if (strlen($item_class) == 0) {
	$item_class = 'portfolinator_item';
}

$subpage = intval($_GET['subpage']);
