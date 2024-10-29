<?php
	/*
	Plugin Name: AJAXed Wordpress
	Plugin URI: http://ajaxedwp.com/
	Description: A plugin that incorporates the best AJAX Wordpress features and wrap them in a single framework--including such features as inline posts and comments, the ability to paginate posts, submit comments, thread comments, edit comments and posts inline.  <strong> The Admin panel is under Appearance tab.</strong> Need Help? <a href="http://ajaxedwp.com/support">Find out about your support options</a> or try reading <a href="http://ajaxedwp.com/go/readme/">the full documentation</a>.
	Author: Aaron Harun
	Version: 1.29
	Author URI: http://anthologyoi.com/

* 	Copyrighted 2006-9 by:
*	Aaron Harun http://ajaxedwp.com
*/

	$aWP[version] = '1290';

	if($_GET['awp'] == 'test')
		$awpsuffix = '?awp=test';

	if($_GET['awp'] == 'ajax')
		define('AWP_AJAXED', true);

	if($_GET['noredirect'] == 1)// Fix problems with CURL, canonical redirects and safe mode.
		remove_filter('template_redirect', 'redirect_canonical'); 


if ( !defined('WP_CONTENT_URL') )
    define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );


	//Constants
	define('AWP_BASE',AWP::get_base());
	define('AWP_MODULES','/'.AWP_BASE.'/modules');
	define('AWP_AJAX', WP_CONTENT_URL."/plugins/".AWP_BASE."/aWP-response.php$awpsuffix");

	// Globals
	global $awpall, $awp_mods, $aWP;
	add_action('init', array('AWP','init'));

	register_activation_hook(__file__,array('AWP','maybe_set_defaults'));

class AWP{

	function start_up(){
	global $aWP;
		$options = array();

		if($_GET['awp'] == 'test'){
			$options = get_option('awp_test');
		}else{
			$options = get_option('awp');
		}

		if(!is_array($options)){
			$options = AWP::set_defaults();
			$aWP[set_defaults1] = 1;
		}

		$options = apply_filters('awp_startup',$options);

	return $options;
	}

	function init() {
		global $awpall,$aWP,$awp_mods;

 		/* Language support.*/
		$currentLocale = get_locale();

		if($awpall['awp_language'])
			$currentLocale = $awpall['awp_language'];

		if(!empty($currentLocale)) {
			$moFile = dirname(__FILE__) . "/translations/aWP-" . $currentLocale . ".mo";
			if(@file_exists($moFile) && is_readable($moFile)){
				load_textdomain('awp', $moFile);
			}
		}
		include_once(ABSPATH . PLUGINDIR . '/'.AWP_BASE  . '/control/aWP-upgrade.php');

		$awpall = AWP::start_up(); /*Get all options, load after language to set correct lanugage on defaults.*/
		$awp_mods = get_option('awp_mods'); /*Get active Modules*/


		if(!empty($awpall['awp_language'])) { /*Whoops they set a custom language, let's go and load it.*/
			$moFile = dirname(__FILE__) . "/translations/aWP-" . $awpall['awp_language'] . ".mo";
			if(@file_exists($moFile) && is_readable($moFile)){
				load_textdomain('awp', $moFile);
			}
		}

		/*Load modules. If we restore defaults, no modules are needed.*/
		if ( is_array($awp_mods) && $_POST["action"] != "restoredefaults") {
			$awp_init = array();
			foreach ($awp_mods as $mod) {
				if ('' != $mod && file_exists(ABSPATH . PLUGINDIR . AWP_MODULES. '/' . $mod))
				include_once(ABSPATH . PLUGINDIR . AWP_MODULES . '/' . $mod);
			}
			if(count($awp_init) > 0){ // loop once to set up the classes and once to initiate them.
				foreach($awp_init as $class){
					$$class = null;
					global $$class;
					$$class = new $class;
				}
				reset($awp_init);
				foreach($awp_init as $class){
					$$class->init();
				}
			}
		}

		include_once(ABSPATH . PLUGINDIR . '/'.AWP_BASE  . '/control/aWP-ajax.php');
		//include_once(ABSPATH . PLUGINDIR . '/'.AWP_BASE  . '/control/aWP-news.php');

		/*Do admin stuff.*/
		if(strpos($_GET['page'],'aWP-admin_panel.php') == true){
			
			
				add_action( 'admin_print_footer_scripts', 'codepress_footer_js' );
				wp_enqueue_script( 'codepress' );

			include_once(ABSPATH . PLUGINDIR . '/'.AWP_BASE  . '/control/aWP-admin.php');
			add_action('admin_head',array('AWP_admin','admin_js'));
			if($_REQUEST['action'])
				AWP_admin::process_admin();
		}

		add_action('admin_menu', array('AWP','menu')); // Add admin menu item.
		add_action('awp_no', array('AWP','no')); //In case AWP is disabled.
		add_action('awp_yes', array('AWP','yes'));//In case AWP is enabled.

		if(!$awpall['js_footer']){
			add_action('wp_head', array('AWP','print_header')); //um...print header?
		}else{
			add_action('wp_footer', array('AWP','print_header')); //um...print header?
		}
		if($awpall['give_credit'])
			add_action('wp_footer',array('AWP','give_credit'));// [You] are a nice person.
	}

	function give_credit(){
		if(is_home())
			echo ' <a href="http://ajaxedwp.com/" rel="external">WordPress Loves AJAX</a> ';

	}

	function get_base(){
		$base = str_replace(array('\\','/aWP.php','/awp.php'),array('/',''),__FILE__);
		$base = explode('/', $base);
		$base = end($base);
		return $base;
	}

	function print_header(){
	global $awpall,$wp_version;
		$home = get_settings('siteurl');

		do_action('awp_get_library');

		$JSscript = apply_filters('awp_jscore',WP_CONTENT_URL.'/plugins/'.AWP_BASE.'/js/core.js.php');
		$CSSscript = apply_filters('awp_csscore',WP_CONTENT_URL.'/plugins/'.AWP_BASE.'/js/core.css.php');

		wp_register_script('awp-core', $JSscript, false, $awpall[last_modified]);
		$JSscript .= '?ver='.$awpall[last_modified];
		echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $CSSscript ."\" />\n";

		echo '<script type="text/javascript"  src="' . $JSscript .'" ></script>'."\n";


		///wp_print_scripts('awp-core');
		do_action('aWP_header');
	?>
	<!--_______/___^__^____________^__^_____/______
		______/__Added by AJAXed Wordpress_/_______-->
	<?php
	}

	function enabled($mod){
	global $awpall;
		$var = 'AWP_'.$mod;
		global $$var;

		if(class_exists('AWP_'.$mod) && $awpall[$mod] == 'Enabled' && isset($$var))
			return true;

	return false;
	}

	function find_path($loc,$dir=0){

		if(strpos('/',$loc) === false){
			for($i = 0; $i < 10; $i++){
				if($dir){
					if(is_dir($ladder.$loc)){
						return $ladder;
						break;
					}
				}elseif(is_file($ladder.$loc)){
					return $ladder;
					break;
				}
				$ladder .='../';
			}
		}
	}

	function template($module='', $template=''){
	global $awpall;
	static $files;
	// This so we can save time and resources.
	// We have to check for some files dozens of time in a single page load.

		if($files[$module][$template])
			return $files[$module][$template];

		if($awpall[default_template_folder] == 'theme'){

			$file = TEMPLATEPATH . '/aWP/'.$module.'/'.$template.'.php';

			if(file_exists($file)){
				$files[$module][$template] = $file;
				return $file;
			}

		}

		if($awpall[default_template_folder] == 'plugins'){

			$file = ABSPATH.PLUGINDIR.'/aWP-templates/'.$module.'/'.$template.'.php';

			if(file_exists($file)){
				$files[$module][$template] = $file;
				return $file;
			}

		}
		$files[$module][$template] = ABSPATH.PLUGINDIR.AWP_MODULES.'/'.$module.'/'.'templates'.'/'.$template.'.php';
		//If we get to this point it needs to be returned.
		return $files[$module][$template];
	}

	function make_response($responses='', $vars='', $actions=''){

		$actions = apply_filters('awp_ajax_'.addslashes($_REQUEST['type']).'_actions',$actions);
		$responses = apply_filters('awp_ajax_'.addslashes($_REQUEST['type']).'_responses',$responses);
		$vars = apply_filters('awp_ajax_'.addslashes($_REQUEST['type']).'_vars',$vars);


		header('Content-type: text/xml; charset=' . get_option('blog_charset'), true);
		echo '<?xml version="1.0"?>'."\n";
?>
		<awp>
			<variables>
				<?php
					if(is_array($vars)){
						while (list($var, $value) = each($vars)) {
							echo '<var name="'.$var.'"><![CDATA['.$value.']]></var>'."\n";
						}
					}
				?>
			</variables>
			<responses>
				<?php
					if(is_array($responses)){
						while (list($name, $response) = each($responses)) {
							if(!is_string($name) || $name == false){
								echo '<response><![CDATA['.$response.']]></response>'."\n";
							}else{
								echo '<response name="'.$name.'"><![CDATA['.$response.']]></response>'."\n";
							}
						}
					}elseif($responses){
							echo '<response><![CDATA['.$responses.']]></response>'."\n";
					}
				?>
			</responses>
			<actions>
				<?php
					if(is_array($actions)){
						while (list($action, $value) = each($actions)) {
							if(is_int($action)){
								echo '<action><![CDATA['.$value.']]></action>'."\n";
							}else{
								echo '<action name="'.$action.'"><![CDATA['.$value.']]></action>'."\n";

							}
						}
					}
				?>
			</actions>
		</awp>
<?php

	}


	function process_text($link_text,$pid=''){
	global $id,$post;

		$link_text = stripslashes($link_text);
		if(!$pid){
			$link_text = str_replace('%count', AWP::get_count($id),$link_text);
			$link_text = str_replace('%title', get_the_title($id),$link_text);
			$link_text = str_replace('%author',get_the_author(),$link_text);
			$link_text = str_replace('%date',the_date(null,null,null,false),$link_text);
			$link_text = str_replace('%time',get_the_time(),$link_text);
			$link_text = str_replace('%categories',AWP::get_a_category_list(),$link_text);
		}else{
			$embedded_post = get_post($pid[ID]);
			$link_text = str_replace('%count', AWP::get_count($pid[ID]),$link_text);
			$link_text = str_replace('%title', get_the_title($pid[ID]),$link_text);
			$link_text = str_replace('%author',get_author_name($pid[post_author]),$link_text);
			$link_text = str_replace('%date', mysql2date(get_option('date_format'), $pid[post_date]),$link_text);
			$link_text = str_replace('%time', mysql2date('U', $pid[post_date]),$link_text);
			$link_text = str_replace('%categories','',$link_text);
		}

		if(defined('AWP_AJAXED')){ // convert entities to characters.
			$link_text = str_replace(array('&#8217;','&#8220;','&#8221;'),array('’','“','”'), $link_text);
			$link_text = @html_entity_decode($link_text,ENT_QUOTES,get_option('blog_charset'));
		}

		$link_text = apply_filters('awp_text_filters',$link_text);

	return $link_text;
	}


	function process_comment_text($link_text){
	global $id,$post;

		$link_text = stripslashes($link_text);
		$link_text = str_replace('%author',get_comment_author(),$link_text);
		$link_text = str_replace('%count',AWP::get_count($id),$link_text);
		$link_text = str_replace('%date',get_comment_date(),$link_text);
		$link_text = str_replace('%title', get_the_title($id),$link_text);
		$link_text = str_replace('%time',get_comment_time(),$link_text);
		$link_text = str_replace('%total',get_comments_number($id),$link_text);
		$link_text = str_replace('%trackbacks',get_comments_number($id,'tracks'),$link_text);

		if(defined('AWP_AJAXED')){ // convert entities to characters.
			$link_text = str_replace(array('&#8217;','&#8220;','&#8221;'),array('’','“','”'), $link_text);
			$link_text = @html_entity_decode($link_text,ENT_QUOTES,get_option('blog_charset'));
		}

		$link_text = apply_filters('awp_text_filters',$link_text);

	return $link_text;
	}


	function get_count($id){
	global $awpall;

		$count = AWP::get_true_comment_count($id);
		if($count == 0 && strlen($awpall[zero_comments]) > 0){
			return str_replace('%',$count,$awpall[zero_comments]);

		}elseif($count == 1 && $awpall[one_comment]){
			return str_replace('%',$count,$awpall[one_comment]);

		}elseif($count > 1 && $awpall[some_comments]){
			return str_replace('%',$count,$awpall[some_comments]);
		}else{
			return $count;
		}
	}

	function get_true_comment_count($id=0,$num='comments'){
		static $results = array();
		if(!$id) global $id;

		$default = get_comments_number($id);
		$actual_comments = get_post_meta($id, 'comment_count',true);
		$actual_trackbacks = get_post_meta($id, 'trackback_count',true);

		if(!is_array($results[$id])){
			if(($actual_comments + $actual_trackbacks) != $default){
				$results[$id] = AWP::true_comment_count($id);
			}else{
				$results[$id] = array( 'comments' => $actual_comments, 'trackbacks' => $actual_trackbacks);
			}
		}

		if($num == 'comments'){
			return $results[$id][comments];
		}elseif($num == 'tracks'){
			return $results[$id][trackbacks];
		}elseif($num == 'total'){
			return $results[$id][trackbacks] + $results[$id][comments];
		}else{
			return $results[$id];
		}
	}

	function true_comment_count($id){
	global $wpdb;

		delete_post_meta($id, 'comment_count');
		delete_post_meta($id, 'trackback_count');

		$counts = $wpdb->get_results("SELECT count(comment_type) as cnt, comment_type FROM   $wpdb->comments WHERE  comment_approved = '1' AND `comment_post_ID` = $id GROUP BY comment_type");

		foreach ($counts as $count){
			if ('trackback' == $count->comment_type || 'pingback' == $count->comment_type) {
					$trackbacks += $count->cnt;
			}elseif('' == $count->comment_type){
					$comments += $count->cnt;
			}
		}

		add_post_meta($id, 'comment_count', $comments);
		add_post_meta($id, 'trackback_count', $trackbacks);
		return array( 'comments' => $comments, 'trackbacks' => $trackbacks);
	}

	function link_texts($texts,$defaults='',$mode='',$pid=''){
	global $awpall;
		$links = array();

		if(is_array($texts)){
			while (list($name, $option) = each($texts)) {
				if($mode == 'comment'){
					$links[$name] = AWP::process_comment_text($option);
				}else{
					$links[$name] = AWP::process_text($option);
				}
			}
		}

		if(is_array($defaults)){
			while (list($name, $default) = each($defaults)) {
				if(!$links[$name])
					$links[$name] = $default;
			}
		}

		return $links;
	}

	function links($ops, $return=1){
		$ops[doit] = ($ops[doit]) ? 'aWP.doit({'."$ops[doit]".'}); ' :'';
		$ops[onclick] = ($ops[onclick] || $ops[doit]) ? ' onclick="'.$ops[doit].$ops[onclick].' return false;"' :'';
		$ops[rel] = ($ops[rel]) ? ' rel="'.$ops[rel].'"' : '';
		$ops[_class] = ($ops[_class]) ? ' class="'.$ops[_class].'"' : '';
		$ops[style] = ($ops[style]) ? ' style="'.$ops[style].'"' : '';

		$link = '<a href="%s" id="%s"%s%s%s%s>%s</a>';
		$link = sprintf($link, $ops[URL], $ops[id], $ops[_class], $ops[onclick], $ops[rel], $ops[style], $ops[anchor] );

		if($return){
			return $link;
		}else{
			echo $link;
		}

	}

	function maybe_JS($input){
	global $aWP;

		if(!defined('AWP_AJAXED') && !$aWP[nomaybejs]){
			//So normal users without JS won't see it. No reason to tease them.
			return '<script type="text/javascript">/*<![CDATA[*/document.write(\''.str_replace(array("'","\n"),array("\'",''),$input).'\');/*]]>*/</script>';
		}else{
			return $input;
		}

	}

	function get_a_category_list(){
		$categories = get_the_category();

		if (empty($categories))
			return __('Uncategorized');

		$separator = ',';
		$thelist = '';
			$i = 0;
			foreach ( $categories as $category ) {
				if ( 0 < $i )
					$thelist .=', ';
				$thelist .= $category->cat_name;
				++$i;
			}
	return $thelist;
	}

	function no(){
		global $aWP;
		$aWP['die'] = 1;
		do_action('awp_die');
	}

	function yes(){
		global $aWP;
		do_action('awp_live');
		$aWP['die'] = 0;
	}

	function menu() {
		add_submenu_page('themes.php', __('AJAXed WordPress'), __('AJAXed WordPress'), 8, AWP_BASE.'/control/aWP-admin_panel.php');
	}

/* 			Options Stuff.
=================================
*/

	function maybe_set_defaults(){
	global $awpall;
		AWP::start_up();
	}

	function set_defaults(){
	global $awpall, $awp_mods,$aWP;

		delete_option('awp');
		delete_option('awp_mods');

		$awpall = null;
		$awp_mods = null;

		/*Comment Form Defaults*/
		$awpall[commentform] = 'Enabled';
		$awpall[commentform_open] = __('Add a Comment','awp');
		$awpall[commentform_hide] = __('Cancel reply','awp');
		$awpall[commentform_reply_open] = __('Reply to %author','awp');
		$awpall[commentform_reply_hide] = __('Cancel reply','awp');
		$awpall[commentform_input_suffix] = '_%ID';
		$awpall[show_commentform_page] = 1;
		$awpall[show_commentform_single] = 1;

		/*Inline Comments Defaults*/
		$awpall[inlinecomments] = 'Enabled';
		$awpall[closed_comments] = __('Comments are closed','awp');
		$awpall[no_comments] = __('No Comments','awp');
		$awpall[comment_open] = __('Show Comments','awp');
		$awpall[comment_hide] = __('Hide Comments','awp');
		$awpall[show_comments_single] = 1;
		$awpall[show_comments_page] = 1;

		/*Inline Posts Defaults*/
		$awpall[inlineposts] = 'Enabled';
		$awpall[split_mode] = 'more';
		$awpall[link_show_text] = __('Click to continue reading "%title"','awp');
		$awpall[link_hide_text] = __('Hide "%title"','awp');
		$awpall[read_more] = __('Go straight to Post','awp');
		$awpall[simple_posts] = 1;

		/*Core Defaults*/
		$awpall[one_comment] = __('a comment','awp');
		$awpall[some_comments] = __('% comments','awp');
		$awpall[special_effects] = 'Enabled';
		$awpall[js_library] = 'sack';
		$awpall[do_effect] = 'ScrollLeft';
		$awpall[effects] = 'SlideUp';
		$awpall[background_color] = '#FFFFFF';
		$awpall[give_credit] = 1;
		$awpall[default_css] = <<<block

/* Comment Stuff */
.awpcomments ol.comments{
	padding-left:0;
	margin-left:0;
	list-style-type:none !important;
}

.awpcomments ol.comments * > ol.reply{
	list-style-type:none !important;
	padding-left:1.3em;
	margin-left:0;
}

.awpcomments * ol.reply{
	list-style-type:none !important;
}

.awpcomments * .authorcomment {
	border:1px solid #c0c0c0;
	line-height:1.5em;
	margin:3px;
	padding:4px;
}

.awpcomments * .commentbar {
	display:block;
	margin:0 !important;
	padding:5px 5px 10px 5px !important;
	font-weight:400;
	text-align:left;
}

.awpcomments * .commentbar cite{
	font-style:normal;
}

.comment_form input[type=text], .comment_form textarea, .input {
	font-size: 1.1em;
	padding: 3px;
	border: 2px solid #B3B3B3 !important;
}

.comment_form input[type=text]:focus, .comment_form  textarea:focus, .input:focus {
	background: #fff;
	color: #333;
	border: 2px solid #B3B3B3 !important;
}

.preview_comment{
	border: 2px solid #B3B3B3 !important;
	padding-top: 10px;
}
.comment_form input[type=text] {
	width: 45%;
	margin: 5px 5px 1px 0;
}

.comment_form textarea {
	height: 250px;
	width: 95% !important;
	margin-right:4% !important;
	font-size: 1.2em;
}

.ed_button, .submit, .comment_form input[type=submit],.comment_form input[type=button] {
	border:1px solid #B2B2B2;
	font-size:1em;
	margin-right:2px;
	padding:3px;
	width:auto;
}

.wp-smiley{
	vertical-align:middle;
	border:0;
}
.bigthrobber{
	right:30%;
	position:absolute;
	z-index:100;
}


block;

		if(!get_option('awp_firstinstall')){
			add_option('awp_firstinstall',gmdate('Y-m-d H:i:59'), 'This is the first install date of aWP, it will never change or be deleted');
		}else{
			$awpall[lastreset] = gmdate('Y-m-d H:i:59');
		}

		$awpall = apply_filters('awp_setdefaults',$awpall);
		AWP::update_options($awpall);
		add_option('awp',$awpall, 'The main option for all of aWP.');

		add_option('awp_version',$aWP[version], 'The current AWP version.');

		$awp_mods[] = 'inlinecommentform/inlinecommentform.php';
		$awp_mods[] = 'inlinecomments/inlinecomments.php';
		$awp_mods[] = 'inlineposts.php';
		add_option('awp_mods', $awp_mods, 'An array of activated modules.');

	return $awpall;
	}

	function set_options(){
		global $awpall, $aWP;
		$aWP[options] = apply_filters('awp_options',$awpall);
	}

	function rm_options(){
		delete_option('awp');
		delete_option('awp_mods');
		do_action('awp_shutdown');
	}

	function update_options($options){
	global $awpall;
		$checkboxes = array('give_credit');
		$texts = array('zero_comments','one_comment','some_comments');
		$checkboxes = apply_filters('awp_get_options', array('texts'=>$texts, 'radios'=>$radios, 'selects'=>$selects, 'checkboxes' =>$checkboxes));

		foreach($checkboxes['checkboxes'] as $name){
			if(!$options[$name]){ $options[$name] = 0; }
		}

		while (list($option, $value) = each($options)) {
			if( get_magic_quotes_gpc() ) {
				$value = stripslashes($value);
			}
			$awpall[$option] =$value;
		}

	return $awpall;
	}

	function count_numeric_items($array){
		return is_array($array) ? count(array_filter(array_keys($array), 'is_numeric')) : 0;
	}

function XML($xml){
		$xml_parser = & new AWP_XML();
		$data = $xml_parser->parse($xml);
		return $data;
	}
}
	if (!function_exists("htmlspecialchars_decode")) { /* PHP 4 support. I like this function.*/
		function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT) {
			return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
		}
	}
?>
