<?php
	/*
	Plugin Name: AJAX navigator
	Plugin URI: http://ajaxedwp.com
	Description: Automatically make your entire WordPress website navigatable with AJAX. DO NOT USE with ajax nav module. <b>This module works better then the AJAX Nav module.</b>
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/


$awp_init[] = 'AWP_ajaxnavigator';

register_activation_hook(__file__,array('AWP_ajaxnavigator','set_defaults'));
Class AWP_ajaxnavigator{

	function init(){
	global $awpall;
		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){

			add_action('awp_admin_more_menus',array(&$this,'admin'));
			add_action('awp_admin_more_menu_links',array(&$this,'admin_link'));
			add_filter('awp_get_options',array(&$this,'awp_get_options'));

		}elseif($awpall['ajaxnavigator'] == 'Enabled'){

			$this->awp_live();

			add_action('awp_die',array(&$this,'awp_die'));
			add_action('awp_live',array(&$this,'awp_live'));

			add_action('init', array(&$this, 'maybe_AJAX'),99);
			add_action('awp_js_start',array(&$this,'awp_js_start'));

			add_filter('aWP_JS', array(&$this,'addJS'));
		}
	}
	function awp_die(){
	global $awpall;
		if($awpall['simple_posts'] == 1){
			remove_action('loop_end', array(&$this, 'loop_end'),5);
			remove_action('loop_start', array(&$this, 'loop_start'),5);
		}
	}

	function awp_live(){
	global $awpall,$aWP;
	static $started;

		if(!$started || $aWP['die']){ /* We do not want to do this several times.*/
			add_action('loop_end', array(&$this, 'loop_end'),5);
			add_action('loop_start', array(&$this, 'loop_start'),5);
		}
		
		$started = 1;
	}
	function addJS(){
		echo "\n"."\n".'/* start AJAX nav UnFocus */'."\n var historyKeeper; \n var unFocus;";
		include(ABSPATH . PLUGINDIR . AWP_MODULES . '/ajaxnavigator/unFocus-History-p.js');
		$this->unfocus();
	}

	function unfocus(){
	global $awpall;
?>
//<script>

	aWP.addEvent('load',start_awp_navigator,window);

	var ajax_nav;
	aWP.foward = 0;
	aWP.started = 0;

	function start_awp_navigator(){
		ajax_nav = new awp_navigator;
		var parts = location.href.split('#');
<?php if(!$awpall['ajax_nav_nohash']){?>
		if(!unFocus.History.getCurrent()){
			ajax_nav.addHistory(location.href.replace('<?php echo get_option('home');?>','awp::'));
		}else if(parts[1].substring(0,3) == 'awp'  && parts[1].replace('awp::',<?php echo "'".get_option('home')."'";?>) != parts[0]){
 			aWP.doit({'type': 'navigator', 'url': unFocus.History.getCurrent().replace('awp::',<?php echo "'".get_option('home')."'";?>), 'i' : 'awp_loop', 'force' : 1});
		}
<?php }?>
		aWP.started = 1;
	}

	function awp_navigator(){
		var stateVar = "nothin'";

		this.addHistory = function(newVal) {
			unFocus.History.addHistory(newVal);
		};

		this.historyListener = function(historyHash) {
			stateVar = historyHash;
			var parts = location.href;
			if(aWP.foward != 1 && historyHash != '' && aWP.started != 0){
 				aWP.doit({'type': 'navigator', 'url': historyHash.replace('awp::',<?php echo "'".get_option('home')."'";?>), 'i' : 'awp_loop', 'force' : 1 });
			}

		};

		unFocus.History.addEventListener('historyChange', this.historyListener);
		this.historyListener(unFocus.History.getCurrent());
	}

	function awp_navigator_goforerror(){

		var parts = location.href.split('#');
		document.location = parts[1].replace('awp::',<?php echo "'".get_option('home')."/'";?>);
	
	}


	aWP.addEvent('load',awp_navigator_links,window);

	function awp_navigator_links(){
		<?php echo "var base_url ='".get_option('home')."';";?>

		if(!document.getElementById('awp_loop'))
			return false;

		var anchors = document.getElementsByTagName('a');

		for(x = 0; x < anchors.length; x++){

			if(anchors[x].onclick)
				continue;

			if(anchors[x].href.indexOf(base_url) == -1)
				continue;

			if(anchors[x].href.indexOf('/wp-') != -1)
				continue;

			if(anchors[x].href.lastIndexOf('http://') > 2)
				continue;

			if(anchors[x].getAttribute("rel") != null  && anchors[x].rel.indexOf('noajax') != -1)
				continue;

			if(anchors[x].getAttribute("class") != null  && anchors[x].className.indexOf('noajax') != -1)
				continue;

			if(anchors[x].getAttribute("target") != null   && anchors[x].target.indexOf('_blank') != -1)
				continue;

			if(anchors[x].getAttribute("target") != null   && anchors[x].target.indexOf('_top') != -1)
				continue;

<?php			 if($awpall['ajax_nav_excludelinks'] != ''){
			$excludes = explode(',',$awpall['ajax_nav_excludelinks']);
				if(count($excludes)){
					foreach($excludes as $exclude){
						echo "\t\t\tif(anchors[x].href.indexOf('$exclude') != -1) \n\t\t\t\t continue;";
					}
				}
			}

?>

			anchors[x].onclick = function(){awp_navigator_click(this); return false;};
		}
	}

	function awp_navigator_click(item){
		<?php if(!$awpall['ajax_nav_nohash']){?>
				ajax_nav.addHistory(item.href.replace('<?php echo get_option('home');?>','awp::'));
		<?php }else{?>
 			aWP.doit({'type': 'navigator', 'nav': 'url', 'url': item.href, 'i' : 'awp_loop', 'force' : 1 });
		<?php } ?>
	}

<?php
}

	function awp_js_start(){
	
?>
			navigator: function(postobj){
				if(document.getElementById('awp_loop')){
					postobj['url'] = _d[i].url;
					postobj['id'] = 0;


					if(!_p[i].scrollval){
							var end = pos(i) - 100;
							var cur_scroll = get_current_scroll();
							_p[i].scrollval = cur_scroll - end;
					}

					get_throbber('awp_loop','bigthrobber','<?php echo $awpall['big_throbber']?>');
					aWP.toggle.smooth_scroll(i,-100);
				}else{

					if(_d[i].ths)
						window.location(_d[i].ths.href);
					return false;

				}
			return postobj;
			},
<?php
	}

	function maybe_AJAX(){
		if($_POST['type'] == 'navigator'){
			define('AWP_AJAXED', true);
			ob_start();
			add_action('wp_footer', array(&$this, 'noposts'));
			add_action('loop_start', array(&$this, 'ajax_loop_start'),6);
			add_action('loop_end', array(&$this, 'ajax_loop_end'),6);
			add_action('awp_loop_start', array(&$this, 'ajax_loop_start'));
			add_action('awp_loop_end', array(&$this, 'ajax_loop_end'));
		}else{
			return;
		}
	}

	function AJAX_end(){
		global $awpall, $aWP;
		$response = ob_get_contents();
		ob_end_clean();

		if(!$response){
			$actions[] = 'try{awp_navigator_goforerror();}catch(e){}';
			$response = __('AJAX Page could not be loaded.','awp');
			AWP::make_response($response, $vars,$actions);
			exit;
		}

		$actions[] = 'setTimeout("awp_navigator_links();",500);';

		if(strpos($_POST['url'],'#') !== false){
			$hash = explode('#', $_POST['url']); 
			$vars['focus'] = $hash[1];
		}

		if($awpall['jquery_hide'] && $awpall['jquery_show']){
			$hide_effect = explode('-',$awpall['jquery_hide']);
			$show_effect = explode('-',$awpall['jquery_show']);

			$actions[] = " var ajax_navigator = function(){ jQuery('#awp_loop').$hide_effect[0]('$hide_effect[1]',function(){jQuery('#awp_loop').html(_d[i].response)}).$show_effect[0]('$show_effect[1]'); do_JS(e); aWP.toggle.main()}";
			$vars['update_next'] = 'ajax_navigator';
			$vars['force'] = '1';
		}

		if($aWP['title'] != '') //Converts special characters and html entities to normal characters with correct encoding.
			$actions[] = 'try{var newDiv = document.createElement("newDiv"); newDiv.innerHTML = "'.$aWP['title'].'"; document.title = newDiv.innerHTML;}catch(e){}';

		AWP::make_response($response, $vars,$actions);
	}


	function ajax_loop_start(){
		global $aWP;
		if(!is_feed()){
			$buffer = ob_get_contents();

			preg_match('/<title>([^<]*)<\/title>/',$buffer,$matches);
			$aWP['title'] = $matches[1];

			ob_end_clean();
			ob_start();
		}
	}

	function ajax_loop_end(){
		if(!is_feed()){
				$this->AJAX_end();
				exit;
		}
	}

	function noposts(){
		ob_end_clean();
			echo 'No posts found.';
		exit;
	}

	function set_defaults(){
		global $awpall;
		$awpall[ajaxnavigator] = 'Enabled';
	}

	function loop_start(){
	global $paged, $awpall,$aWP;
		if(!is_feed()){

			if(!($awpall[ajax_nav_home_loop] && (is_home() || is_archive())) && !($awpall[ajax_nav_single_loop] && (is_single() || is_page())) && !$awpall[ajax_nav_loop]  && !$aWP[awp_loop_start]){
				echo '<div id="awp_loop">';
				$aWP['endloop']=1;
				$aWP['awp_loop_start'];
			}
		}
	}

	function loop_end(){
	global $paged,$aWP,$awpall;
		if(!is_feed()){

			if($aWP['endloop']==1){
				$aWP['endloop']=0;
				echo '</div>';
			}
		}

	}

	function awp_get_options($j){
		$j[selects][] = 'ajaxnavigator';
		$j[checkboxes][] = 'ajax_navigator_single_loop';
		$j[checkboxes][] = 'ajax_navigator_home_loop';
		$j[checkboxes][] = 'ajax_navigator_loop';
		$j[checkboxes][] = 'ajax_nav_nohash';
		$j[texts][] = 'ajax_nav_all_keywords';
		return $j;
	}

	function admin(){
	global $awpall, $aWP;

	ob_start();
?>

	<menus>
		<menu id="ajaxnavigator">
			<name><?php _e('Ajax Navigation','awp');?></name>
			<title><?php _e('Ajax Navigation Options','awp');?></title>
		<submenu>
			<desc><?php _e('AJAX everything','awp');?></desc>
			<item name="ajax_nav_excludelinks" open="1" type="text" d="<?php _e('Exclude links with %s in them.','awp');?>">
			<desc><?php _e('Comma seperated list, simple search, no regex.','awp');?></desc>
			</item>
			<item name="ajax_nav_nohash" type="checkbox" d="<?php _e('Do not use navigation hashes.','awp');?>">
			<desc><?php _e('This will disable forward and back buttons','awp');?></desc>
			</item>
		</submenu>
		<submenu>
			<desc><?php _e('The following options control the automatic addition of the required awp_loop div. If you select any of the following options, you will have to edit your theme manually to load that type of pages inline.','awp');?></desc>
			<item name="ajax_navigator_single_loop" open="1" type="checkbox" d="<?php _e('Do NOT automatically add awp_loop div on Single post/page pages.','awp');?>">
			</item>

			<item name="ajax_navigator_home_loop" open="1" type="checkbox" d="<?php _e('Do NOT automatically add awp_loop div on Home and Archive pages.','awp');?>">
			</item>
			<item name="ajax_navigator_loop" type="checkbox" d="<?php _e('Do NOT automatically add awp_loop div on ANY page.','awp');?>">
			</item>
		</submenu>
		</menu>
	</menus>
<?php
	$menu =	 ob_get_contents();
	ob_end_clean();
	?>
		<div id="admin_navigation" name="awp_menu" <?php if($_GET['last_screen'] != 'admin_navigation'){?> class="Disabled" <?php } ?>>
	<?php
		do_action('awp_build_menu',$menu);
	?>
		</div>
	<?php
	}

	function admin_link(){
?>

		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_navigation',1); return false;" id="menu_admin_navigation"><?php _e('Navigation', 'awp'); ?></a></li>
<?php
	}
}
?>
