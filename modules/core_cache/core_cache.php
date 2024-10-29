<?php
	/*
	Plugin Name: Javascript and CSS Core Cache
	Plugin URI: http://ajaxedwp.com
	Description: File-based cache for the core CSS and JS files so they are not recreated every time a user visits the website.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/


$awp_init[] = 'AWP_corecache';
register_activation_hook(__file__,array('AWP_corecache','set_defaults'));
class AWP_corecache {

	function init(){
		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
			add_action('awp_admin_other',array(&$this,'admin'));
			add_filter('awp_get_options',array(&$this,'awp_get_options'));
			add_action('awp_admin_update',array(&$this,'recacheJS'));
			add_action('awp_admin_update',array(&$this,'recacheCSS'));
		}elseif(AWP::enabled('corecache')){
			add_filter('awp_jscore', array(&$this,'switchjs'));
			add_filter('awp_csscore', array(&$this,'switchcss'));
		}
	}
	function switchjs($file){
		$home = get_settings('siteurl');

		$tfile  = ABSPATH . PLUGINDIR . AWP_MODULES.'/core_cache/core.cache.js';

		if(@file_exists($tfile) && is_readable($tfile) && filesize($tfile) > 10)
			$file = WP_CONTENT_URL.'/plugins'.AWP_MODULES.'/core_cache/core.cache.js.php';

	return $file;
	}

	function switchcss($file){
		$home = get_settings('siteurl');

		$tfile  = ABSPATH . PLUGINDIR . AWP_MODULES.'/core_cache/core.cache.css';
		if(@file_exists($tfile) && is_readable($tfile) && filesize($tfile) > 10)
			$file = WP_CONTENT_URL.'/plugins'.AWP_MODULES.'/core_cache/core.cache.css.php';

	return $file;
	}
	function recache(){
		$this->recacheJS();
		$this->recacheCSS();
	}

	function my_file_get_contents($file){
		if (!$handle = fopen($file,'r')) {
		return '';
		}

		$contents = fread($handle, filesize($file));
		fclose($handle);

		return $contents;
	}


	function recacheJS(){
	global $awpall,$aWP;

		$file  = ABSPATH . PLUGINDIR . AWP_MODULES.'/core_cache/core.cache.js';

		if (!$handle = fopen($file,'w')) {
			return false;
		}

		$contents = $this->my_file_get_contents(ABSPATH.PLUGINDIR.'/'.AWP_BASE.'/js/core.js.php');

		if (fwrite($handle, $contents) === FALSE) {
			return false;
		}

		fclose($handle);

		return true;

	}

	function recacheCSS(){
	global $awpall,$aWP;

		$file  = ABSPATH . PLUGINDIR . AWP_MODULES.'/core_cache/core.cache.css';

		if (!$handle = fopen($file,'w')) {
			return false;
		}

		$contents = file_get_contents(WP_CONTENT_URL.'/plugins/'. AWP_BASE. '/js/core.css.php');


		if (fwrite($handle, $contents) === FALSE) {
			return false;
		}

		fclose($handle);

		return true;
	}

	function admin(){
	global $aWP, $awpall;

		ob_start();
?>
		<menu id="corecache">
			<title><?php _e('Core Cache Options.','awp');?></title>
			<name><?php _e('Core Cache','awp');?></name>
			<desc><?php _e('This module updates a static file when options are saved rather than reloading WP to generate the core JS and CSS files.','awp');?></desc>
		</menu>
<?php
		$menu =	 ob_get_contents();
		ob_end_clean();
		do_action('awp_build_menu',$menu);
	}

	function awp_get_options($i){
		$i[selects][] = 'corecache';
		return $i;
	}

	function set_defaults(){
		global $awpall,$AWP_corecache;
		$awpall[corecache] = 'Enabled';
		$AWP_corecache = new AWP_corecache();
		$AWP_corecache->recache();
		update_option('awp',$awpall);
	}
}

?>
