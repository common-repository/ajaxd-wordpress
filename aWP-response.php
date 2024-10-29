<?php


	ob_start();
		define('AWP_AJAXED', true);
		define('AWP_ID', $id);

      $root = dirname(dirname(dirname(dirname(__FILE__))));
      if (file_exists($root.'/wp-load.php')) {
          // WP 2.6
          require_once($root.'/wp-load.php');
      } else {
          // Before 2.6
          require_once($root.'/wp-config.php');
      }


	ob_end_clean(); //Ensure we don't have output from other plugins.
	header('Content-Type: text/html; charset='.get_option('blog_charset'));

	if($_POST['comment_post_ID']){
		$id = intval($_POST['comment_post_ID']);
	}elseif($_POST['id']){
		$id = intval($_POST['id']);
	}

	if((is_int($id) || $id === false || $id == 0) && $_REQUEST['type']){
		if($id){
			$post = get_post($id);
			//This doesn't get setup automatically.
			$authordata = get_userdata($post->post_author);
			if (!empty($post->post_password) && $_COOKIE['wp-postpass_'. COOKIEHASH] != $post->post_password) {
				die();
			}
		}

		do_action('awp_ajax_type_'.addslashes($_REQUEST['type']));

	}else{
			_e('Invalid ID.','awp');
	}
 ?>




