<?php
	/*
	Plugin Name: Compatibility Mode
	Plugin URI: http://ajaxedwp.com
	Description: Helps Lightbox, Slimbox, Lightview, Shutter Reloaded, Contact Forms 7 and more work with AWP.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/
	if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
		add_action('awp_admin_other',array('AWP_compatibilities','admin'));
		add_filter('awp_get_options',array('AWP_compatibilities','awp_get_options'));
	}else{
		add_filter('awp_ajax_comments_actions', array('AWP_compatibilities','compatible'));
		add_filter('awp_ajax_post_actions', array('AWP_compatibilities','compatible'));
		add_filter('awp_ajax_nav_actions', array('AWP_compatibilities','compatible'));
		add_filter('awp_ajax_navigator_actions', array('AWP_compatibilities','compatible'));
	}

register_activation_hook(__file__,array('AWP_compatibilities','set_defaults'));

class AWP_compatibilities {

	function compatible($actions){
	global $awpall,$id;

		if($awpall['comp_Lightview'])
			$actions[] = 'setTimeout("try{Lightview.updateViews();}catch(e){}",2000);'; //lightview

		if($awpall['comp_Slimbox'])
			$actions[] = 'setTimeout("try{Lightbox.init()}catch(e){}",2000);'; //slimbox

		if($awpall['comp_Shutter']){ //Hopefully this will be shrunk to a line soon.
			$srel_main = get_option('srel_main');
		    $srel_included = (array) get_option('srel_included');
			$srel_excluded = (array) get_option('srel_excluded');
			$addshutter = false;
			switch( $srel_main ) {
			case 'srel_pages' :
				if ( in_array($id, $srel_included) )
					$addshutter = 'shutterReloaded.Init();';
				break;

			case 'auto_set' :
				if ( ! in_array($id, $srel_excluded) ) {
					$addshutter = "shutterReloaded.Init('sh');";
					$srel_autoset = true;
				}
				break;

			case 'srel_class' :
				$addshutter = "shutterReloaded.Init('sh');";
				break;

			case 'srel_lb' :
				$addshutter = "shutterReloaded.Init('lb');";
				break;

			default :
				if ( ! in_array($id, $srel_excluded) )
					$addshutter = 'shutterReloaded.Init();';
			}
			$actions[] = 'setTimeout("try{'.$addshutter.'}catch(e){}",2000);'; //slimbox
		}

		if($awpall['comp_Lightbox'])
			$actions[] = 'setTimeout("try{Lightbox.prototype.updateImageList()}catch(e){try{Lightbox.prototype.initialize()}catch(e){}}",2000);';

		if($awpall['comp_CF7'])
			$actions[] = "setTimeout(\"try{jQuery('div.wpcf7 > form').attr({'action':location.href}).ajaxForm({beforeSubmit: wpcf7BeforeSubmit,dataType: 'json',success: wpcf7ProcessJson});}catch(e){}\",2000);";

		if($awpall['comp_Thickbox'])
			$actions[] = 'setTimeout("try{tb_init(\'a.thickbox, area.thickbox, input.thickbox\');}catch(e){}",2000);'; //thickbox

	return $actions;
	}

	function admin(){
	global $aWP, $awpall;

		ob_start();
?>
		<menu>
			<title><?php _e('Add compatibility with the following Scripts and Plugins.','awp');?></title>
			<submenu>
				<item name="comp_Lightbox" type="checkbox" value="1" open="1" d="Lightbox" />
				<item name="comp_Slimbox" type="checkbox" value="1" open="1" d="Slimbox" />
				<item name="comp_Shutter" type="checkbox" value="1" open="1" d="Shutter Reloaded" />
				<item name="comp_Lightview" type="checkbox" value="1" open="1" d="Lightview" />
				<item name="comp_CF7" type="checkbox" value="1" open="1" d="Contact Forms 7" />
				<item name="comp_Thickbox" type="checkbox" value="1" open="1" d="Thickbox" />
			</submenu>
		</menu>
<?php
		$menu =	 ob_get_contents();
		ob_end_clean();
		do_action('awp_build_menu',$menu);
	}

	function awp_get_options($i){
		$i[checkboxes][] = 'comp_Lightview';
		$i[checkboxes][] = 'comp_Slimbox';
		$i[checkboxes][] = 'comp_Shutter';
		$i[checkboxes][] = 'comp_Lightbox';
		$i[checkboxes][] = 'comp_CF7';
		$i[checkboxes][] = 'comp_Thickbox';
		return $i;
	}
}
?>
