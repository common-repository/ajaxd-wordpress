<?php
	/*
	Plugin Name:
	Plugin URI: http://anthologyoi.com/awp/
	Description: This module will cache the major functions in AWP to speed up the processing of each page.  This is especially useful if you have inline comments with threading or inline posts split by word count. (Inspired by and based upon <a href="http://rmarsh.com/plugins/poc-cache/">POC Cache</a>)
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

$awp_init[] = "AWP_Cache";
register_activation_hook(__file__,array(&$this,'set_defaults'));
class AWP_Cache {

	function init(){
	global $awpall, $aWP,$AWP_inlineposts;
		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
		add_action('awp_admin_other',array(&$this,'admin'));
		add_filter('awp_get_options',array(&$this,'awp_get_options'));
		add_action('awp_admin_update',array(&$this,'delete'));
		}elseif($awpall['cache'] == 'Enabled'){
			if(AWP::enabled('inlineposts')){

				remove_action('awp_paginate', array($AWP_inlineposts, 'paginate'));
				add_action('awp_paginate', array($AWP_inlineposts, 'post_paginate'));
			}

			if(AWP::enabled('inlinecomments')){

				$aWP['basic_comments'] = 1;
				remove_action('awp_comments_do', array('AWP_inlinecomments','comments'));
				add_action('awp_comments_do', array(&$this,'comments_comments'));

			}

			add_action('publish_post', array(&$this,'remove_post_cache'), 1);
			add_action('edit_post', array(&$this,'remove_post_cache'), 1);
			add_action('delete_post', array(&$this,'remove_post_cache'), 1);
			add_action('publish_phone', array(&$this,'remove_post_cache'), 1);
			add_action('delete_comment', array(&$this,'delete_comment_cache'), 1);
			// these we check for validity before invalidating the cache
			add_action('trackback_post', array(&$this,'remove_comment_cache'), 1);
			add_action('pingback_post', array(&$this,'remove_comment_cache'), 1);
			add_action('comment_post', array(&$this,'remove_comment_cache'), 1);
			add_action('edit_comment', array(&$this,'remove_comment_cache'), 1);
			add_action('wp_set_comment_status', array(&$this,'remove_comment_cache'), 1);


			do_action('awp_cache_init');
		}


	}

	function comments_comments(){
	global $id, $AWP_inlinecomments;
		$cache = $this->fetch('comments_'.$id);
		if(!$cache){
			ob_start();
			$AWP_inlinecomments->comments();
			$cache = ob_get_contents();
			ob_end_clean();
			$this->store('comments_'.$id, $cache);
		}

		echo $cache;
	}

	function post_paginate(){
	global $id,$pages,$AWP_inlineposts;
		$cache = $this->fetch('post_'.$id);
		if(!$cache){
			$cache = $AWP_inlineposts->paginate();
			$this->store('post_'.$id, $cache);
		}
		$pages = $cache;
	}

	function post_pages(){
	global $id,$AWP_inlineposts;
		$cache = $this->fetch('post_pages_'.$id);
		if(!$cache){
			$cache = $AWP_inlineposts->pages();
			$this->store('post_pages_'.$id, $cache);
		}
	return $cache;
	}

	function clear_cache($id) {
		$this->remove_cache('post_'.$id);
		$this->remove_cache('post_'.$id);
		$this->remove_cache('comments_'.$id);
	}

	function delete_comment_cache($comment_id) {

		$comment = get_commentdata($comment_id, 1, true);
		$this->remove_cache('comments_'.$comment['comment_post_ID']);
	}

	function remove_post_cache($id) {
		$this->remove_cache('post_'.$id);
		$this->remove_cache('post_pages_'.$id);
	}

	function remove_comment_cache($comment_id) {
	global $id;
		$comment = get_commentdata($comment_id, 1, true);
		if( strpos($_SERVER['REQUEST_URI'], 'wp-admin/') == false && $comment['comment_approved'] != 1 ){
			return;
		}else{
			$this->remove_cache('comments_'.$comment['comment_post_ID']);
		}
	}

	function remove_cache($cache_id) {
		global $awp_cached;
		if ($awp_cached[$cache_id]){
			return;
		}else{

			delete_option('AWP_CACHE_'.$cache_id);
		}
	}

	function fetch($cache_id) {
		$result = get_option('AWP_CACHE_'.$cache_id);

		if($result){
			return $result;
		}else{
			return false;
		}
	}

	function store($cache_id, $data) {
		update_option('AWP_CACHE_'.$cache_id, $data);
	}

	function delete($cache_id) {
	global $wpdb;
		$wpdb->query("DELETE FROM `" . $wpdb->options . "` WHERE `option_name` LIKE 'AWP_CACHE_%'");
	}

	function admin(){
	global $aWP, $awpall;


	ob_start();
?>
		<menu id="cache">
			<title><?php _e('Cache Options.','awp');?></title>
			<name><?php _e('Cache','awp');?></name>
			<desc><?php _e('This module will cache the paginated post (just the paged content that AWP creates not the modifications other plugins do to it), the text of the post show/hide links and the entire comment list with formatting. The module automatically removes the old cached items when they are out of date and re-caches them the next time they are needed. This will speed up processing time exponentially.','awp');?></desc>
		</menu>
<?php


	$menu =	 ob_get_contents();
	ob_end_clean();


	do_action('awp_build_menu',$menu);

	}

	function awp_get_options($i){
		$i[selects][] = 'cache';
		return $i;
	}

	function set_defaults(){
		global $awpall;
		$awpall[cache] = 'Enabled';
		update_option('awp',$awpall);
	}
}

?>