<?php
	/*
	Plugin Name: AWP Control Panel
	Plugin URI: http://ajaxedwp.com
	Description: Adds a cohesive structure for a dynamic administration panel that uses XML and Javascript to provide a rich administration experience. <strong>This is a control module and may not be deactivated or activated.</strong>
	Author: Aaron Harun
	Version: 1.0
	Author URI: http://anthologyoi.com/
*/

$AWP_admin = new AWP_admin();

if(!$_REQUEST['custom_options_menu'])
	add_action('awp_build_menu',array($AWP_admin,'admin_panel'));



add_action('awp_admin_overall', 'admin_panel_overall');
add_action('admin_print_footer_scripts', array(&$AWP_admin,'admin_footer_js'));
class AWP_admin{

	function process_admin(){
		global $awpall,$awp_mods;

		if ($_POST["action"] == "saveconfiguration") {

			if($_POST["awp_test"] == 1){
				AWP::update_options($_REQUEST['awp']);
				$awpall[last_modified] = gmdate('Y-m-d_H:i:59');
				update_option('awp_test',$awpall);
				$action = '&act=test';
			}elseif($_POST["awp_test"] == 2){
				update_option('awp_test','');
				$awpall[last_modified] = gmdate('Y-m-d_H:i:59');
				$action = '&act=testdelete';
				$awpall = get_option('awp');
			}else{
				AWP::update_options($_REQUEST['awp']);
				$awpall[last_modified] = gmdate('Y-m-d_H:i:59');
				update_option('awp',$awpall);
				update_option('awp_test','');
				$action = '&act=updated';
			}

		}elseif($_POST["action"] == "restoredefaults"){
			$awp_mods ='';
			$awpall = '';
			AWP::set_defaults();

			$action = '&act=defaults';

		}elseif($_POST["action"] == "restoreupdate" && $_POST['resop']){

			$options = trim($_POST['resop']);
			if( get_magic_quotes_gpc() ) {
				$options = trim(stripslashes($options));
			}
			$options = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $options );
			$options = unserialize($options);
			$awpall = '';

			if(is_array($options)){
				AWP::update_options($options);
				update_option('awp_test',$awpall);
				$action = '&act=restore';
			}
		}elseif ('activate' == $_GET['action'] && $_GET['module']) {

			include_once(ABSPATH . '/wp-admin/admin-functions.php');
			check_admin_referer('activate-module_' . $_GET['module']);
			$module = trim($_GET['module']);
			if ( validate_file($module) )
				wp_die(__('Invalid module.','awp'));
			if ( ! file_exists(ABSPATH . PLUGINDIR . AWP_MODULES .'/' . $module) )
				wp_die(__('Plugin file does not exist.'));
			if (!in_array($module, $awp_mods)) {
				wp_redirect('themes.php?page='.AWP_BASE.'/control/aWP-admin_panel.php&act=failed');

				ob_start();
					@include(ABSPATH . PLUGINDIR . AWP_MODULES .'/' . $module);
					$awp_mods[] = $module;
					sort($awp_mods);
					update_option('awp_mods', $awp_mods);
					do_action('activate_' . ltrim(AWP_MODULES .'/' .  $_GET['module'], '/'));
				ob_end_clean();
				$action ='&act=activated';
			}

		} else if ('deactivate' == $_GET['action'] && $_GET['module']) {
			check_admin_referer('deactivate-module_' . $_GET['module']);
			array_splice($awp_mods, array_search( $_GET['module'], $awp_mods), 1 ); // Array-fu!
			update_option('awp_mods', $awp_mods);


			do_action('deactivate_' .  ltrim(AWP_MODULES .'/' .  $_GET['module'], '/'));
			$action = '&act=deactivate';
		} else if ('remove_messages' == $_POST['action']) {
			$messages = get_option('awp_messages');
			$count = count($messages);
			while($x == false && count($messages) > 0 && $i < $count){
				if($_POST['time'] == array_shift($messages)){
					$x = true;
				}
				$i++;
			}
			update_option('awp_messages',$messages);
			$action = '&act=remove_messages';
		}

			if($_POST['last_screen'])
				$action .= '&last_screen='.$_POST['last_screen'];
		do_action('awp_admin_update');
		wp_redirect('themes.php?page='.AWP_BASE.'/control/aWP-admin_panel.php'.$action);
	}

	function admin_panel($menu){
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
		?>
			<fieldset class="dbx-box"><h3 class="dbx-handle"><?php echo $m[title];?></h3>
			<div class="dbx-content">
				<?php echo $m['desc']?>
				<ul class="def">
					<li class="important">
						<strong><?php echo $m[name]?></strong> -- <select name="awp[<?php echo $id?>]" onchange="aWP_toggle('<?php echo $id?>')">
							<option selected="selected" value="<?php echo $awpall[$id];?>"><?php _e($awpall[$id],'awp');?></option>
							<option value="<?php echo $aWP[select][$id];?>"><?php _e($aWP[select][$id],'awp');?></option>
							</select>
					</li>
				</ul>
		<?php
			}else{


			echo '<fieldset class="dbx-box">';

			if($m[title]){echo '<h3 class="dbx-handle">'.$m[title].'</h3>';}

			echo '<div class="dbx-content">';

			echo $m[desc];

			}



			 if($m[submenu]){
			 	$class = ($awpall[$id])? 'class="'.$awpall[$id].'"' : '' ;
			 	$e_id = ($id)? ' id="'.$id.'"' : '' ;
				echo '<div '.$class.$e_id.'>';
					$this->admin_panel_sub($m[submenu]);
					if($m[action])
						do_action($m[action]);
				echo '</div>';

				} ?>

			</div>
		</fieldset>

	<?php
		}
	}
	function admin_panel_sub($smenu){
	global $aWP, $awpall;
	static $info;
		foreach($smenu as $menu){
			echo $menu['desc'];
			if($menu['info']){
				echo ' <a href="#" onclick="aWP_toggle(\'info-'.$info.'\'); return false;">['.__('More Information','awp').']</a>';
				echo '<span class="Disabled" id="info-'.$info.'"><p>'.$menu[info].'</p></span>';
				$info++;
			}
			if(is_array($menu[item])){
				echo '<ul class="def">';
					$this->admin_panel_item($menu[item]);
					if($menu[action])
						do_action($menu[action]);
				echo '</ul>';
			}
		}

	}
	function admin_panel_item($items){
	global $aWP, $awpall;
		static $radios;
		foreach($items as $item){
			$selected = $size =$post =$pre = $extra='';
			$type = $item[type];
			$name = $item[name];

			if(!$open){

				if($item[important] > 0)
					$class= ' class="level'.$item[important].'"';

				echo sprintf('<li%s> <p>',$class);
			}

			if($item[intro])
				echo __($item[intro],'awp').'<br />';

			if($type){
				if($type != 'select' && $type != 'textarea'){
					switch($type){
						case 'text':
							$value=$awpall[$name];
							break;
						case 'checkbox';
							$value='1';
							$extra = $aWP[$type][$name];
							$pre = '<label>';
							$post = '</label>';
							break;
						case 'radio';
							$value=$item[value];
							$extra = $aWP[$type][$name][$value];
							$pre = '<label>';
							$post = '</label>';
							$radios[$name]++;
							break;
						default:
							$value=$awpall[$name];
					}

					$size = ($item[size]) ? ' size="'.$item[size].'"' : ' ';

					$input = sprintf('<input type="%s" value="%s" name="awp[%s]" %s%s/>',$type,$value,$name,$extra,$size);

				}elseif($type == 'textarea'){
					$value=$awpall[$name];
					$input = '<textarea name="awp['.$name.']" class="'.$item['class'].'" rows="15" cols="55" id="'.$item['name'].'">'.$value.'</textarea>';
				


				}else{

					$input = '<select name="awp['.$name.']">';

					if(!$item[option]){
						$input .= '<option selected="selected" value="'.$awpall[$name].'">'.$awpall[$name].'</option>
						<option value="'.$aWP[$type][$name].'">'.$aWP[$type][$name].'</option>';
					}else{
						foreach($item[option] as $option){
							$selected = ($awpall[$name]==$option[value] ) ? ' selected="selected"' : '';
							$input .= '<option value="'.$option[value].'"'.$selected.'>'.$option[name].'</option>';
						}
					}

					$input .= '</select>';
				}

				if(strpos($item[d],'%s') === false)
					$item[d] = '%s  &laquo;&mdash;'.$item[d];

				$output = $pre.sprintf($item[d],$input).$post;

				if($item[desc]){
					$output .= ' <a href="#" onclick="aWP_toggle(\''.$name.$radios[$name].'\'); return false;">[?]</a>';
					$inline .= '<span class="Disabled" id="'.$name.$radios[$name].'">'.$item[desc].'</span>';
				}
			}

			echo $output;

			if(!$item[open] && !$item[nobreak]){
				echo $inline.'</p></li>'."\n\n";
				$inline = $open = null;
			}else{
				$open = 1;
				echo ' ';
				if(!$item[nobreak])
					echo '<br />'."\n";
				if($item[action])
					do_action($item[action]);
			}
		}
	}
function admin_footer_js() {
	?><script type="text/javascript">
/* <![CDATA[ */
jQuery('#ajaxedwp').submit(function(){
	if (jQuery('.codepress').length)
		jQuery('.codepress').each(function(i){jQuery(this).val(eval(this.id.substr(0,this.id.length-3)+'.getCode()')).removeAttr('disabled')});
})
/* ]]> */
</script>
<?php
}
	function admin_js(){

	?>
			<script type="text/javascript">
				//<![CDATA[

				function aWP_toggle(id,is_menu){
					var div = document.getElementById(id);
					if(div){
						if(div.className == 'Enabled'){
							div.className = 'Disabled';
						}else{
							div.className = 'Enabled';

							if(is_menu){
								if(id != 'admin_main' && id != 'admin_modules'){
									document.getElementById('awp_submit').className = 'Enabled';
								}else{
									document.getElementById('awp_submit').className = 'Disabled';
								}
								document.getElementById('last_screen').value = id;

								var menu = document.getElementById('menu_' + id);
								if(menu)
									menu.style.fontWeight = '800';
							}
						}
					}
				}

				function getElementsByName_iefix(tag, name) {

				     var elem = document.getElementsByTagName(tag);
				     var arr = new Array();
				     for(i = 0,iarr = 0; i < elem.length; i++) {
				          att = elem[i].getAttribute("name");
				          if(att == name) {
				               arr[iarr] = elem[i];
				               iarr++;
				          }
				     }
				     return arr;
				}

				function aWP_hide(){

					var x = getElementsByName_iefix('div','awp_menu');
					for(i=0;i<x.length;i++){
						var div = null;
						div = document.getElementById(x[i].id);

						if(div){
							try{document.getElementById('menu_' + x[i].id).style.fontWeight = '400';}catch(e){}
							div.className = 'Disabled';
						}
					}
				}

				function aWP_hide_empty(){

					var x = getElementsByName_iefix('div','awp_menu');
					for(i=0;i<x.length;i++){
						var div = null;
						div = document.getElementById(x[i].id);

						if(div && div.innerHTML.length <= 50){
							try{document.getElementById('menu_' + x[i].id).style.display = 'none';}catch(e){}
						}
					}
				}
			//]]>

			</script>
			<style 	type="text/css">
				/* Second menu on the header */

				ul.examplemenu {
					margin-left: 0;
					padding-left: 0;
					white-space: nowrap;
				}

				.examplemenu li{
					display: inline;
					list-style-type: none;
					margin:0 !important;
					padding:0 !important;
				}

				.examplemenu a {
					padding: 3px 10px;
					border: solid 1px ;
					color: #fff;
					background: #467aa7 ;
				}

				.examplemenu a:link,.examplemenu a:visited {
					color: #fff;
					text-decoration: none;
				}

				.examplemenu a:hover{
					color: #fff;
					background-color: #578bb8;
					text-decoration: underline;
				}

				.wrap * li * span{
					border:1px solid silver;
					background-color:#fafbfc;
					color:#000;
					width:95%;
					line-height:1.5em;
					margin:5px;
					padding:5px;
				}
				.wrap * ul.def li{
					display:block;
					border:1px solid #BBBBBB;
					background-color:#F0F8FF;
					color:#000000;
					margin:5px;
					padding:5px;
				}
				.wrap * ul.def li.important, ul.def li.level2{
					border:2px solid #000;
				}
				}
				.wrap * ul.def li.importish, ul.def li.level5{
					border:2px solid #BBBBBB;
				}
				.wrap * .Enabled{
					display:block;
				}
				.wrap * .Disabled{
					display:none;
				}

				.wrap * #plugin_info a, #Utilities a{
					display:block;
					margin-bottom:5px;
					border: none ;
				}
				.active .name, .active td {
					background:#E7F7D3 !important;
				}
			</style>

<?php
	}
//
// This is a slightly modified version of get_plugins
//
	function get_modules() {
		global $awp_modules;

		if ( isset( $awp_modules ) ) {
			return $awp_modules;
		}

		$awp_modules = array ();
		$modules_root = ABSPATH . PLUGINDIR . AWP_MODULES ;

		// Files in  WP_CONTENT_DIR;/plugins directory
		$modules_dir = @ dir( $modules_root);
		if ( $modules_dir ) {
			while (($file = $modules_dir->read() ) !== false ) {
				if ( substr($file, 0, 1) == '.' )
					continue;
				if ( is_dir( $modules_root.'/'.$file ) ) {
					$modules_subdir =  dir( $modules_root.'/'.$file );
					if ( $modules_subdir ) {
						while (($subfile = $modules_subdir->read() ) !== false ) {
							if ( substr($subfile, 0, 1) == '.' )
								continue;
							if ( substr($subfile, -4) == '.php' )
								$modules_files[] = "$file/$subfile";
						}
					}
				} else {
					if ( substr($file, -4) == '.php' )
						$modules_files[] = $file;
				}
			}
		}
		if ( !$modules_dir || !$modules_files )
			return $awp_modules;

		foreach ( $modules_files as $modules_file ) {
			if ( !is_readable( "$modules_root/$modules_file" ) )
				continue;

			$modules_data = $this->get_modules_data( "$modules_root/$modules_file" );

			if ( empty ( $modules_data['Name'] ) )
				continue;

			$awp_modules[plugin_basename( $modules_file )] = $modules_data;
		}

		uasort( $awp_modules, create_function( '$a, $b', 'return strnatcasecmp( $a["Name"], $b["Name"] );' ));

		return $awp_modules;
	}

	function get_modules_data( $mod_file ) {
		$mod_data = implode( '', file( $mod_file ));
		preg_match( "@(Plugin|Module) Name:(.*)@i", $mod_data, $mod_name );
		preg_match( "@(Plugin|Module) URI:(.*)@i", $mod_data, $mod_uri );
		preg_match( "@Description:(.*)@i", $mod_data, $description );
		preg_match( "@Author:(.*)@i", $mod_data, $author_name );
		preg_match( "@Author URI:(.*)@i", $mod_data, $author_uri );
		preg_match( "@Requires:(.*)@i", $mod_data, $requires );
		if ( preg_match( "@AWP Release:(.*)@i", $mod_data, $awpversion ))
			$awpversion = trim( $awpversion[1] );
		else
			$awpversion = '';
		if ( preg_match( "@Version:(.*)@i", $mod_data, $version ))
			$version = trim( $version[1] );
		else
			$version = '';

		$description = wptexturize( trim( $description[1] ));

		$name = $mod_name[2];
		$name = trim( $name );
		$mod = $name;
		if ('' != $mod_uri[2] && '' != $name ) {
			$mod = '<a href="' . trim( $mod_uri[2] ) . '" title="'.__( 'Visit plugin homepage' ).'">'.$mod.'</a>';
		}

		if ('' == $author_uri[1] ) {
			$author = trim( $author_name[1] );
		} else {
			$author = '<a href="' . trim( $author_uri[1] ) . '" title="'.__( 'Visit author homepage' ).'">' . trim( $author_name[1] ) . '</a>';
		}

		return array('Name' => $name, 'Title' => $mod, 'Description' => $description, 'Author' => $author, 'Version' => $version, 'AWP_Version' => $awpversion, 'Requires' => $requires);
	}

	function print_modules($c=1){
	global $aWP,$awp_mods;
	?>

				<table<?php if($c){ ?> class="widefat plugins"<?php } ?>>
				<thead>
				<tr>
					<th><?php _e('Module','awp'); ?></th>
					<!--<th style="text-align: center"><?php _e('Module Version','awp'); ?></th>
					<th style="text-align: center"><?php _e('AWP Version','awp'); ?></th>-->
					<th><?php _e('Description'); ?></th>
					<?php if($c){ ?>
					<th style="text-align: center"><?php _e('Status'); ?></th>
					<th style="text-align: center"><?php _e('Action'); ?></th><?php } ?>
				</tr>
				</thead>
			<?php
			//
			// This is a slightly modified version of wp-admin/plugins.php
			//
				$style = '';
				foreach($aWP[modules] as $module_file => $module_data) {
					$style = ('class="alternate"' == $style|| 'class="alternate active"' == $style) ? '' : 'alternate';

					if (!empty($awp_mods) && in_array($module_file, $awp_mods)) {
						$toggle = "<a href='" . wp_nonce_url("themes.php?page=".AWP_BASE."/control/aWP-admin_panel.php&action=deactivate&amp;module=$module_file", 'deactivate-module_' . $module_file) . "' title='".__('Deactivate this AWP Module')."' class='delete'>".__('Deactivate')."</a>";
						$module_data['Title'] = "<strong>{$module_data['Title']}</strong>";
						$style .= $style == 'alternate' ? ' active' : 'active';
						$status = "<span class='active'>".__('Active')."</span>";
					} else {
						$toggle = "<a href='" . wp_nonce_url("themes.php?page=".AWP_BASE."/control/aWP-admin_panel.php&action=activate&amp;module=$module_file", 'activate-module_' . $module_file) . "' title='".__('Activate this module')."' class='edit'>".__('Activate')."</a>";
						$status = __('Inactive');
					}
					if($c){
						$modules_allowedtags = array('a' => array('href' => array(),'title' => array()),'abbr' => array('title' => array()),'acronym' => array('title' => array()),'code' => array(),'em' => array(),'strong' => array());
					}else{
						$modules_allowedtags = array('abbr' => array('title' => array()),'acronym' => array('title' => array()),'code' => array(),'em' => array());
					}
					// Sanitize all displayed data
					$module_data['Title']       = wp_kses($module_data['Title'], $modules_allowedtags);
					$module_data['Version']     = wp_kses($module_data['Version'], $modules_allowedtags);
					$module_data['AWP_Version'] = wp_kses($module_data['AWP_Version'], $modules_allowedtags);
					$module_data['Description'] = wp_kses($module_data['Description'], $modules_allowedtags);
					$module_data['Author']      = wp_kses($module_data['Author'], $modules_allowedtags);

					if ( $style != '' )
						$style = 'class="' . $style . '"';
					echo "
				<tr $style>
					<td class='name'>{$module_data['Title']}</td>
					<!--<td class='vers'>{$module_data['AWP_Version']}</td>
					<td class='vers'>{$module_data['Version']}</td>-->
					<td class='desc'><p>{$module_data['Description']} <cite>".sprintf(__('By %s'), $module_data['Author']).".</cite></p></td>";
					if($c){ echo "<td class='status'>$status</td>";}
					if($c){ echo "<td class='status'>$toggle</td>";}
					echo "
				</tr>";
				}
			?>
			</table>

	<?php

	}

	function start_panel(){
	global $awpall, $aWP, $wp_version;

		$aWP[modules] = $this->get_modules();
		$messages = get_option('awp_messages');
		if(count($messages) > 1){
			$aWP[messages] .= '<h3>AWP Updates:</h3><ol>';
			foreach($messages as $m){
				if(is_int($m)){$time = $m; continue;}
					$aWP[messages] .= '<li>'.$m.'</li>';
			}
			$aWP[messages] .= '</ol> <form method="post"><input type="hidden" name="time" value="'.$time.'"/><input type="hidden" value="remove_messages" name="action" /><input type="submit" value="'.__('Remove these messages.','awp').'"></form>';
		}

		switch($_GET['act']){
		case 'updated':
			$aWP[admin_message] .= __('AWP options updated.','awp').'<br/>';
			break;
		case 'activated':
			$aWP[admin_message] .= __('Module Activated Successfully.','awp').'<br/>';
			break;
		case 'deactivated':
			$aWP[admin_message] .= __('Module Deactivated Successfully.','awp').'<br/>';
			break;
		case 'failed':
			$aWP[admin_message] .= __('The module you attempted to activate has an error in it and could not be activated.','awp').'<br />';
			break;
		case 'defaults':
			$aWP[admin_message] .= __('AWP settings restored to defaults.','awp').'<br/>';
			break;
		case 'restore':
			$aWP[admin_message] .= __('Options successfully saved as test options. Please review them for consistancy before saving.','awp').'<br />';
			break;
		case 'test':
			$aWP[admin_message] .= __('AWP test options updated. These settings will not go live until you save them.','awp').'<br/>';
			break;
		case 'testdelete':
			$aWP[admin_message] .= __('AWP test options deleted.','awp').'<br/>';
			break;
		}

		if (version_compare($wp_version, '2.1', '<')){
			$aWP[admin_message] .= __('This version of Wordpress is outdated and not supported by this plugin. Please upgrade to the latest version from','awp').'<a href="http://wordpress.org/download/">wordpress.org</a> <br />';
		}

		$awp_test = get_option('awp_test');

		if(is_array($awp_test)){
			$awpall = $awp_test;
			$aWP['is_test']= true;
			$aWP[admin_message] .= __('You are using the test options currently. You must save them as live options or delete them to see the current live options.','awp').__('To view these settings append ?awp=test to any URL in your blog or click the follwing link to view your homepage>','awp').'<a href="'.get_settings('siteurl').'?awp=test">'.get_settings('siteurl').'</a';
		}

		$radios = array();
		$selects = array();
		$texts = array();
		$checkboxes = array();

		$options = apply_filters('awp_get_options', array('texts'=>$texts, 'radios'=>$radios, 'selects'=>$selects, 'checkboxes' =>$checkboxes));

		foreach($options['radios'] as $radio){
			$aWP[radio][$radio[0]][$awpall[$radio[0]]] = 'checked="checked"';
			if(!$awpall[$radio[0]])
			$aWP[radio][$radio[0]][$radio[1]] = 'checked="checked"';
		}

		foreach($options['texts'] as $name){
			$awpall[$name]= stripslashes(htmlspecialchars($awpall[$name],ENT_QUOTES));
		}

		foreach($options['checkboxes'] as $name){
			if(!empty($awpall[$name]))
				$aWP[checkbox][$name]= 'checked="checked"';
		}

		foreach($options['selects'] as $select){
			if(!$awpall[$select]){
				$awpall[$select] = 'Disabled';
			}
			$aWP[select][$select] = ($awpall[$select] == 'Disabled') ? 'Enabled' : 'Disabled';
		}

	}

}

function admin_panel_overall(){

	ob_start();
?>
<menu>
	<title><?php _e('Overall Options','awp');?></title>
<submenu>
	<desc><![CDATA[<p><strong><?php _e('All options on this control the overall options for the plugin, and options that are used by multiple modules.','awp');?></p></strong>]]></desc>
</submenu>
	<submenu>

		<desc><?php _e('You may customize the texts for %count here. A % will be replaced with the number of comments. Example: A show text of "Show %count inline" and a "some comments" text of "% comments" on a post with 5 comments will be displayed as "Show 5 comments inline."','awp'); ?></desc>

		<item important="5" type="text" open="1" d="<?php _e('One Comment Text: %s','awp');?> " name="one_comment"/>
			<item type="text" open="1" d="<?php _e('Some Comments: %s','awp');?>" name="some_comments"/>
			<item type="text" open="1" d="<?php _e('Zero Comments: %s','awp');?>" name="zero_comments"/>

	</submenu>

	<submenu>
		<desc><![CDATA[<?php _e('By default, all template files reside in the aWP module\'s folder; however, if you customise them, these changes are overwritten when you upgrade. The following options allow you to move all template files into other directories, so the files are not overwritten during upgrades.','awp'); ?>]]></desc>
		<info><?php _e('In the following options, the extended information uses %something% to designate the name of files. These are based on the theme, module, or file you are currently using. The module name is the same as its folder name. If the specified file is not found, the plugin defaults to the default template. If this occurs, ensure you have created the directory structure properly.','awp'); ?></info>
		<item type="radio" open="1" value="normal" name="default_template_folder">
			<d><?php _e('All module template files are in their default location.','awp'); ?></d>
			<desc><?php _e('This option should be selected if you have not modified the templates, and these files may be overwritten when uploading later versions.','awp'); ?></desc>
		</item>

		<item type="radio" open="1" value ="theme"  name="default_template_folder">
			<d><?php _e('Some or all templates are in my current WordPress theme\'s folder.','awp'); ?></d>
			<desc><![CDATA[<?php _e('All customized templates should be in a folder in your wordpress theme.<br />The files should be in the following structure: <br />%theme_directory%/aWP/%module_name%/%template_file%.php','awp'); ?>]]></desc>
		</item>

		<item type="radio" value ="plugins" name="default_template_folder">
			<d><?php _e('Some or all templates are in a directory in my plugins directory.','awp'); ?></d>
			<desc><![CDATA[<?php _e('All customized templates should be in a folder in your wordpress plugins directory.<br />The files should be in the following structure: <br />plugins/aWP-templates/%module_name%/%template_file%.php','awp'); ?>]]></desc>
		</item>
	</submenu>

	<submenu>
		<desc><![CDATA[<?php _e('Why be like everyone else? Pick your own Throbber.','awp'); ?>]]></desc>
			<d>Pick a throbber.</d>
		<item type="radio" nobreak="1" value =""  name="throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbber.gif" alt='normal'> %s ]]></d>
		</item>
		<item type="radio" nobreak="1" value ="bouncing_ball"  name="throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbberbouncing_ball.gif" alt='bouncy ball'> %s ]]></d>
		</item>
		<item type="radio" nobreak="1" value ="sun"  name="throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbbersun.gif" alt='sun'> %s ]]></d>
		</item>
		<item type="radio" nobreak="1" value ="dot"  name="throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbberdot.gif" alt='dot'> %s ]]></d>
		</item>
		<item type="radio" nobreak="1" value ="kit"  name="throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbberkit.gif" alt='kit'> %s ]]></d>
		</item>
		<item type="radio" nobreak="1" value ="radar"  name="throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbberradar.gif" alt='radar'> %s ]]></d>
		</item>
	</submenu>
	<submenu>
		<desc><![CDATA[<?php _e('Why be like everyone else? Pick your own Throbber.','awp'); ?>]]></desc>
			<d>Pick a really big throbber.</d>
		<item type="radio" nobreak="1" value ="big"  name="big_throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbberbig.gif" alt='normal'> %s ]]></d>
		</item>
		<item type="radio" nobreak="1" value ="large"  name="big_throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbberlarge.gif" alt='normal'> %s ]]></d>
		</item>
		<item type="radio" nobreak="1" value ="bar_dark"  name="big_throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbberbar_dark.gif" alt='normal'> %s ]]></d>
		</item>
		<item type="radio" nobreak="1" value ="bar"  name="big_throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbberbar.gif" alt='normal'> %s ]]></d>
		</item>
		<item type="radio" nobreak="1" value ="clock"  name="big_throbber">
			<d><![CDATA[ <img src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo AWP_BASE;?>/images/throbberclock.gif" alt='normal'> %s ]]></d>
		</item>
	</submenu>

	<submenu>
		<desc><![CDATA[<?php _e('Force AJAXed WordPress to use the following language.','awp');?>]]></desc>
		<item name="awp_language" type="select" d="<?php _e('Override the default WordPress Language with: %s','awp');?>">
			<option value="" name="<?php _e('None','awp');?>"/>
			<option value="es_ES" name="<?php _e('Spanish (es_ES)','awp');?>"/>
			<option value="fr" name="<?php _e('French (fr)','awp');?>"/>
			<option value="it_IT" name="<?php _e('Italian (it_IT)','awp');?>"/>
			<option value="ja" name="<?php _e('Japanese (ja)','awp');?>"/>
		</item>
	</submenu>

	<submenu>
		<desc><![CDATA[<?php _e('When content is loaded inline what should aWP do?','awp'); ?>]]></desc>
			<item type="radio" open="1" value=""  name="scrolling_type">
				<d><?php _e('There should be a smooth scrolling effect that slowly brings the user to the new content.','awp'); ?></d>
			</item>
			<item type="radio" open="1" value="abrupt"  name="scrolling_type">
				<d><?php _e('Attempt to focus immediately on the new content.','awp'); ?></d>
			</item>
			<item type="radio" value="none"  name="scrolling_type">
				<d><?php _e('No scrolling of any kind. Let the user find the new content on their own.','awp'); ?></d>
			</item>
	</submenu>
	<submenu>
		<item type="checkbox" value="1" name="no_default_css">
				<d><?php _e('Do not load default CSS.','awp');?></d>
				<desc><?php _e('AWP loads some default structural CSS. This will disable it, but it will not disable the required CSS.','awp');?></desc>
		</item>
		<item type="checkbox" value="1" name="js_footer">
				<d><?php _e('Load JavaScript in the blog footer.','awp');?></d>
		</item>
	</submenu>
	<submenu>
		<desc><![CDATA[<?php _e('AJAXed WordPress News','awp'); ?>]]></desc>
			<item type="checkbox" open="1" value="1"  name="no_news">
				<d><?php _e('Disable all news announcements?','awp'); ?></d>
			</item>
			<item type="checkbox" open="1" value="1"  name="news_no_donation">
				<d><?php _e('Disable news announcments of donations?','awp'); ?></d>
			</item>
	</submenu>
	<submenu>
		<item type="checkbox" value="1" name="give_credit">
				<d><?php _e('Append a small link to aWP to blog footer.','awp');?></d>
				<desc><?php _e('Please consider leaving this option checked to append a small "AJAXed with AWP" link in the footer of your blog\'s front page. This helps promote the plugin.','awp');?><?php  _e('(As of version 1.19, this is no longer a site-wide link.)','awp');?></desc>
		</item>
	</submenu>
<!--	<submenu>
		<item type="textarea" value="1" name="default_css" class="codepress css">
				<desc><?php _e('Edit the default CSS.','awp');?></desc>
		</item>
	</submenu>-->
</menu>
<?php


	$admin_menu =	 ob_get_contents();
	ob_end_clean();
	do_action('awp_build_menu',$admin_menu);
}


add_filter('awp_get_options','awp_admin_get_options');

function awp_admin_get_options($j){
	$j[radios][] = array('default_template_folder','normal');
	$j[radios][] = array('throbber','');
	$j[radios][] = array('big_throbber','big');
	$j[radios][] = array('scrolling_type','');
	$j[checkboxes][] = 'give_credit';
	$j[checkboxes][] = 'js_footer';
	$j[checkboxes][] = 'no_news';
	$j[checkboxes][] = 'no_default_css';
	$j[checkboxes][] = 'news_no_donation';
	return $j;
}

?>
