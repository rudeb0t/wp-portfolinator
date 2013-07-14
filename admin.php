<?php
add_action('admin_init', 'portfolinator_admin_init');
add_action('admin_menu', 'portfolinator_admin_menu');

function portfolinator_admin_init() {
	register_setting('portfolinator_options', 'portfolinator_options', 'portfolinator_validator');
	add_settings_section('portfolinator_main', __('Portfolio Display'), 'portfolinator_main_section', 'portfolinator');
	add_settings_field('portfolinator_root_page', __('Root Page'), 'portfolinator_root_page_field', 'portfolinator', 'portfolinator_main');
	add_settings_field('portfolinator_gallery_position', __('Gallery Position'), 'portfolinator_gallery_position_field', 'portfolinator', 'portfolinator_main');
	add_settings_field('portfolinator_items_per_page', __('Items Per Page'), 'portfolinator_items_per_page_field', 'portfolinator', 'portfolinator_main');

	add_settings_section('portfolinator_html', __('HTML Options'), 'portfolinator_html_section', 'portfolinator');
	add_settings_field('portfolinator_html', __('Wrapper and Item HTML Tags'), 'portfolinator_html_field', 'portfolinator', 'portfolinator_html');
	add_settings_field('portfolinator_wrap_class', __('Wrapper CSS Class'), 'portfolinator_wrap_class_field', 'portfolinator', 'portfolinator_html');
	add_settings_field('portfolinator_item_class', __('Item CSS Class'), 'portfolinator_item_class_field', 'portfolinator', 'portfolinator_html');
    add_settings_field('portfolinator_use_bundled_colorbox', __('Use bundled colorbox'), 'portfolinator_colorbox_field', 'portfolinator', 'portfolinator_html');
}

function portfolinator_admin_menu() {
	add_submenu_page('options-general.php', 'Portfolinator', 'Portfolinator Options', 'manage_options', 'portfolinator-options-page', 'portfolinator_options_page');
}

function portfolinator_validator($input) {
	$options = portfolinator_options(true);

	$root_page = intval($input['root_page']);
	$page = get_page($root_page);
	if ($page) {
		$options['root_page'] = $root_page;
	} else if ($root_page) {
		add_settings_error('portfolinator_root_page', 'portfolinator_root_page_error', __('Select a valid root page.'), 'error');
	}

	$gallery_position = trim($input['gallery_position']);
	if (in_array($gallery_position, array('before', 'after', 'disable'))) {
		$options['gallery_position'] = $gallery_position;
	} else {
		add_settings_error('portfolinator_gallery_position', 'portfolinator_gallery_position_error', __('Select a valid gallery position.'), 'error');
	}

	$items_per_page = intval($input['items_per_page']);
	if ($items_per_page < 0) {
		$items_per_page = 0;
	}
	$options['items_per_page'] = $items_per_page;

	$html = intval($input['html']);
	if (in_array($html, array(PORTFOLINATOR_HTML_UL_LI, PORTFOLINATOR_HTML_DIV_P, PORTFOLINATOR_HTML_DIV_DIV))) {
		$options['html'] = $html;
	} else {
		add_settings_error('portfolinator_html', 'portfolinator_html_error', __('Select a valid HTML combination.'), 'error');
	}

	$wrap_class = sanitize_html_class(trim($input['wrap_class']));
	if ($wrap_class == trim($input['wrap_class'])) {
		$options['wrap_class'] = $wrap_class;
	} else {
		add_settings_error('portfolinator_wrap_class', 'portfolinator_wrap_class_error', __('Select a valid CSS class for the wrapper HTML element.'), 'error');
	}

	$item_class = sanitize_html_class(trim($input['item_class']));
	if ($item_class == trim($input['item_class'])) {
		$options['item_class'] = $item_class;
	} else {
		add_settings_error('portfolinator_item_class', 'portfolinator_item_class_error', __('Select a valid CSS class for the item HTML element.'), 'error');
	}

    $use_bundled_colorbox = 
    $options['use_bundled_colorbox'] = intval($input['use_bundled_colorbox']) ? 1 : 0;

	return $options;
}

function portfolinator_root_page_field() {
	$options = portfolinator_options();
	$root_page = $options['root_page'];
	echo '<select id="portfolinator_root_page" name="portfolinator_options[root_page]">';
	echo '<option value="">'.__('Select Page').'</option>';
	foreach(get_pages() as $page) {
		$item = sprintf('<option value="%d"', $page->ID);
		if ($page->ID == $root_page) {
			$item .= ' selected="selected"';
		}
		$item .= '>' . $page->post_title . '</option>';
		echo $item;
	}
	echo '</select>';
	echo '<p class="description">';
	_e('Root page for your Portfolio entries. Sub-pages under this page will be scanned for [gallery] shortcode contents.');
	echo '</p>';
}

function portfolinator_gallery_position_field() {
	$options = portfolinator_options();
	$gallery_position = ($options['gallery_position'] ? $options['gallery_position'] : 'before');
	echo '<label><input type="radio" id="portfolinator_gallery_before" value="before" name="portfolinator_options[gallery_position]"' . ($gallery_position == 'before' ? ' checked="checked"' : '').'> ' . __('Before content') . '</label>';
	echo '&nbsp;&nbsp;';
	echo '<label><input type="radio" id="portfolinator_gallery_after" value="after" name="portfolinator_options[gallery_position]"' . ($gallery_position == 'after' ? ' checked="checked"' : '').'> ' . __('After content') . '</label>';
	echo '&nbsp;&nbsp;';
	echo '<label><input type="radio" id="portfolinator_gallery_disable" value="disable" name="portfolinator_options[gallery_position]"' . ($gallery_position == 'disable' ? ' checked="checked"' : '').'> ' . __('Disable content') . '</label>';
	echo '<p class="description">';
	_e('Choose where the gallery thumbnail index appears in the root page. Or prevent it from appearing.');
	echo '</p>';
}

function portfolinator_items_per_page_field() {
	$options = portfolinator_options();
	$items_per_page = ($options['items_per_page'] ? $options['items_per_page'] : PORTFOLINATOR_ITEMS_PER_PAGE);
	echo '<input type="number" step="1" min="0" class="small-text" value="'.$items_per_page.'" name="portfolinator_options[items_per_page]">';
	echo '<p class="description">';
	_e('Number of items to display per page. Set to 0 to display all items.');
	echo '</p>';
}

function portfolinator_html_field() {
	$options = portfolinator_options();
	$html = ($options['html'] ? $options['html'] : PORTFOLINATOR_HTML_UL_LI);
	echo '<select id="portfolinator_html" name="portfolinator_options[html]">';
	echo '<option value="' . PORTFOLINATOR_HTML_UL_LI . '"' . ($html == PORTFOLINATOR_HTML_UL_LI ? ' selected="selected"' : '') . '>' . htmlentities2('<ul><li><a><img></a></li></ul>') . '</option>';
	echo '<option value="' . PORTFOLINATOR_HTML_DIV_P . '"' . ($html == PORTFOLINATOR_HTML_DIV_P ? ' selected="selected"' : '') . '>' . htmlentities2('<div><p><a><img></a></p></div>') . '</option>';
	echo '<option value="' . PORTFOLINATOR_HTML_DIV_DIV . '"' . ($html == PORTFOLINATOR_HTML_DIV_DIV ? ' selected="selected"' : '') . '>' . htmlentities2('<div><div><a><img></a></div></div>') . '</option>';
	echo '</select>';
}

function portfolinator_wrap_class_field() {
	$options = portfolinator_options();
	$wrap_class = ($options['wrap_class'] ? $options['wrap_class'] : PORTFOLINATOR_WRAP_CLASS);
	echo '<input type="text" name="portfolinator_options[wrap_class]" value="' . $wrap_class . '">';
	echo '<p class="description">';
	_e('CSS class for the wrapper element.');
	echo '</p>';
}

function portfolinator_item_class_field() {
	$options = portfolinator_options();
	$item_class = ($options['item_class'] ? $options['item_class'] : PORTFOLINATOR_ITEM_CLASS);
	echo '<input type="text" name="portfolinator_options[item_class]" value="' . $item_class . '">';
	echo '<p class="description">';
	_e('CSS class for the item element.');
	echo '</p>';
}

function portfolinator_colorbox_field() {
	$options = portfolinator_options();
	$colorbox = intval($options['use_bundled_colorbox']) ? 1 : 0;
	echo '<label><input type="radio" id="portfolinator_bundled_colorbox" value="1" name="portfolinator_options[use_bundled_colorbox]"' . ($colorbox == 1 ? ' checked="checked"' : '').'> ' . __('Yes') . '</label>';
	echo '&nbsp;&nbsp;';
	echo '<label><input type="radio" id="portfolinator_no_bundled_colorbox" value="0" name="portfolinator_options[use_bundled_colorbox]"' . ($colorbox == 0 ? ' checked="checked"' : '').'> ' . __('No (theme must use colorbox)') . '</label>';
	echo '<p class="description">';
	_e('Choose whether or not to use the colorbox bundled with this project.');
	echo '</p>';
}

function portfolinator_options_page() {
?>
<div class="wrap">
<?php screen_icon(); ?>
<h2><?php _e('Portfolinator Options'); ?></h2>
<form method="post" action="options.php">
<?php
settings_fields('portfolinator_options');
//do_settings_fields('portfolinator', 'portfolinator_main');
do_settings_sections('portfolinator');
submit_button();
?>
</form>
</div>
<?php
}

function portfolinator_main_section() {
	echo '<p>';
	_e('These settings control where and how the portfolio thumbnail index will be displayed.');
	echo '</p>';
}

function portfolinator_html_section() {
	echo '<p>';
	_e('These settings control the HTML that will be used in generating the gallery index. Make sure that you use valid and matching tags for the opening and closing HTML. If you leave these blank the defaults will be used.');
	echo '</p>';
}
