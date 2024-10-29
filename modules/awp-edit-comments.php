<?php
	/*
	Plugin Name: WP AJAX Edit Comments Compatibility
	Plugin URI: http://ajaxedwp.com
	Description: Helps <a href="http://wordpress.org/extend/plugins/wp-ajax-edit-comments/">WP AJAX Edit Comments</a> to find comments loaded inline.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/
	if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
		add_action('awp_admin_other',array('AWP_aecomments','admin'));
		add_filter('awp_get_options',array('AWP_aecomments','awp_get_options'));
	}elseif($awpall['aecomments'] == 'Enabled'){
		add_filter('awp_ajax_comments_actions', array('AWP_aecomments','update_aecomments'));
		add_action('init', array('AWP_aecomments','init'));
	}

register_activation_hook(__file__,array('AWP_aecomments','set_defaults'));
class AWP_aecomments {

	function init(){
		if(class_exists('WPrapAjaxEditComments'))
			WPrapAjaxEditComments::JS();
	}

	function update_aecomments($actions){
	global $awpall;

		$actions[] = 'setTimeout("try{AjaxEditComments.init();}catch(e){}",1000);';

	return $actions;
	}

	function admin(){
	global $aWP, $awpall;

		ob_start();
?>
		<menu id="aecomments">
			<title><?php _e('WP AJAX Edit Comments Options.','awp');?></title>
			<name><?php _e('WP AJAX Edit Comments Support','awp');?></name>
			<desc><?php _e('This module just helps WP AJAX Edit Comments to find new inline loaded comments.','awp');?></desc>
		</menu>
<?php
		$menu =	 ob_get_contents();
		ob_end_clean();
		do_action('awp_build_menu',$menu);
	}

	function awp_get_options($i){
		$i[selects][] = 'aecomments';
		return $i;
	}

	function set_defaults(){
		global $awpall;
		$awpall[aecomments] = 'Enabled';
		update_option('awp',$awpall);
	}
}

?>
