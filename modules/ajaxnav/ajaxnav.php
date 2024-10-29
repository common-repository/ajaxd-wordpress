<?php
	/*
	Plugin Name: AJAX navigation
	Plugin URI: http://ajaxedwp.com
	Description: Navigate all or part of your blog with AJAX. This module is completely stand-alone. Do not use with AJAX Navigator Module!
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/


$awp_init[] = 'AWP_ajaxnav';

register_activation_hook(__file__,array('AWP_ajaxnav','set_defaults'));
Class AWP_ajaxnav{

	function init(){
	global $awpall;
		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){

			add_action('awp_admin_more_menus',array(&$this,'admin'));
			add_action('awp_admin_more_menu_links',array(&$this,'admin_link'));
			add_filter('awp_get_options',array(&$this,'awp_get_options'));

		}elseif($awpall['ajaxnav'] == 'Enabled'){

			$this->awp_live();

			add_action('awp_die',array(&$this,'awp_die'));
			add_action('awp_live',array(&$this,'awp_live'));

			add_action('awp_ajax_type_nav', array(&$this, 'AJAX'));
			add_action('loop_end', array(&$this, 'loop_end'));
			add_action('loop_start', array(&$this, 'loop_start'));
			add_action('awp_js_start',array(&$this,'awp_js_start'));

			/*Template Functions*/
			add_action('awp_nav_bar_single', array(&$this,'post_nav_bar'));
			add_action('awp_nav_bar', array(&$this,'nav_bar'));


			add_filter('aWP_JS', array(&$this,'addJS'));

			add_action('awp_nav_single_onclick', array(&$this,'single_post_add_onclick'));

			if($awpall['ajax_nav_pages'])
				add_filter('wp_list_pages', array(&$this,'pages_add_onclick'));

			if($awpall['ajax_nav_categories'])
				add_filter('wp_list_categories', array(&$this,'cats_add_onclick'));
		}
	}
	function awp_die(){
	global $awpall;
		if($awpall['simple_posts'] == 1){
			remove_action('loop_end', array(&$this, 'loop_end'));
			remove_action('loop_start', array(&$this, 'loop_start'));
		}
	}

	function awp_live(){
	global $awpall,$aWP;
	static $started;

		if(!$started || $aWP['die']){ /* We do not want to do this several times.*/
			add_action('loop_end', array(&$this, 'loop_end'));
			add_action('loop_start', array(&$this, 'loop_start'));
		}
		
		$started = 1;
	}
	function addJS(){
		echo "\n"."\n".'/* start AJAX nav UnFocus */'."\n var historyKeeper; \n var unFocus;";
		include(ABSPATH . PLUGINDIR . AWP_MODULES . '/ajaxnav/unFocus-History-p.js');
		$this->unfocus();
	}

	function unfocus(){
	global $awpall;
?>
//<script>

	aWP.addEvent('load',start_awp_nav,window);

	var ajax_nav;
	aWP.foward = 0;
	aWP.started = 0;

	function start_awp_nav(){
		ajax_nav = new awp_nav;

		var parts = location.href.split('#');
<?php if(!$awpall['ajax_nav_nohash']){?>
		if(!unFocus.History.getCurrent()){
			ajax_nav.addHistory(location.href.replace('<?php echo get_option('home')."/";?>','awp::'));
		}else if(parts[1].replace('awp::',<?php echo "'".get_option('home')."/'";?>) != parts[0]){
			aWP.doit({'type': 'nav', 'nav': 'url', 'url': unFocus.History.getCurrent(), 'i' : 'awp_loop', 'force' : 1 });
		}
<?php }?>
		aWP.started = 1;
	}

	function awp_nav(){
		var stateVar = "nothin'";

		this.addHistory = function(newVal) {
			unFocus.History.addHistory(newVal);
		};

		this.historyListener = function(historyHash) {

			stateVar = historyHash;
			var parts = location.href;
			if(aWP.foward != 1 && historyHash != '' && aWP.started != 0){
				aWP.doit({'type': 'nav', 'nav': 'url', 'url': historyHash, 'i' : 'awp_loop', 'force' : 1 });
			}

		};

		unFocus.History.addEventListener('historyChange', this.historyListener);
		this.historyListener(unFocus.History.getCurrent());
	}

	function awp_nav_goforerror(){

		var parts = location.href.split('#');
		document.location = parts[1].replace('awp::',<?php echo "'".get_option('home')."/'";?>);
	
	}

<?php if($awpall[ajax_nav_all] !=''){ ?>
	aWP.addEvent('load',awp_nav_links,window);

	function awp_nav_links(){
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

			anchors[x].onclick = function(){awp_nav_click(this); return false;};
		}
	}

	function awp_nav_click(item){
<?php if(!$awpall['ajax_nav_nohash']){?>
		ajax_nav.addHistory(item.href.replace('<?php echo get_option('home')."/";?>','awp::'));
<?php }else{?>
	aWP.doit({'type': 'nav', 'nav': 'url', 'url': item.href.replace('<?php echo get_option('home')."/";?>','awp::'), 'i' : 'awp_loop', 'force' : 1 });
<?php } ?>
	}

<?php } ?>

<?php

		$this->search_form();
}

	function awp_js_start(){
?>
			nav: function(postobj){
				if(document.getElementById('awp_loop')){
					aWP.foward = 1;
					<?php if(!$awpall['ajax_nav_nohash']){?>
					if(_d[i].ths && _d[i].nav != 'url'){
						ajax_nav.addHistory(_d[i].ths.href.replace('<?php echo get_option('home')."/";?>','awp::'));
					}
					<?php } ?>
					aWP.foward = 0;

					postobj['nav'] = _d[i].nav;
					if(_d[i].nav != 'single' && _d[i].nav != 'url'){
						postobj['pagenum'] = _d[i].pagenum;
					}

					if(_d[i].nav == 'url'){

						if(_d[i].url.slice(0, 5) != 'awp::')
							return false;

						postobj['url'] = _d[i].url;
						postobj['id'] = 0;
					}

					if(_d[i].nav == 'cat'){
						postobj['cat_id'] = _d[i].cat_id;
						postobj['id'] = 0;
					}

					get_throbber('awp_loop','bigthrobber','large');
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

	function AJAX(){
	global $id, $post, $awpall;
	global $hemingway; //for themes that aren't quite sure how to place nice with others.
						// There are these things called filters and hooks, USE THEM.

	global $user_login,$userdata;;
		get_currentuserinfo();

		ob_start();

		if($_REQUEST['nav'] == "single" || $_REQUEST['nav'] == "page" || $_REQUEST['nav'] == "cat"){
			$id = (int) $_REQUEST['id'];

			if($_REQUEST['nav']=='cat')
				$id = $_REQUEST['cat_id'];

			if($id){
				unset($GLOBALS['wp_query']);
				$GLOBALS['wp_query'] =& new WP_Query();
				if($_REQUEST['nav'] == "single"){
					$GLOBALS['wp_query']->query('p='.$id);
					if(get_single_template()){
						include(get_single_template());
					}else{
						include(TEMPLATEPATH . '/index.php');
					}


				}elseif($_REQUEST['nav'] == "page"){
					$GLOBALS['wp_query']->query('page_id='.$id);
					if(get_page_template()){
						include(get_page_template());
					}else{
						include(TEMPLATEPATH . '/index.php');
					}
				}elseif($_REQUEST['nav'] == "cat"){
					$GLOBALS['wp_query']->query('cat='.$id);
					if(get_archive_template()){
						include(get_archive_template());
					}else{
						include(TEMPLATEPATH . '/index.php');
					}
				}
				$title = wp_title('',false);

			}else{
				return false;
			}


		}elseif($_REQUEST['nav'] == "url"){
			if(strpos($_REQUEST['url'], '://') === FALSE){
				$url = str_replace('awp::',get_option('home').'/',$_REQUEST['url']);

				if(strpos($url,'?') !== false){
					$url .= '&awp=ajax&noredirect=1';
				}else{
					$url .= '?awp=ajax&noredirect=1';
				}


				if(function_exists('curl_init') && !$awpall['nocurl']){
					
					foreach ($_COOKIE as $k => $v){
						$cookiestring .= $k.'='.$v.';';
					}
	
					$agent = "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.1) Gecko/2008071719 Firefox/3.0.1";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt( $ch, CURLOPT_COOKIE, $cookiestring );
					curl_setopt($ch, CURLOPT_USERAGENT, $agent);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					$content = curl_exec($ch);
					curl_close($ch);

				}else{
					$content = file_get_contents($url);
				}

				preg_match('/<title>([^<]*)<\/title>/',$content,$matches);
				$title = $matches[1];
				echo $content;
			}
		}else{
			global $paged;
			$paged = (int) $_REQUEST['pagenum'];
			if($paged){
				unset($GLOBALS['wp_query']);
				$GLOBALS['wp_query'] =& new WP_Query();
				$GLOBALS['wp_query']->query('paged='.$paged);
				include(TEMPLATEPATH . '/index.php');
			}
		}

		$buffer = ob_get_contents();
		ob_end_clean();
		preg_match('@<!--awp_loop-->([\S\s]*)<!--awp_loop-->@',$buffer,$match);
		$response = str_replace('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'] ,get_settings('siteurl'),$match[0]);

		if(!$response){

			$actions[] = 'try{awp_nav_goforerror();}catch(e){}';
			$response = __('AJAX Page could not be loaded.','awp');

			AWP::make_response($response, $vars,$actions);
			exit;

		}

		if($awpall[ajax_nav_all] != '')
			$actions[] = 'setTimeout("awp_nav_links();",500);';

		if($awpall['jquery_hide'] && $awpall['jquery_show']){
			$hide_effect = explode('-',$awpall['jquery_hide']);
			$show_effect = explode('-',$awpall['jquery_show']);

			$actions[] = " var ajax_navigator = function(){ jQuery('#awp_loop').$hide_effect[0]('$hide_effect[1]',function(){jQuery('#awp_loop').html(_d[i].response)}).$show_effect[0]('$show_effect[1]'); do_JS(e); aWP.toggle.main()}";
			$vars['update_next'] = 'ajax_navigator';
			$vars['force'] = '1';
		}

		if($title != '') //Converts special characters and html entities to normal characters with correct encoding.
			$actions[] = 'try{var newDiv = document.createElement("newDiv"); newDiv.innerHTML = "'.$title.'"; document.title = newDiv.innerHTML;}catch(e){}';

		AWP::make_response($response, $vars,$actions);
	}

	function set_defaults(){
		global $awpall;
		$awpall[ajaxnav] = 'Enabled';
	}

	function loop_start(){
	global $paged, $awpall,$aWP;
		if(!is_feed()){

			if(!($awpall[ajax_nav_home_loop] && (is_home() || is_archive())) && !($awpall[ajax_nav_single_loop] && (is_single() || is_page())) && !$awpall[ajax_nav_loop]){
				echo '<div id="awp_loop"><!--awp_loop-->';
				$aWP['endloop']=1;
			}


			if(is_single() && $awpall[ajax_nav_single] && $awpall[ajax_nav_single_links] == 'above'){
				$this->post_nav_bar();
			}elseif(!$awpall[ajax_nav_home_loop] && is_home() && ($awpall[ajax_nav_home_links] == 'above' || $awpall[ajax_nav_home_links] == 'both')){
				$this->nav_bar();
			}
		}
	}

	function loop_end(){
	global $paged,$aWP,$awpall;
		if(!is_feed()){
			if(is_single() && $awpall[ajax_nav_single] && $awpall[ajax_nav_single_links] == 'below'){
				$this->post_nav_bar();
			}elseif(!$awpall[ajax_nav_home_loop] && is_home() && ($awpall[ajax_nav_home_links] == 'below' || $awpall[ajax_nav_home_links] == 'both')){
				$this->nav_bar();
			}

			if($aWP['endloop']==1){
				$aWP['endloop']=0;
				echo '<!--awp_loop--></div>';
			}
		}

	}

	function search_form(){
?>
		function add_ajax_form(){
			try{document.getElementById('searchform').onsubmit = function (){ajax_nav.addHistory('<?php echo get_option('home');?>?s='+document.getElementById('s').value);  return false;};}catch(e){}
		}
		aWP.addEvent('load',add_ajax_form,window);
<?php
	}


	function post_nav_bar(){
	?>
			<div style="clear:both"></div>
				<div style="width:100%;" class="awp_nav">
				<div style="float:left;width:40%;"><?php awp_previous_post_link(); ?></div>

	<?php if(check_awp_previous_post_link() && check_awp_next_post_link()){ ?>
				<div style="float:left;width:18%; text-align:center;" id="awp_nav_center"> &mdash; </div>
	<?php } ?>

				<div style="float:right;width 40%; text-align:right;"><?php awp_next_post_link(); ?></div>
				</div>
			<div style="clear:both"></div>
	<?php
	}

	function nav_bar(){
	global $awpall;
	?>
		<div style="clear:both"></div>
			<div style="width:100%;" class="awp_nav">
			<div style="float:left;width:40%;"><?php awp_posts_nav_link('','','&laquo; '.__('Older')) ?></div>	

	<?php if(check_awp_previous_post_link() && check_awp_next_post_link()){ ?>
				<div style="float:left;width:18%; text-align:center;" id="awp_nav_center"> &mdash; </div>
	<?php } ?>

			<div style="float:right;width:40%; text-align:right;"><?php awp_posts_nav_link('',__('Newer').' &raquo;','') ?></div>
			</div>
		<div style="clear:both"></div>
	<?php
	}


	function pages_add_onclick($pages) {
		return preg_replace_callback('!(<li class="page_item page-item-([0-9]*)[^>]*><a([^>]*)>)!ims', array(&$this,'pages_add_onclick_finish'), $pages);
	}

	function pages_add_onclick_finish($matches){
		$page_id = $matches[2];
		$link = $matches[0];

		$onclick = $matches[3]. ' onclick="aWP.doit('."{'id': '$page_id', 'type': 'nav', 'ths': this, 'nav': 'page', 'i' : 'awp_loop', 'force' : 1 }); return false;\"";

		$link = str_replace($matches[3], $onclick, $link);

	return $link;
	}

	function cats_add_onclick($cats) {
		return preg_replace_callback('!(<li class="cat-item cat-item-([0-9]*)">[\s\S]*?<a([^>]*)>)!ims', array(&$this,'cats_add_onclick_finish'), $cats);
	}

	function cats_add_onclick_finish($matches){
		$cat_id = $matches[2];
		$link = $matches[0];
		$onclick = $matches[3]. ' onclick="aWP.doit('."{'cat_id': '$cat_id', 'type': 'nav', 'ths': this, 'nav': 'cat', 'i' : 'awp_loop', 'force' : 1 }); return false;\"";
		$link = str_replace($matches[3], $onclick, $link);

	return $link;
	}

	function awp_get_options($j){
		$j[selects][] = 'ajaxnav';
		$j[checkboxes][] = 'ajax_nav_pages';
		$j[checkboxes][] = 'ajax_nav_single';
		$j[checkboxes][] = 'ajax_nav_search';
		$j[checkboxes][] = 'ajax_nav_home';
		$j[checkboxes][] = 'ajax_nav_categories';
		$j[checkboxes][] = 'ajax_nav_some';
		$j[checkboxes][] = 'ajax_nav_single_loop';
		$j[checkboxes][] = 'ajax_nav_home_loop';
		$j[checkboxes][] = 'ajax_nav_loop';
		$j[checkboxes][] = 'ajax_nav_nohash';
		$j[texts][] = 'ajax_nav_all_keywords';
		$j[radios][] = array('ajax_nav_single_links','above');
		$j[radios][] = array('ajax_nav_home_links','above');
		$j[radios][] = array('ajax_nav_all','');
		return $j;
	}

	function admin(){
	global $awpall, $aWP;

	ob_start();
?>

	<menus>
		<menu id="ajaxnav">
			<name><?php _e('Ajax Navigation','awp');?></name>
			<title><?php _e('Ajax Navigation Options','awp');?></title>
		<submenu>
			<desc><?php _e('Use AJAX navigation with specific parts of your blog.','awp');?></desc>
			<item name="ajax_nav_pages" open="1" type="checkbox" d="<?php _e('Modify wp_list_pages to load pages inline?','awp');?>" />
			<item name="ajax_nav_pages" open="1" type="checkbox" d="<?php _e('Modify wp_list_categories to load archives inline?','awp');?>" />
			<item name="ajax_nav_single" open="1" type="checkbox" d="<?php _e('Load single page navigation inline?','awp');?>" />
			<item name="ajax_nav_home" open="1" type="checkbox" d="<?php _e('Load index page navigation inline?','awp');?>" />
			<item name="ajax_nav_search" type="checkbox" d="<?php _e('Attempt to AJAXify your theme\'s search form?','awp');?>" />
		</submenu>
		<submenu>
			<desc><?php _e('AJAX everything','awp');?></desc>
			<item name="ajax_nav_all" value="" open="1" type="radio" d="<?php _e('Do not automatically attempt to load blog pages inline.','awp');?>" />
			<item name="ajax_nav_all" value="some" open="1" type="radio" d="<?php _e('Attempt to load ALL blog pages inline?','awp');?>" />
			<item name="ajax_nav_excludelinks" open="1" type="text" d="<?php _e('Exclude links with %s in them.','awp');?>">
			<desc><?php _e('Comma seperated list, simple search, no regex.','awp');?></desc>
			</item>
			<item name="ajax_nav_nohash" type="checkbox" d="<?php _e('Do not use navigation hashes.','awp');?>">
			<desc><?php _e('This will disable forward and back buttons','awp');?></desc>
			</item>
		</submenu>
		<submenu>
				<desc><?php _e('Advanced options for Single Post Navigation','awp');?></desc>
			<item type="radio" open="1" value="above" name="ajax_nav_single_links">
				<d><?php _e('Show links above posts.','awp');?></d>
			</item>

			<item type="radio" open="1" value ="below"  name="ajax_nav_single_links">
				<d><?php _e('Show navigation links below posts.','awp');?></d>
			</item>

			<item type="radio" value ="none" name="ajax_nav_single_links">
				<d><?php _e('Do not show navigation links automatically.','awp');?> </d>
				<desc><?php _e('You will have to edit your theme manually.','awp');?></desc>
			</item>
		</submenu>
		<submenu>
				<desc><?php _e('Advanced options for Home Page Navigation','awp');?></desc>
			<item type="radio" open="1" value="above" name="ajax_nav_home_links">
				<d><?php _e('Show links above posts.','awp');?></d>
			</item>

			<item type="radio" open="1" value ="below"  name="ajax_nav_home_links">
				<d><?php _e('Show navigation links below posts.','awp');?></d>
			</item>

			<item type="radio" open="1" value ="both"  name="ajax_nav_home_links">
				<d><?php _e('Show navigation links both above and below posts.','awp');?></d>
			</item>

			<item type="radio" value ="none" name="ajax_nav_home_links">
				<d><?php _e('Do not show navigation links automatically.','awp');?> </d>
				<desc><?php _e('You will have to edit your theme manually.','awp');?></desc>
			</item>
		</submenu>

		<submenu>
			<desc><?php _e('The following options control the automatic addition of the required awp_loop div. If you select any of the following options, you will have to edit your theme manually to load that type of pages inline.','awp');?></desc>
			<item name="ajax_nav_single_loop" open="1" type="checkbox" d="<?php _e('Do NOT automatically add awp_loop div on Single post/page pages.','awp');?>">
			</item>

			<item name="ajax_nav_home_loop" open="1" type="checkbox" d="<?php _e('Do NOT automatically add awp_loop div on Home and Archive pages.','awp');?>">
			</item>
			<item name="ajax_nav_loop" type="checkbox" d="<?php _e('Do NOT automatically add awp_loop div on ANY page.','awp');?>">
			</item>
		</submenu>
		<submenu>
			<desc><?php _e('Advanced Options.','awp');?></desc>

			<item name="ajax_nav_nocurl" type="checkbox" d="<?php _e('Disable CURL?','awp');?>">
			<desc><?php _e('By default, the module tries to use CURL so that users can use cookies still and WordPress will see them as logged in. If you are unable to load any pages, try checking this option.','awp');?></desc>
			</item>

		</submenu>
		</menu>
	</menus>
<?php
	$menu =	 ob_get_contents();
	ob_end_clean();
/*
			<item name="ajax_nav_all" value = "all" open="1" type="radio" d="<?php _e('Attempt to load SOME blog pages inline?','awp');?>" />
			<item name="ajax_nav_all_keywords" type="text" d="<?php _e('Only load pages inline if the urls match one of %s these keywords.','awp');?>">
				<desc><?php _e('Use a Comma seperated list of words that match part of the URLs you want to be loaded inline..','awp');?></desc>
*/
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
	function next_posts($label='Next Page &raquo;', $max_page=0) {
	global $paged, $wpdb, $wp_query,$awpall;
		if ( !$max_page ) {
			$max_page = $wp_query->max_num_pages;
		}
		if ( !$paged )
			$paged = 1;
		$nextpage = intval($paged) + 1;
		if ( (! is_single()) && (empty($paged) || $nextpage <= $max_page) ) {
			echo '<a href="';
			next_posts();
			echo '"';

			if($awpall[ajax_nav_home])
				echo ' onclick="aWP.doit('."{'type': 'nav', 'ths': this, 'nav': 'index', 'pagenum': '".($nextpage)."', 'i' : 'awp_loop', 'force' : 1 }); return false;\"";

			echo '  class="awpnav_next_posts">'. preg_replace('/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $label) .'</a>';
		}
	}

	function previous_posts($label='&laquo; Previous Page') {
	global $paged, $awpall;
		if ( (!is_single())	&& ($paged > 1) ) {
			echo '<a href="';
			previous_posts();
			echo '"';

			if($awpall[ajax_nav_home])
				echo ' onclick="aWP.doit('."{'type': 'nav', 'ths': this, 'nav': 'index', 'pagenum': '".($paged-1)."', 'i' : 'awp_loop', 'force' : 1 }); return false;\"";

			echo '  class="awpnav_previous_posts">'. preg_replace('/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $label) .'</a>';
		}
	}

	function posts_nav($sep=' &#8212; ', $prelabel='&laquo; Previous Page', $nxtlabel='Next Page &raquo;') {
	global $wp_query;
		if ( !is_singular() ) {
			$max_num_pages = $wp_query->max_num_pages;
			$paged = get_query_var('paged');

			//only have sep if there's both prev and next results
			if ($paged < 2 || $paged >= $max_num_pages) {
				$sep = '';
			}
			if ( $max_num_pages > 1 ) {
				$this->previous_posts($prelabel);
				echo preg_replace('/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $sep);
				$this->next_posts($nxtlabel);
			}
		}
	}

//
// Single Post navigation.
//

	function single_post_add_onclick(){
		global $id;
		echo $this->single_post_onclick($id);
	}

	function single_post_onclick($id){
		return " onclick=\"aWP.doit({'id': '$id', 'type': 'nav', 'ths': this, 'nav': 'single', 'i' : 'awp_loop', 'force' : 1 }); return false;\" ";
	}

	function previous_post($format='&laquo; %link', $link='%title', $in_same_cat = false, $excluded_categories = '') {
	global $awpall;
	if ( is_attachment() )
			$post = & get_post($GLOBALS['post']->post_parent);
		else
			$post = get_previous_post($in_same_cat, $excluded_categories);

		if ( !$post )
			return;

		$title = apply_filters('the_title', $post->post_title, $post);
		$string = '<a href="'.get_permalink($post->ID).'" ';

		if($awpall[ajax_nav_single])
			$string .= $this->single_post_onclick($post->ID);

		$string .= ' class="awpnav_previous_post">';
		$link = str_replace('%title', $title, $link);
		$link = $pre . $string . $link . '</a>';

		$format = str_replace('%link', $link, $format);

	echo $format;
	}


	function next_post($format='%link &raquo;', $link='%title', $in_same_cat = false, $excluded_categories = '') {
	global $awpall;
		$post = get_next_post($in_same_cat, $excluded_categories);

		if ( !$post )
			return;

		$title = apply_filters('the_title', $post->post_title, $post);
		$string = '<a href="'.get_permalink($post->ID).'" ';

		if($awpall[ajax_nav_single])
			$string .= $this->single_post_onclick($post->ID);

		$string .= ' class="awpnav_next_post">';
		$link = str_replace('%title', $title, $link);
		$link = $string . $link . '</a>';
		$format = str_replace('%link', $link, $format);

	echo $format;
	}

}
//Grandfathering it in.

	function awp_posts_nav_link($sep=' &#8212; ', $prelabel='&laquo; Previous Page', $nxtlabel='Next Page &raquo;') {
		global $AWP_ajaxnav; $AWP_ajaxnav->posts_nav($sep, $prelabel, $nxtlabel);
	}


	function awp_previous_posts_link($label='&laquo; Previous Page') {
		global $AWP_ajaxnav; $AWP_ajaxnav->previous_posts($label);
	}

	function awp_next_posts_link($label='Next Page &raquo;', $max_page=0) {
		global $AWP_ajaxnav; $AWP_ajaxnav->next_posts($label, $max_page);
	}

	function awp_previous_post_link($format='&laquo; %link', $link='%title', $in_same_cat=false, $excluded_categories = '') {
		global $AWP_ajaxnav; $AWP_ajaxnav->previous_post($format, $link, $in_same_cat, $excluded_categories);
	}

	function awp_next_post_link($format='%link &raquo;', $link='%title', $in_same_cat = false, $excluded_categories = '') {
		global $AWP_ajaxnav; $AWP_ajaxnav->next_post($format, $link, $in_same_cat, $excluded_categories);
	}

	function check_awp_previous_posts_link($label='&laquo; Previous Page') {
		global $AWP_ajaxnav; 	global $paged, $awpall;
		if ( (!is_single())	&& ($paged > 1) ) {
			return true;
		}
		return false;
	}

	function check_awp_next_posts_link($max_page=0) {
		global $paged, $wpdb, $wp_query,$awpall;
		if ( !$max_page ) {
			$max_page = $wp_query->max_num_pages;
		}
		if ( !$paged )
			$paged = 1;

		$nextpage = intval($paged) + 1;
		if ( (! is_single()) && (empty($paged) || $nextpage <= $max_page) ) {
			return true;
		}
		return false;
	}

	function check_awp_previous_post_link($format='&laquo; %link', $link='%title', $in_same_cat=false, $excluded_categories = '') {
		global $AWP_ajaxnav; 

		$post = get_previous_post($in_same_cat, $excluded_categories);

		if ( !$post )
			return false;

		return true;
	}

	function check_awp_next_post_link($format='%link &raquo;', $link='%title', $in_same_cat = false, $excluded_categories = '') {
		global $AWP_ajaxnav; 

		$post = get_next_post($in_same_cat, $excluded_categories);

		if ( !$post )
			return false;

		return true;
	}
?>
