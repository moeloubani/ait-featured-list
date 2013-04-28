<?php
/**
 * AIT WordPress Theme
 *
 * Copyright (c) 2012, Affinity Information Technology, s.r.o. (http://ait-themes.com)
 * Modified by: Moe Loubani (moe@loubani.com)
 */
$latteParams['type'] = (isset($_GET['dir-search'])) ? true : false;
if($latteParams['type']){
  $latteParams['isDirSearch'] = true;
	// show all items on map
	if(isset($aitThemeOptions->search->searchShowMap)){
		$radius = array();
		if(isset($_GET['geo'])){
			$radius[] = $_GET['geo-radius'];
			$radius[] = $_GET['geo-lat'];
			$radius[] = $_GET['geo-lng'];
		}
		$latteParams['items'] = getItems(intval($_GET['categories']),intval($_GET['locations']),$GLOBALS['wp_query']->query_vars['s'],$radius);
	}
	global $wp_query;
	$posts = $wp_query->posts;
	foreach ($posts as $item) {
		$item->link = get_permalink($item->ID);
		$image = wp_get_attachment_image_src( get_post_thumbnail_id($item->ID) );
		$ml_featured = get_post_meta($item->ID, 'moe_featured_checkbox', true);
		if ($ml_featured == 'on') {
			$item->packageClass = $item->packageClass . 'ml_featured_class';
		}
		if($image !== false){
			$item->thumbnailDir = $image[0];
		} else {
			$item->thumbnailDir = $aitThemeOptions->directory->defaultItemImage;
		}
		$item->excerptDir = aitGetPostExcerpt($item->post_excerpt,$item->post_content);
	}

} else {
	$posts = WpLatte::createPostEntity($wp_query->posts);
}
$latteParams['posts'] = $posts;

WPLatte::createTemplate(basename(__FILE__, '.php'), $latteParams)->render();
