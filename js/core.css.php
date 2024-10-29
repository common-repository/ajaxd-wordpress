<?php
if(!defined('ABSPATH')){ /*Only if WP is not already loaded.*/
	if(extension_loaded('zlib')){
		$z = strtolower(ini_get('zlib.output_compression'));
		if ($z == false || $z == 'off')
			ob_start('ob_gzhandler');
	}
	 $root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
      if (file_exists($root.'/wp-load.php')) {
          // WP 2.6
          require_once($root.'/wp-load.php');
      } else {
          // Before 2.6
          require_once($root.'/wp-config.php');
      }
	header ("content-type: text/css; charset: UTF-8");
	$offset = 60 * 60 * 24;
	$expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
	header ($expire);

}


	
	$file = TEMPLATEPATH . '/aWP/style.css';
		if($awpall[default_template_folder] == 'theme' && file_exists($file)){
				include($file);
		}elseif(!$awpall[no_default_css]){
				echo $awpall['default_css'];
		}

		do_action('aWP_CSS');

?>

/*Dummy*/
.dummyclass{
	border:0;
}
