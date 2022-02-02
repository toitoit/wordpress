<?php
// Enqueue jQuery and Dequeue Gutenberg Styles

function enqueueDequeue() {
	wp_enqueue_script("jquery");
	wp_dequeue_style("global-styles");
	wp_dequeue_style("wp-block-library");
	wp_dequeue_style("wp-block-library-theme");
}

add_action("wp_enqueue_scripts", "enqueueDequeue", 100);

// Theme support

function themeSupport() {
	add_theme_support("title-tag");
	add_theme_support("menus");
	add_theme_support("post-thumbnails");

	add_image_size("landscape", 500, 350, true);
	add_image_size("portrait", 500, 700, true);
	add_image_size("square", 500, 500, true);

	add_filter("media_library_infinite_scrolling", "__return_true");
}

add_action("after_setup_theme", "themeSupport");

// Disable Category re-ordering on Edit Post page

function taxonomyChecklist($args) {
	$args['checked_ontop'] = false;

	return $args;
}

add_filter("wp_terms_checklist_args", "taxonomyChecklist");

// Remove Comments

function removeMenus() {
	remove_menu_page("edit-comments.php");
}

add_action("admin_menu", "removeMenus");

function removeToolbarItems($wp_adminbar) {
	$wp_adminbar->remove_node("comments");
}

add_action("admin_bar_menu", "removeToolbarItems", 999);

// Add Excerpt to Pages

function addExcerptToPages() {
	add_post_type_support("page", "excerpt");
}

add_action("init", "addExcerptToPages");

// Add Tags to Pages and Attachments

function addTags() {
	register_taxonomy_for_object_type("post_tag", "page");
	register_taxonomy_for_object_type("post_tag", "attachment");
}

add_action("init", "addTags");

// Deactivate Image Sizes

function deactivateImageSizes($sizes) {
	$targets = ['medium_large', '1536x1536', '2048x2048'];

	foreach ($sizes as $size_index => $size) {
		if (in_array($size, $targets)) {
			unset($sizes[$size_index]);
		}
	}

	return $sizes;
}

add_action("intermediate_image_sizes", "deactivateImageSizes", 10, 1);

// Disable lazy loading

add_filter("wp_lazy_loading_enabled", "__return_false");

// Customise WYSIWYG Editor

function addStyleSelectButtons($buttons) {
	array_unshift($buttons, "styleselect");

	return $buttons;
}

add_filter("mce_buttons_2", "addStyleSelectButtons");

// Add custom styles to the TinyMCE/WYSIWYG Editor

function myCustomStyles($init_array) {
	$style_formats = array(
		array(
			"title" => "Leading",
			"block" => "p",
			"classes" => "leading",
			"wrapper" => false
		),
		array(
			"title" => "Important",
			"inline" => "span",
			"classes" => "important"
		),
		array(
			"title" => "Alert",
			"block" => "div",
			"classes" => "alert",
			"wrapper" => true
		)
	);

	$init_array['style_formats'] = json_encode($style_formats);

	return $init_array;
}

add_filter("tiny_mce_before_init", "myCustomStyles");

// Set TinyMCE/WYSIWYG Editor styles

function customStylesheet($stylesheets) {
	$stylesheets = explode(",", $stylesheets);

	$stylesheets[] = get_template_directory_uri()."/css/editor.css?".filemtime(get_template_directory()."/css/editor.css");

	$stylesheets = implode(",", $stylesheets);

	return $stylesheets;
}

add_filter("mce_css", "customStylesheet");

// Add Custom Admin Stylsheet

function customAdminStylesheet() {
	wp_enqueue_style("admin-custom", get_template_directory_uri()."/css/admin.css?".filemtime(get_template_directory()."/css/admin.css"));
}

add_action("admin_enqueue_scripts", "customAdminStylesheet");

// Remove DNS Prefetch

function removeDnsPrefetch() {
	remove_action("wp_head", "wp_resource_hints", 2, 99);
}

add_action("init", "removeDnsPrefetch");

// Exclude categories from Yoast SEO Sitemap
// https://github.com/Yoast/wordpress-seo/issues/387

function excludeCategoriesFromSitemap($excluded_posts_ids) {
	$args = array(
		"fields" => "ids",
		"post_type" => "post",
		"category__in" => array(1, 2, 3),
		"posts_per_page" => -1
	);

	return array_merge($excluded_posts_ids, get_posts($args));
}

add_filter("wpseo_exclude_from_sitemap_by_post_ids", "excludeCategoriesFromSitemap");
?>