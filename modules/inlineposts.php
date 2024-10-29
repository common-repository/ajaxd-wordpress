<?php
	/*
	Plugin Name: Inline Posts
	Plugin URI: http://ajaxedwp.com
	Description: This module controls all aspects of inline posts and is required by modules that hook into AWP posts.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

$awp_init[] = 'AWP_inlineposts';

register_activation_hook(__file__,array('AWP_inlineposts','set_defaults'));

class AWP_inlineposts {

	function init(){
	global $awpall;
		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){

			add_action('awp_admin_posts',array(&$this,'admin'),1);
			add_filter('awp_get_options',array(&$this,'awp_get_options'));

		}elseif($awpall['inlineposts'] == 'Enabled'){
			$awped_posts = array();
			if(AWP_AJAXED !== true){
				$this->awp_live();	
			}

			add_action('awp_die',array(&$this,'awp_die'));
			add_action('awp_live',array(&$this,'awp_live'));

			/*Template Functions*/
			add_action('awp_pages',array(&$this,'pages'));
			add_action('go_to_post',array(&$this,'go_to_post'));
			add_action('awp_post',array(&$this,'awp_posts'));
			add_action('awp_title', array(&$this,'title'));

			/*Internal Functions*/
			add_action('awp_ajax_type_post', array(&$this, 'AJAX'));
			add_action('awp_filter_start', array('AWP', 'set_options'));
			add_action('awp_paginate_start', array('AWP', 'set_options'));
			add_action('awp_pages_start', array('AWP', 'set_options'));
			add_action('awp_post_start', array('AWP', 'set_options'));
			add_action('awp_paginate', array(&$this, 'paginate'));
			add_action('awp_js_start',array(&$this,'awp_js_start'));
			add_action('awp_js_toggle',array(&$this,'awp_js_toggle'));

			if($awpall['split_mode'] != 'more'){
				add_filter('awp_posts_before_pagination', array(&$this,'findtags'));
				add_filter('awp_posts_after_pagination', array(&$this,'findblocks'));
			}
		}


	}

	function awp_die(){
	global $awpall;
		if($awpall['simple_posts'] == 1){
			remove_filter('the_content', array(&$this,'filter'),-10,1);
			remove_filter('the_excerpt', array(&$this,'filter'),-10,1);
			add_filter('get_the_excerpt', 'wp_trim_excerpt');
		}
	}

	function awp_live(){
	global $awpall,$aWP;
	static $started;

		if(!$started || $aWP['die']){ /* We do not want to do this several times.*/
			if($awpall['simple_posts'] == 1){
				add_filter('the_content', array(&$this,'filter'),-10,1);
				add_filter('the_excerpt', array(&$this,'filter'),-10,1);
				remove_filter('get_the_excerpt', 'wp_trim_excerpt');
			}

		}

		$started = 1;
	}

	function awp_js_toggle(){
	global $awpall;
	if($awpall['special_effects'] != 'Enabled'){ $awpall['do_effect'] ='';}
	if($awpall['do_effect'] == 'Fade'){
			$extra = ", 'background': '$awpall[background_color]'";
	}

?>//<script>
			post: function(){
				var hideChildren;

				if(_d[i].hideChildren){
					hideChildren = 'awppost_'+_d[i].main;
				}



				AOI_eff.start('awppage_'+_p[i].prev_page+'_'+_d[i].main, {'mode': 'hide', 'eff': '<?php echo $awpall['do_effect'];?>', 'queue': ['show::'+'awppage_'+_d[i].this_page+'_'+_d[i].main]<?php echo $extra;?>, 'hideChildren': hideChildren} );

					if(_d[i].pagenum){
						/*** Add class switching.  ***/
						$('awppage_'+_d[i].this_page+'_'+_d[i].main+'_link').style.fontWeight = 'bold';

						if(_p[i].prev_page !=_d[i].this_page ){
								$('awppage_'+_p[i].prev_page+'_'+_d[i].main+'_link').style.fontWeight = 'normal';
						}

					}else{
						if(!_d[i].noChange)
							link_text('awppost_link'+'_'+_d[i].main,_p[i].show,_p[i].hide,'awppost_link','awppost_link_hide');
					}
					_p[i].prev_page = _d[i].this_page;
			},
<?php
	}


	function awp_js_start(){

?>
			post: function(postobj){

				if (_p[i].prev_page == 0 || isNaN(_p[i].prev_page)){ /*The post has never been loaded.*/

					/*If a pagenum isn't passed then there are only two pages.*/
					_d[i].this_page = (isNaN(_d[i].pagenum)) ? 2 : _d[i].pagenum;

					_p[i].prev_page = _d[i].fp;

					_d[i].force = 1;

					postobj['first_page'] = _d[i].fp;

				}else{/*post has been loaded so we are toggling.*/

					if(isNaN(_d[i].pagenum)){
						_d[i].this_page  = (_p[i].prev_page == 2) ? 1 : 2;
					}else{
						_d[i].this_page  = _d[i].pagenum;
					}
				}

				return postobj;
			},
<?php

	}
	function AJAX(){
	global $awpall, $id, $pages, $post, $aWP;

		if(!empty($post->post_password) && stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) != $post->post_password){
			die('hack');
		}

		do_action('awp_paginate');

		// we remove the filter so the function doesn't call itself
		//if($awpall['simple_posts'] == 1){
		//	remove_filter('the_content', array(&$this,'filter'),-10,1);
			add_filter('the_content',array(&$this,'break_content'),99999);
		//}

		$this->awp_die();

		$i = 1;
		if(is_array($pages)){

			
			foreach($pages as $part){

				$part = preg_replace("/(\s*)<!--title=(.*?)-->(\s*)*/",'',$part);
				$output .= '<div id="awppage_'.$i.'_'.$id.'" class="awppage" style="';
				if($_POST[first_page] != $i)
					$output .= 'display:none;';
				$output .= $style.'">';
				$output .= "\n".chr(13)."\n".$part."\n".chr(13)."\n";
				$output .= '</div>';

			$i++;
			}
		}else{

			$output .= '<div id="awppage_1_'.$id.'" class="awppage" style="'.$style.'">';
			$output .= "\n".chr(13)."\n".$pages[0]."\n".chr(13)."\n";
			$output .= '</div>';
		}
		$response[] = apply_filters('the_content',$output.'@$%@$$%##$%#$%#$');

		$actions[] = '_p[i].show = _d[i].show';
		$actions[] = '_p[i].hide = _d[i].hide';


		$links = $this->get_link_texts();
		$vars = array();

		if($post->post_excerpt == '' && !$aWP[options]['strip_excerpt']){
			$vars[no_jump] = 1;
		}

		$vars[show] = htmlspecialchars_decode($links[show], ENT_QUOTES);
		$vars[hide] = htmlspecialchars_decode($links[hide], ENT_QUOTES);

		AWP::make_response($response, $vars,$actions);

		do_action('awp_inlineposts_finished');

	}

	// this processes which options will be used from custom options and which ones are globals.
	function awp_posts(){
	global $awpall,$aWP,$id;

		do_action('awp_post_start');

		if($aWP[disable_awp][$id] == 1){

			the_content();
		}else{

			$output = $this->filter('');

			$output = apply_filters('the_content', $output.chr(13));

			if(!$aWP[options]['awp_pages'] && $aWP[disable_awp][$id] != 1)
				echo do_action('awp_pages');
		}
	}

	function filter($default_content=''){
	global $aWP,$awped_posts,$content,$aWP;
	global $id, $post, $page, $pages;

		do_action('awp_filter_start');

		if ( !empty($post->post_password) && stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) != $post->post_password ) {	// and it doesn't match the cookie

			echo get_the_password_form();

		}elseif(is_page() || $aWP[disable_awp][$id] ==1 || (is_single() && !$aWP[options]['paginate_single']) || (is_feed() && !$aWP[options]['trimfeeds']) || $aWP['die'] == 1 ){
			add_filter('get_the_excerpt', 'wp_trim_excerpt');
			if($default_content != ''){
				return $default_content;
			}else{
				return $post->post_content;
			}

		}else{
			if(is_singular())
				$page = get_query_var('page');

			if($page ==0)
				$page = 1;

			if(!is_numeric($awped_posts[$id]))
				do_action('awp_paginate');


			// Start the output.
			$output = '<div id="awppost_'.$id.'" class="awppost">'.'<div id="awppost_'.$page.'_'.$id.'" class="awppage" style="'.$style.'">'."\n".chr(13)."\n".$pages[$page-1]."\n".chr(13)."\n".'</div></div>';

			if(!$aWP[options]['awp_pages'])
				$output .= apply_filters('awp_pages','');

			return $output;
		}
	}

	function paginate(){
	global $pages,$awpall,$awped_posts,$post,$content,$id,$aWP;
	global $is_page, $is_single;

		$pages = null;

		do_action('awp_paginate_start');

		$content = $post->post_content;

		if($aWP[options]['strip_html'] == 1)
			$content = strip_tags($content);

		$content = force_balance_tags($content);

		$output = $this->create_pages();
		$count = count($output);

		$sep = ' ';

		if(($is_single && !$aWP[options]['paginate_single']) || $count== 1){

			$pages[0] = force_balance_tags(implode($sep,$output));

		}elseif($count == 2 || $aWP[options]['inlinepaginatedposts'] != 'Enabled'){
			
			if($aWP[options]['split_mode'] == 'dumbword'){
				$pages[0] = force_balance_tags($output[0].' (...)');
			}else{
				$pages[0] = force_balance_tags($output[0]);
			}

			if($aWP[options]['strip_excerpt'])
				unset($output[0]);

			$pages[1] = force_balance_tags(implode($sep,$output));

			


		}else{

			foreach($output as $part){
				$pages[] = force_balance_tags($part);
			}

		}

	return $pages;
	}

	function create_pages($split_mode=1, $word_limit=300,$para_limit = 5){
	global $pages,$awpall,$awped_posts,$post,$content,$id,$aWP;
	global $is_page, $is_single;
	$k = $y = $n = $x = 0;

		$split_mode = ($aWP[options]['split_mode'])? $aWP[options]['split_mode']: $split_mode;

		$default = $word_limit;
		if($split_mode == 'paragraph')
			$default = $para_limit;


		$split_limit = ($aWP[options]['max_words_para']) ? $aWP[options]['max_words_para']: $default;

		$split_limit2 = ($aWP[options]['paginate_max_words_para'] > 0) ? $aWP[options]['paginate_max_words_para']: $default;

		$content = apply_filters('awp_posts_before_pagination', $content);

		//Run some tests.
		if(strpos($content, '<!--nextpage-->') != false)
			$more = true;
		if(strpos($content, '<!--more-->') != false)
			$more = true;
		if(strpos($content, '<!--newpage-->') != false)
			$more = true;

		if($split_mode == 'more' || $more){

			$output = preg_split('/(\s*?)\<\!--(nextpage|newpage|more)--\>(\s*?)/', $content);

		}else{

			if($split_mode == 'paragraph'){
				$lines = preg_split('/(\n'.chr(13).'\n+)/', $content,-1, PREG_SPLIT_DELIM_CAPTURE);
				$i = count($lines);

				if($i > $split_limit){

					for($y=$n; $y < $i; $y++){
						$output[$k] .= $lines[$y];
						$y++;
						$output[$k] .= $lines[$y];
						$x++;

						if($x>=$split_limit){
						$split_limit = $split_limit2;
							$x=0;
							$k++;

						}
					}

				}else{
					$output[0]=$content;
				}

			}elseif($split_mode == 'word'){

				$sentances = preg_split("/(?<=(\.|!|\?)+)\s/", $content, -1, PREG_SPLIT_NO_EMPTY);
				$y= count($sentances);
				$min = $split_limit - .1*$split_limit;
				$max = $split_limit + .1*$split_limit;
				$half1 = $split_limit + floor($split_limit/2);

				for ($x = 0; $x < $y; $x++){
					$output[$k] .= $sentances[$x].' ';
					$words = $this->wordcount($output[$k]);

					if ( $words > $min && $words+$this->wordcount($sentances[$x+1]) > $max ){
						if($y-$x <= 2){
							if($words+$this->wordcount($sentances[$x+1].$sentances[$x+2]) > ($split_limit + $split_limit/2))
								$break = true;
						}
						if(!$break){
							if($k == 0){
								$split_limit = $split_limit2;
								$min = $split_limit - .1*$split_limit;
								$max = $split_limit + .1*$split_limit;
								$half1 = $split_limit + floor($split_limit/2);
							}
							$k++;
						}
					}
				}
			}elseif($split_mode == 'dumbword'){

				$words = explode(' ', $content);
					if (count($words) > $split_limit) {
						$i = count($words);
						for($y=$n; $y <=$i; $y++){
								$output[$k] .= ' '.$words[$y];
							$x++;
							if($x >=$split_limit && ($i-$y >= $split_limit/2 || $k == 0)){
							$split_limit = $split_limit2;
								$x=0;
								$k++;
							}
						}
					}else{
						$output[0]=$content;
					}


			}else{
				$output = apply_filters('awp_posts_pagination_'.$split_mode, $content);
			}

		}

		if(!is_array($output)){

			$output = null;
			$output[0] = $content; /* Just sync it back */

		}else{

			$output = array_values(array_diff($output, array("","\n")));

		}

		$output = apply_filters('awp_posts_after_pagination',$output);

		if(!$aWP[options]['hide_excerpt']){
			if($post->post_excerpt != '' && !is_single() && !is_page())
				array_unshift($output, $post->post_excerpt);
		}

		$awped_posts[$id] =1;

	return $output; ;
	}

//Merge from post protect tags module.

	function wordcount($str) {
 		 return count(explode(" ",$str));
	}
	function findtags($content){
	global $id, $aWP,$awpall;

		$content = preg_replace_callback('!(\<a[^>]*\>)!ims', array(&$this,'returnblocks'), $content);
		$content = preg_replace_callback('!(\<img[^>]*\>)!ims', array(&$this,'returnblocks'), $content);

		if($awpall['protecttags'] == 1){
			$content = preg_replace_callback('!(\<code\>[\S\s]*?\<\/code\>)!ims', array(&$this,'returnblocks'), $content);
			$content = preg_replace_callback('!(\<blockquote\>[\S\s]*?\<\/blockquote\>)!ims', array(&$this,'returnblocks'), $content);
			$content = preg_replace_callback('!(\<pre\>[\S\s]*?\<\/pre\>)!ims', array(&$this,'returnblocks'), $content);
		}

	return $content;
	}

	function returnblocks($blocks){
		global $id, $aWP;
		$aWP[blocks][$id][] = $blocks[1];
		return '[block]'.(count($aWP[blocks][$id])-1).'[/block]';
	}


	function findblocks($output){
	global $id, $aWP;
			if(is_array($aWP[blocks][$id])){
				for($c = 0; $c <count($output); $c++){
					$output[$c] = preg_replace_callback('!(\[block\]([0-9]*?)\[\/block\])!', array(&$this,'return_tags'), $output[$c]);
				}
			}
	return $output;
	}

	function return_tags($blocks){
		global $id, $aWP;
		return $aWP[blocks][$id][$blocks[2]];
	}



	// Ensures that things added to the end of the content are not repeated.
	function break_content($content){

		$content = explode('<p>@$%@$$%##$%#$%#$</p>',$content);
		if(count($content) <= 1)
			$content = explode('@$%@$$%##$%#$%#$',$content[0]);

		$content = apply_filters('awp_post_breakcontent_after', $content);
		return $content[0];
	}

	function get_link_texts (){
		global $aWP;

		$texts = array();
		$defaults = array();

		$texts[show] = $aWP[options]['link_show_text'];
		$texts[hide] = $aWP[options]['link_hide_text'];
		$defaults[show] = __('Click to continue reading','awp');;
		$defaults[hide] = __('Click to hide post','awp');

		return AWP::link_texts($texts,$defaults);
	}

//modified version of wp_link_pages
	function pages(){
	global $id,$awpall,$awped_posts, $page, $pages,$aWP;
	global $is_page, $is_single;
	$k = 1;

		do_action('awp_pages_start');

		if(!is_numeric($awped_posts[$id])){
			if($is_single && !$aWP[options][paginate_single]){
				$pages[0] = $content;
			}else{
				do_action('awp_paginate');
			}
		}

		$numpages = count($pages);

		if(!$aWP[options]['pagelinks']){ $aWP[options]['pagelinks'] = '%';}

		if ( $numpages >2 ) {
			$output = $aWP[options]['beforepages'];

				for ( $i = 1; $i <= $numpages; $i++ ) {

					preg_match('/(\s*?)<!--title=(.*?)-->(\s*?)/',$pages[$i-1],$matches);
					if($matches[2]){

						$j = htmlspecialchars($matches[2],ENT_QUOTES);

					}else{

						$j = str_replace('%',"$k",$aWP[options]['pagelinks']);


					}
					$style = '';

					if($k == $page){
						$style = 'font-weight:bold;';
					}

					if(!$is_single || !$is_page){
						$rel = 'nofollow';
					}

					$output .= ' '.$aWP[options]['beforepage'];

						$ops[doit] = "'id': '$id', 'type': 'post', 'pagenum': $i, 'hideChildren': '1', 'fp': '$page'" ; //fp = first_page
						$ops[rel] = $rel;
						$ops[_class] = "awppost_link";
						$ops[id] = 'awppage_'.$i.'_'.$id.'_link';
						$ops[style] = $style;
						$ops[anchor] = $j;

						if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) ){
								$ops[URL] = get_permalink() . '&amp;page=' . $i;
						}else{
								$ops[URL] = trailingslashit(get_permalink()) . $i;
						}
						
						$ops = apply_filters('awp_link_array',$ops, 'post');

						$output .= AWP::links($ops);
						$output .= $aWP[options]['afterpage'];

						if($i != $numpages)
							$output .= $aWP[options]['page_sep'];
						$k++;
				}

				$output .= $aWP[options]['afterpages'];

		}elseif($numpages == 2){

			$links = $this->get_link_texts();

			$ops[doit] = "'id': '$id', 'type': 'post', 'fp': '$page'" ;
			$ops[_class] = "awppost_link";
			$ops[id] = 'awppost_link_'.$id;
			$ops[anchor] = stripslashes($links[show]);
			$ops[URL] = get_permalink($id);

			$ops = apply_filters('awp_link_array',$ops, 'post');

			$output .= AWP::links($ops);
		}

		if($aWP[options]['go_to_post'] == 1 && $aWP[options]['read_more'] != ''){
				$output.= '<br />'.$this->go_to_post();
		}

		if(!$aWP[options]['awp_pages']){
			return $output;
		}else{
			echo $output;
		}

	}

	function go_to_post(){
	global $aWP;

		if( $aWP[options]['read_more'] != ''){
				return '<a href="'.get_permalink($id).'" class="awppost_more">'.stripslashes(AWP::process_text($aWP[options]['read_more'])).'</a>';
		}

	}

	function title(){
			global $aWP, $id, $post;
		$title = the_title('','',false);
		echo 'onclick="aWP.doit('."{'id': '$id', 'type': 'post', 'fp': '1', 'noChange': '1'}".'); return false;" id="awppost_link_'.$id.'"';
	}

	function admin(){
	global $aWP, $awpall;

	ob_start();
?><menus>
	<menu id="inlineposts">
		<title><?php _e('Inline Post Options.','awp');?></title>
		<name><?php _e('Inline Posts','awp');?></name>
		<submenu>
			<item name="simple_posts" type="checkbox" d="<?php _e('Use Simple Posts','awp');?>" important='2'>
				<desc><![CDATA[<?php _e('Simple Posts" allows you to continue using the_content and the_excerpt in your theme file, but the plugin will work as if you edited your theme files. This will also filter anything that applies the content and excerpt filters. This <em>may</em> cause undesirable effects with certain plugins; if you have any problems with it, you may want to edit your files as described in the instructions.','awp');?>]]></desc>
			</item>
		</submenu>

		<submenu custom="1">
			<desc><?php _e('The following texts are the default texts displayed when a post has more than one page. You can use the tags %title, %author, %date, %categories, %count (comment count) and %time to show their respective data in the following textboxes.','awp');?></desc>

			<item open='1' important='2' name="link_show_text" type="text" d='<?php _e('Show text (for posts): %s','awp');?>'/>
				<item name="link_hide_text" type="text" d="<?php _e('Hide text (for posts): %s','awp');?>" />

			<item open="1" name="go_to_post" type="checkbox" d='<?php _e('Automatically use "go straight to post" link?','awp');?>' />
				<item name="read_more" type="text" d="<?php _e('Go straight to post text: %s','awp');?>" />
		</submenu>

		<submenu>
				<desc><?php _e('Use the following method to create excerpts or pages.','awp');?></desc>

				<item open="1" name="split_mode" type="radio" value="more" d='<?php _e('Only on more or nextpage tags.','awp');?>'>
					<desc><![CDATA[<?php _e('This option will only split a post when you specifically set a &lt;!--more--&gt; or &lt;!--nextpage--&gt; tag.','awp');?>]]></desc>
				</item>
				<item open="1" name="split_mode" type="radio" value="paragraph" d='<?php _e('By paragraphs (unless more tag).','awp');?>'>
					<desc><![CDATA[<?php _e('This is a dumb method, so its use is not recommended because it does not differentiate between a 10 word paragraph and a 400 word one. By default a more tag will always overrule this option.','awp');?>]]></desc>
				</item>
				<item open="1" name="split_mode" type="radio" value="word" d='<?php _e('By word count (with full sentences).','awp');?>'>
					<desc><![CDATA[<?php _e('This is you best option to have a uniform look and feel while keep posts attractive and easy to read for readers. This will create use your word count as a guide and fit in as many complete sentences as possible. By default a more tag will always overrule this option.','awp');?>]]></desc>
				</item>
				<item open="1" name="split_mode" type="radio" value="dumbword" d='<?php _e('By word count (exact word count).','awp');?>'>
					<desc><![CDATA[<?php _e('This is a "dumb" method. It chops off pages at exactly the number of words specified. By default a more tag will always overrule this option','awp');?>]]></desc>
				</item>
				<item name="max_words_para" type="text" d='<?php _e('Show %s words or paragraphs.','awp');?>' size="4">
					<desc><?php _e('Whether this is used to determine paragraphs or words will depend on your split mode, so if you change your split mode do not forget to change this also.','awp');?></desc>
				</item>
			<item name="strip_excerpt" type="checkbox" d='<?php _e('Strip excerpt?','awp');?>'>
				<desc><![CDATA[<?php _e('By default after clicking to see the rest of a post, the excerpt will be re-shown. This option will trim the excerpt from the post.','awp');?>]]></desc>
			</item>
			<item name="hide_excerpt" type="checkbox" d='<?php _e('Hide default excerpt?','awp');?>'>
				<desc><![CDATA[<?php _e('Normally, AWP automatically uses a explicitly set excerpt, if it exists; selecting this option disables this behavior.','awp');?>]]></desc>
			</item>
			<item name="strip_html" type="checkbox" d='<?php _e('Strip HTML in preview?','awp');?>'>
				<desc><![CDATA[<?php _e('If HTML is stripped, links, text decoration, images and all other HTML will be removed.','awp');?>]]></desc>
			</item>
			<item type="checkbox" name="postprotecttags">
				<d><?php _e('Treat code, bloquotes and pre blocks as single words?','awp');?></d>
   		 		<desc><?php _e('When posts are split based on paragraphs or words, this option will protect blockquotes, code and pre blocks from being included in the word count. This may mean that a long code segment etc may go over the specified length; however, this is probably preferable to users only getting a partial segment.','awp');?></desc>
			</item>
			<action>awp_admin_post_options</action>
		</submenu>
		<submenu>
			<item name="awp_pages" type="checkbox" d='<?php _e('Use awp_pages function for post show/hide links?','awp');?>'>
				<desc><![CDATA[<?php _e("If this option is checked no link will be shown unless you add do_action('awp_pages'); or awp_title() inside your theme. Not recommended for beginners.",'awp');?>]]></desc>
			</item>
		</submenu>
	</menu>

	<menu id="inlinepaginatedposts">
		<name><?php _e('Inline Paginated Posts','awp');?></name>
		<title><?php _e('Inline Post Pagination Options.','awp');?></title>
		<desc><?php _e('By default AWP only creates two pages, a short preview and everything else; however, by enabling the Post Pagination you will be able to create additional pages to further split the post. This is in addition to the Inline Post Module: not a replacement for it.','awp');?></desc>
		<submenu custom="1">
			<item name="paginate_max_words_para" type="text" size="4" d='<?php _e('Each page after the excerpt should be %s words or paragraphs (based on the split mode) long.','awp');?>'/>
		</submenu>
		<submenu custom="1">
		<desc><?php _e('The following options will style your page links (if you have any). Each may contain XHTML','awp');?></desc>
			<item important='5' open="1" >
				<intro><![CDATA[<?php _e('Combined the example options with CSS styling yield: <br /><ul class="examplemenu" style="display:inline !important;"> <li><a>Page 1 </a></li><li><a>Page 2 </a></li><li><a>Page 3 </a></li></ul>','awp');?>]]></intro>
			</item>
				<item nobreak="1" type="text" size="4" name="beforepages" d='<?php _e(' Before page list.','awp');?>'>
					<desc><![CDATA[<?php _e('(&lt;ul&gt;)','awp');?>]]></desc>
				</item>
					<item open="1" type="text" size="4" name="afterpages" d='<?php _e(' After page list.','awp');?>'>
						<desc><![CDATA[<?php _e('&lt;li&gt;','awp');?>]]></desc>
					</item>
					<item nobreak="1" type="text" size="4" name="beforepage" d='<?php _e(' Before individual page links.','awp');?>'>
						<desc><![CDATA[<?php _e('(&lt;li&gt;)','awp');?>]]></desc>
					</item>
						<item open="1" type="text" size="4" name="afterpage" d='<?php _e(' After individual page links.','awp');?>'>
							<desc><![CDATA[<?php _e('(&lt;li&gt;)','awp');?>]]></desc>
						</item>
					<item nobreak="1" type="text" size="4" name="pagelinks" d='<?php _e('The page links','awp');?>'>
						<desc><![CDATA[<?php _e('A % sign will be replaced with the page number (Page % )','awp');?>]]></desc>
					</item>
						<item type="text" size="4" name="page_sep" d='<?php _e('Separator between individual page links','awp');?>' />
			<item name="paginate_single" type="checkbox" d='<?php _e('Paginate single page posts also?','awp');?>'/>
		</submenu>
	</menu>
</menus>
<?php
	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);
	}

	function awp_get_options($i){
		$i[selects][] = 'inlinepaginatedposts';
		$i[selects][] = 'inlineposts';
		$i[texts][] = 'read_more';
		$i[texts][] = 'link_show_text';
		$i[texts][] = 'link_hide_text';
		$i[texts][] = 'beforepages';
		$i[texts][] = 'pagelinks';
		$i[texts][] = 'afterpages';
		$i[texts][] = 'beforepage';
		$i[texts][] = 'afterpage';
		$i[texts][] = 'page_sep';
		$i[checkboxes][] = 'paginate_single';
		$i[checkboxes][] = 'strip_excerpt';
		$i[checkboxes][] = 'strip_html';
		$i[checkboxes][] = 'simple_posts';
		$i[checkboxes][] = 'awp_pages';
		$i[checkboxes][] = 'hide_excerpt';
		$i[checkboxes][] = 'go_to_post';
		 $i[checkboxes][] = 'protecttags';
		$i[radios][] = array('split_mode','more');
		return $i;
	}

	function set_defaults(){
		global $awpall;

		$awpall[inlineposts] = 'Enabled';
		$awpall[split_mode] = 'more';
		$awpall[link_show_text] = __('Click to continue reading "%title"','awp');
		$awpall[link_hide_text] = __('Hide "%title"','awp');
		$awpall[simple_posts] = 1;
		$awpall[page_sep] = ' | ';

		update_option('awp',$awpall);
	}

}

?>