<?php

	/*
	Plugin Name: Custom Options
	Plugin URI: http://ajaxedwp.com
	Description: Adds the ability to set custom AWP options on individual posts and pages.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/
	if($_REQUEST['set_custom']){
	$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
      if (file_exists($root.'/wp-load.php')) {
          // WP 2.6
          require_once($root.'/wp-load.php');
      } else {
          // Before 2.6
          require_once($root.'/wp-config.php');
      }
		nocache_headers();
		$AWP_customoptions = new AWP_customoptions();
		$AWP_customoptions->update_custom();
	}

$awp_init[] = 'AWP_customoptions';
register_activation_hook(__file__,array('AWP_customoptions','set_defaults'));
//register_deactivation_hook(__file__,array(&$this,'rm_options'));

Class AWP_customoptions{
	function init(){
		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){

				add_action('awp_admin_main_options',array(&$this,'admin'));
				add_filter('awp_get_options',array(&$this,'awp_get_options'));
				if($_GET['post']){
					add_filter('dbx_page_advanced', array(&$this, 'customUI'));
					add_filter('dbx_post_advanced', array(&$this, 'customUI'));
					add_action('init', array(&$this,'start'));
				}
		}elseif($awpall['allow_custom'] == 'Enabled' ){
			add_filter('awp_options', array(&$this, 'get_custom'));
		}
	}

function get_char_map_html(){
	echo '

	<table><tr><td style="width:200px; vertical-align:top;" id="charmap"><div style="overflow:scroll; height:150px;">';

		char_map_build_list();

	echo '</div></td><td id="char_sheet">';

		char_map_build_map(1);

	echo '</td></tr></table>';


}

	function start(){
	global $aWP;
		remove_action('awp_build_menu',array('AWP_admin','admin_panel'),10);
		add_action('awp_build_menu',array(&$this,'options_panel'));
		$aWP[custom_menu][items]['checkbox'][] = array('disable_awp',0);

				do_action('awp_admin_main');

				do_action('awp_admin_posts');

				do_action('awp_admin_comments');

				do_action('awp_admin_commentform');

				do_action('awp_admin_integration');

				do_action('awp_admin_other');

				do_action('awp_admin_ajax');

	}


	function print_menu(){
	global $aWP,$awpall,$awp_mods;


		$id = intval($_GET['post']);

		$aWP_custom = get_post_meta($id, 'awpcustom',true);

		echo '<div id="awp_custom_ops">';
		echo '<select name="awptexts" id="awptexts" onchange="set_option(this,\'awptext\')">';
		echo '<option id="" value=""></option>';
		if($aWP[custom_menu][items]['text']){
			foreach($aWP[custom_menu][items]['text'] as $op){
				$op[1] = stripslashes(htmlspecialchars($op[1],ENT_QUOTES));
				if(!isset($aWP_custom[$op[0]])){

			?>
				<option id="<?php echo $op[0]?>" value="<?php echo $op[1]?>"><?php echo $op[0]?></option>
			<?php
				}else{
					$options .= "<option id='$op[0]' value='".$aWP_custom[$op[0]]."'>$op[0]</option>";
				}
			}
		}
		echo '</select>';

		echo ' <input type="text" name="awptext" id="awptext" value="" />';
		echo ' <input type="button" name="button" onclick="set_custom(\'awptext\');" value="'.__('Add Custom Option', 'awp').'" />';


		echo '<br /><select name="awpchecks" id="awpchecks" onchange="set_option(this,\'awpcheck\')">';
		echo '<option id="" value=""></option>';
		echo '<option id="disable_awp" value=""></option>';
		if($aWP[custom_menu][items]['checkbox']){
			foreach($aWP[custom_menu][items]['checkbox'] as $op){
				if(!isset($aWP_custom[$op[0]])){

			?>
				<option id="<?php echo $op[0]?>" value="<?php echo $op[1]?>"><?php echo $op[0]?></option>
			<?php
				}else{
					$options2 .= "<option id='$op[0]' value='".$aWP_custom[$op[0]]."'>$op[0]</option>";
				}
			}
		}
		echo '</select>';

		echo ' <input type="checkbox" name="awpcheck" id="awpcheck" value="" />';
		echo ' <input type="button" name="button"  onclick="set_custom(\'awpcheck\');" value="Add Custom Option" />';

		if($options != '' || $options2 != '')
		echo '<h4>' . __('Current Post Custom Options', 'awp') . '</h4>';

		if($options != ''){
			echo '<br /><select name="awptext2s" id="awptext2s" onchange="set_option(this,\'awptext2\')">';
			echo '<option id="" value=""></option>';
				echo $options;
			echo '</select>';

			echo ' <input type="text" name="awptext2" id="awptext2" value="" />';
			echo __('Delete Option?','awp').'<input type="checkbox" name="awptext2delete" id="awptext2delete" value="" />';
            echo ' <input type="button" name="button" onclick="set_custom(\'awptext2\');" value="'.__('Edit Custom Option', 'awp').'" />';
		}

		if($options2 != ''){
			echo '<br /><select name="awpchecks" id="awpcheck2s" onchange="set_option(this,\'awpcheck2\')">';
			echo '<option id="" value=""></option>';
			echo $options2;
			echo '</select>';

			echo ' <input type="checkbox" name="awpcheck2" id="awpcheck2" value="" />';
			echo __('Delete Option?','awp').'<input type="checkbox" name="awpcheck2delete" id="awpcheck2delete" value="" />';
            echo ' <input type="button" name="button" onclick="set_custom(\'awpcheck2\');" value="'.__('Edit Custom Option', 'awp').'" />';
		}
	echo '</div>';
	}

	function options_panel($menu){
	global $aWP, $awpall;
		require_once(ABSPATH . PLUGINDIR . '/'. AWP_BASE . '/xmlparser.php');
		$marray = AWP::XML($menu);

		if(is_array($marray[menus])){
			$this->admin_panel_menu($marray[menus][menu]);
		}elseif(is_array($marray[menu])){
			$this->admin_panel_menu($marray[menu]);
		}elseif(is_array($marray[submenu])){
			$this->admin_panel_sub($marray[submenu]);
		}elseif(is_array($marray[item])){
			$this->admin_panel_item($marray[item]);
		}
	}


	function admin_panel_menu($menu){
	global $aWP, $awpall;
			foreach($menu as $m){
			$id = $m[id];

			if($id){
				$aWP[custom_menu][modules][] = array($id,$m[title]);
			}

			 if($m[submenu]){
					$this->admin_panel_sub($m[submenu]);
					if($m[action])
						do_action($m[action]);
			}
		}
	}

	function admin_panel_sub($smenu){
	global $aWP, $awpall;
		foreach($smenu as $menu){
			if(is_array($menu[item])){
				$this->admin_panel_item($menu[item]);

				if($menu[action])
					do_action($menu[action]);
			}
		}
	}
	function admin_panel_item($items){
	global $aWP, $awpall;

		foreach($items as $item){
			$selected = $size =$post =$pre = $extra='';
			$type = $item[type];
			$name = $item[name];

			if($type && $item['global'] != 1){
				if($type != 'select' && $type != 'radio'){
					$value=$awpall[$name];
					$aWP[custom_menu][items][$type][] = array($name,$value);
				}elseif($type == 'radio'){
					$value=$item[value];
					$aWP[custom_menu][items][$type][$name][] = array($value,$item[d]);
				}
				if($item[action])
					do_action($item[action]);
			}
		}
	}

	function admin(){
	global $awpall, $aWP;
	ob_start();
?>
			<item important="2" name="allow_custom" type="select">
				<d><![CDATA[<?php _e('<strong>Custom Options </strong> -- %s','awp');?>]]></d>
				<desc><![CDATA[<?php _e('Occasionally certain posts will not fit with the flow of the rest of the site and do not fit with the general options used. Selecting the following option will allow you to use custom options on a post-by-post basis.','awp');?>]]></desc>
			</item>

<?php


	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);
	}
	function awp_get_options($i){
		 $i['selects'][] = 'allow_custom';
		return $i;
	}

	function get_custom($awpall){
		global $id, $aWP;

		static $posts = array();


		if(!$posts[$id]){

			$aWP_custom = get_post_meta($id, 'awpcustom',true);
			$posts[$id] = $aWP_custom;
			$aWP[disable_awp][$id] = $aWP_custom[disable_awp];
			if($aWP_custom[disable_awp])
				unset($aWP_custom['disable_awp']);

			if(is_array($aWP_custom)){

				return array_merge($awpall, $aWP_custom);

			}else{
				return $awpall;
			}
		}else{
			return array_merge($awpall, $posts[$id]);
		}
	}

	function update_custom(){
	global $awpall, $id,$post,$aWP;
    	if (!isset($id) && is_numeric($_REQUEST['post_ID']) && $_REQUEST['post_ID'] >0)
		   $id = $_REQUEST['post_ID'];

			$stored_custom = get_post_meta($id, 'awpcustom', false);

				if($_POST['name'] == 'disable_awp' && $_POST['value'] != $stored_custom[disable_awp]){
					if($_POST['value'] == 0){
						delete_post_meta($id, 'awp_disable');
					}else{
						add_post_meta($id, 'awp_disable', 1);
					}
				}

			if(isset($stored_custom)){echo 2;
				$stored_custom = unserialize($stored_custom[0]);
				$awp_custom = $stored_custom;
				if($_POST['delete']){
					unset($awp_custom[$_POST['name']]);
				}else{
					$awp_custom[$_POST['name']] = $_POST['value'];
				}
				update_post_meta($id, 'awpcustom', $awp_custom);
			}else{echo 1;
				$awp_custom[$_POST['name']] = $_POST['value'];
				add_post_meta($id, 'awpcustom', $awp_custom);
			}
	}

	function get_adminUI(){
			global $awpall;
			$id = $post->ID;
			$awp_custom = get_post_meta($id, 'awpcustom',true);

			require_once(ABSPATH . PLUGINDIR . '/'. AWP_BASE . '/xmlparser.php');
			$marray = AWP::XML($menu);
			if(is_array($marray[menus])){
				$this->admin_panel_menu($marray[menus][menu]);
			}elseif(is_array($marray[menu])){
				$this->admin_panel_menu($marray[menu]);
			}elseif(is_array($marray[submenu])){
				$this->admin_panel_sub($marray[submenu]);
			}elseif(is_array($marray[item])){
				$this->admin_panel_item($marray[item]);
			}

	}
	function customUI(){
	global $aWP, $awpall, $id,$post;
?>
			<fieldset id="awpUIdiv" class="dbx-box">
				<h3 class="dbx-handle"><?php _e('Custom AWP options for this post', 'awp'); ?></h3>
				<div id="awpUI" class="dbx-content">
					<script type="text/javascript">

						function set_option(bx,f){
							if(f == 'awpcheck' || f == 'awpcheck2'){
								if(bx.options[bx.selectedIndex].value == 1){
									document.getElementById(f).checked = 'checked';
								}else{
									document.getElementById(f).checked = '';
								}
							}else{
								document.getElementById(f).value = bx.options[bx.selectedIndex].value
							}
						}

						function set_custom(f){
							var a = {};
							if(f == 'awpcheck'||f == 'awpcheck2'){
								if(document.getElementById(f).checked == 1){
									val = 1;
								}else{
									val = 0;
								}
									nam = document.getElementById(f+'s').options[document.getElementById(f+'s').selectedIndex].text
							}else{
									nam = document.getElementById(f+'s').options[document.getElementById(f+'s').selectedIndex].text
									val =	document.getElementById(f).value
							}
							try{a['delete'] = document.getElementById(f+'delete').checked }catch(e){}
							a['name'] = nam;
							a['value'] = val;
							do_ajax(a);
						}

						function do_ajax(dat){

									dat['post_ID'] = <?php echo $_GET['post'];?>;

										var finish = function(r){document.getElementById('awp_custom_ops').value = r.responseText; document.getElementById(dat.name).value = dat.value}

									dat['set_custom'] = true;
									var ajax = new Ajax.Request('<?php echo WP_CONTENT_URL."/plugins".AWP_MODULES."/customoptions.php?set_ajax=1"; ?>);',
										{
											method: 'post',
											parameters: dat,
											onComplete:finish

										});
									ajax = null;

						}
					</script>

					<?php $this->print_menu()?>

				</div>
			</fieldset>
<?php
	}
}

?>