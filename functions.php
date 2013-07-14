<?php
define('PORTFOLINATOR_URL_BASE', plugin_dir_url(__FILE__));

define('PORTFOLINATOR_ITEMS_PER_PAGE', 12);

define('PORTFOLINATOR_HTML_UL_LI', 1);
define('PORTFOLINATOR_HTML_DIV_P', 2);
define('PORTFOLINATOR_HTML_DIV_DIV', 3);

define('PORTFOLINATOR_WRAP_CLASS', 'portfolinator_wrap');
define('PORTFOLINATOR_ITEM_CLASS', 'portfolinator_item');

define('PORTFOLINATOR_USE_BUNDLED_COLORBOX', 1);

$GLOBALS['PORTFOLINATOR_PAGINATOR_DATA'] = null;


function portfolinator_get_image_caption($image) {
	if ($image->post_excerpt) {
		return apply_filters('the_title', $image->post_excerpt);
	} else {
		return '';
	}
}

function portfolinator_enqueue_scripts() {
	global $post;

	$options = portfolinator_options();

	if ($options['root_page'] == $post->ID || $options['root_page'] == $post->post_parent) {
		$tn = get_option('thumbnail_size_w') . 'x' . get_option('thumbnail_size_h');
		$args = array(
			'tn' => $tn,
			'wrap_class' => $options['wrap_class'],
			'item_class' => $options['item_class'],
			'subpage' => intval($options['root_page'] == $post->post_parent)
		);

        if ($options['use_bundled_colorbox']) {
            wp_register_style('colorbox-css', PORTFOLINATOR_URL_BASE . 'colorbox/colorbox.css', false, false, false);
            wp_register_style('portfolinator-css', add_query_arg($args, PORTFOLINATOR_URL_BASE . 'css/portfolinator.css.php'), array('colorbox-css'), false, false);

            wp_register_script('colorbox-js', PORTFOLINATOR_URL_BASE . 'colorbox/jquery.colorbox-min.js', array('jquery'), false, true);
            wp_register_script('portfolinator-js', add_query_arg($args, PORTFOLINATOR_URL_BASE . 'js/portfolinator.js.php'), array('colorbox-js'), false, true);

            wp_enqueue_style('portfolinator-css');
            wp_enqueue_script('portfolinator-js');
        }
	}
}

function portfolinator_options($use_default=false) {
	$default_options = array(
		'root_page' => 0,
		'gallery_position' => 'before',
		'items_per_page' => PORTFOLINATOR_ITEMS_PER_PAGE,
		'html' => PORTFOLINATOR_HTML_UL_LI,
		'wrap_class' => PORTFOLINATOR_WRAP_CLASS,
		'item_class' => PORTFOLINATOR_ITEM_CLASS,
        'use_bundled_colorbox' => PORTFOLINATOR_USE_BUNDLED_COLORBOX,
        'paginator_type' => 'list',
        'paginator_prev_next' => 0,
        'paginator_prev_text' => __('Previous'),
        'paginator_next_text' => __('Next')
	);
	if ($use_default) {
		return $default_options;
	} else {
		$options = get_option('portfolinator_options');
		return (is_array($options) ? $options : $default_options);
	}
}

function portfolinator_html($options) {
	switch ($options['html']) {
		case PORTFOLINATOR_HTML_UL_LI:
			return array(
				'wrap_' => sprintf('<ul class="%s">', $options['wrap_class']),
				'_wrap' => '</ul>',
				'item_' => sprintf('<li class="%s">', $options['item_class']),
				'_item' => '</li>'
			);
		case PORTFOLINATOR_HTML_DIV_P:
			return array(
				'wrap_' => sprintf('<div class="%s">', $options['wrap_class']),
				'_wrap' => '</div>',
				'item_' => sprintf('<p class="%s">', $options['item_class']),
				'_item' => '</p>'
			);
		case PORTFOLINATOR_HTML_DIV_DIV:
			return array(
				'wrap_' => sprintf('<div class="%s">', $options['wrap_class']),
				'_wrap' => '</div>',
				'item_' => sprintf('<div class="%s">', $options['item_class']),
				'_item' => '</div>'
			);
	}
}

function portfolinator_extract_gallery($item, $m, $options, $is_first=false) {
	$html = portfolinator_html($options);

	$tag = $m[2];
	$params = shortcode_parse_atts($m[3]);
	if ($tag == 'gallery' && $params['ids']) {
		$output = $html['item_'];

		$ids = explode(',', $params['ids']);
		if ($is_first) {
			$id = array_shift($ids);
			$image = get_post($id);
			$thumb_src = wp_get_attachment_image_src($image->ID, 'thumbnail');
			$full_src = wp_get_attachment_image_src($image->ID, 'large');
			$image_caption = portfolinator_get_image_caption($image);
			$output = $output . sprintf('<a href="%s" title="%s" class="portfolio-item" rel="portfolio-%s"><img src="%s" alt="%s"></a>', $full_src[0], $image_caption, $item->ID, $thumb_src[0], $image_caption);
		}

		foreach($ids as $id) {
			$image = get_post($id);
			$full_src = wp_get_attachment_image_src($image->ID, 'large');
			$image_caption = portfolinator_get_image_caption($image);
			$output = $output . sprintf('<a href="%s" title="%s" class="portfolio-item portfolio-item-hidden" rel="portfolio-%s"></a>', $full_src[0], $image_caption, $item->ID);
		}
		$output = $output . $html['_item'];
		return $output;
	} else {
		if ($is_first) {
			return $html['item_'] . sprintf('<img src="http://placehold.it/%sx%s" alt="No Gallery Available">', $options['tw'], $options['th']) . $html['_item'];
		}
	}
	return '';
}

function portfolinator_the_content($content) {
	global $post;

	$options = portfolinator_options();
	$options['tw'] = get_option('thumbnail_size_w');
	$options['th'] = get_option('thumbnail_size_h');

	if ($options['root_page'] == $post->ID) {
		$html = portfolinator_html($options);
        $paginator = '';
		$current_page = get_query_var('paged');
		if (!$current_page) {
			$current_page = 1;
		}

		$items = new WP_Query(array(
			'post_parent' => $options['root_page'],
			'post_type' => 'page',
			'post_status' => 'publish',
			'posts_per_page' => $options['items_per_page'],
			'paged' => $current_page
		));
		if ($items->max_num_pages > 1) {
			$format = get_option('permalink_structure') ? 'page/%#%/' : '&page=%#%';
            $paginator = paginate_links(array(
                'base' => get_pagenum_link(1) . '%_%',
                'format' => $format,
                'current' => $current_page,
                'total' => $items->max_num_pages,
                'mid_size' => 4,
                'type' => ($options['paginator_type'] == 'disabled' ? 'array' : $options['paginator_type']),
                'prev_next' => $options['paginator_prev_next'],
                'prev_text' => $options['paginator_prev_text'],
                'next_text' => $options['paginator_next_text']
            ));
            if ($options['paginator_type'] == 'disable') {
                $GLOBALS['PORTFOLINATOR_PAGINATOR_DATA'] = $paginator;
                $paginator = '';
            }
		}

		$output = $html['wrap_'];
		$shortcode_regex = sprintf('/%s/', get_shortcode_regex());
		while ($items->have_posts()) {
			$items->next_post();
			$item =& $items->post;
			$m = null;
			$item_content = $item->post_content;
			preg_match($shortcode_regex, $item_content, $m);
			$output = $output . portfolinator_extract_gallery($item, $m, $options, true);
			$item_content = preg_replace($shortcode_regex, '', $item_content, 1);
			while (preg_match($shortcode_regex, $item_content, $m)) {
				$output = $output . portfolinator_extract_gallery($item, $m, $options);
				$item_content = preg_replace($shortcode_regex, '', $item_content, 1);
			}
		}
		$output = $output . $html['_wrap'] . $paginator;
        if ($options['gallery_position'] == 'before') {
            return $output . $content;
        } else if ($options['gallery_position'] == 'after') {
            return $content . $output;
        } else {
            return $output;
        }
    } else {
        return $content;
    }
}
