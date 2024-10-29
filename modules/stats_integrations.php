<?php
	/*
	Plugin Name: Statistics Integrations
	Plugin URI:http://ajaxedwp.com
	Description: Adds support for various statistic plugins to allow inline loaded posts to count as new post views.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){

		}elseif(AWP::enabled('inlineposts')){
			add_filter('awp_inlineposts_finished',array('AWP_stats', 'doit'));
		}
Class AWP_stats{

	function doit(){
		// For compatibility with Statraq
			global $p;
			$p=$id;

		//For compatibility with WP-PostViews
		if(function_exists('process_postviews') && !is_page() && !is_single()) {
			AWP_stats::process_postviews();
		}
		//javascript:urchinTracker('http://anthologyoi.com/awp');
	}


//
// 	To integrate with the WP-PostViews plugin
//
	function process_postviews() {
	global $id;
		$post_views = get_post_custom($post_id);
		$post_views = intval($post_views['views'][0]);
		if(empty($_COOKIE[USER_COOKIE])) {
			if($post_views > 0) {
				update_post_meta($id, 'views', ($post_views+1));
			} else {
				add_post_meta($id, 'views', 1, true);
			}
		}
	}
}
?>