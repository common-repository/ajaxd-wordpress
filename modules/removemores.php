<?php
/*
	Plugin Name: Remove Mores
	Plugin URI: http://ajaxedwp.com
	Description: If you used more tags before you installed AWP then this module will strip them out.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

	if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
		add_action('awp_admin_post_options',array('AWP_removemores','admin'));
		add_filter('awp_get_options',array('AWP_removemores','awp_get_options'));
	}elseif($awpall['remove_mores'] == 1 && $awpall['split_mode'] != 'more'){
		add_filter('awp_posts_before_pagination', array('AWP_removemores','stripmores'));
	}

Class AWP_removemores{

	function stripmores($content){
	global $id, $aWP,$post;

		$old = get_option('awp_firstinstall');
		if($old && (mysql2date('U', $post->post_date_gmt) < mysql2date('U', $old))){
			$content = str_replace('<!--more-->','',$content);
		}

	return $content;
	}

	function admin(){
	global $awpall, $aWP;

	ob_start();
?>
			<item type="checkbox" name="remove_mores">
				<d><?php _e('Remove more tags from posts from before AWP was activated?','awp');?></d>
   		 		<desc><?php _e('If you used more tags before you installed AWP then you may remove them with this module. Posts after the first install date will not have more tags removed.','awp');?></desc>
			</item>
<?php
	$menu =	 ob_get_contents();
	ob_end_clean();

		do_action('awp_build_menu',$menu);
	}

	function awp_get_options($i){
		 $i[checkboxes][] = 'protecttags';
		 $i[checkboxes][] = 'remove_mores';
		return $i;
	}

}

?>