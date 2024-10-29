<?php
/*
	Plugin Name: Shift Pages
	Plugin URI: http://ajaxedwp.com
	Description: Shifts pages so the first page is blank. (Most users will not want this. Use with caution.)
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

	if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
		add_action('awp_admin_post_options',array('AWP_shiftpages','admin'));
		add_filter('awp_get_options',array('AWP_shiftpages','awp_get_options'));
	}elseif(AWP::enabled('inlineposts') && $awpall['shiftpages'] == 1){
			if($awpall['simple_posts'] == 1){
				add_filter('awp_posts_after_pagination', array('AWP_shiftpages','filter'));
			}
	}

	register_activation_hook(__file__,array('AWP_shiftpages','set_defaults'));

Class AWP_shiftpages{

	function filter($output){
	global $id, $aWP,$pages;

		array_unshift($output,'');

	return $output;
	}

	function admin(){
	global $awpall, $aWP;

	ob_start();
?>
			<item type="checkbox" name="shiftpages">
				<d><?php _e('Move pages so the first page (the normal excerpt) is empty? ','awp');?></d>
			</item>
<?php
	$menu =	 ob_get_contents();
	ob_end_clean();

		do_action('awp_build_menu',$menu);
	}

	function awp_get_options($i){
		 $i[checkboxes][] = 'shiftpages';
		return $i;
	}
	function set_defaults($i){
	global $awpall;

		 $awpall[shiftpages] = 1;

		update_option('awp',$awpall);
	}
}

?>