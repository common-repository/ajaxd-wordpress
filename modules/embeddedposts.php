<?php
	/*
	Plugin Name: Embedded Posts
	Plugin URI: http://ajaxedwp.com
	Description: Adds the ability to embed posts inside other posts and then load them inline.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/
	if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
			add_action('awp_admin_posts',array('AWP_EmbeddedPost','admin'));
			add_filter('awp_get_options',array('AWP_EmbeddedPost','awp_get_options'));
		}elseif($awpall['embeddedposts'] == 'Enabled'){
			add_filter('the_content',array('AWP_EmbeddedPost','list_pages'));
			add_filter('the_excerpt',array('AWP_EmbeddedPost','list_pages'));
			add_filter('the_content',array('AWP_EmbeddedPost','embedded_post_tag'));
			add_filter('the_excerpt',array('AWP_EmbeddedPost','embedded_post_tag'));
			add_action('awp_ajax_type_embed', array('AWP_EmbeddedPost', 'AJAX'));
		}

register_activation_hook(__file__,array('AWP_EmbeddedPost','set_defaults'));
//register_deactivation_hook(__file__,array('AWP_EmbeddedPost','rm_options'));

class AWP_EmbeddedPost {

	function AJAX(){
	global $awpall, $id, $pages, $post,$AWP_inlineposts;

		// we remove the filter so the function doesn't call itself
		if($awpall['simple_posts'] == 1){
			remove_filter('the_content', array($AWP_inlineposts,'filter'),-10,1);
			add_filter('the_content',array($AWP_inlineposts,'break_content'),99999);
		}

		$output = "\n".chr(13)."\n".$post->post_content."\n".chr(13)."\n";

		$response[] = apply_filters('the_content',$output.'@$%@$$%##$%#$%#$');

		$actions[] = '_p[i].show = _d[i].show';
		$actions[] = '_p[i].hide = _d[i].hide';

		$links = AWP_EmbeddedPost::get_link_texts();
		$vars = array();
		$vars[show] =$links[show];
		$vars[hide] = $links[hide];

		AWP::make_response($response, $vars,$actions);
	}

	function list_pages($content){
		return preg_replace_callback('!\<\!\-\-mychildren\-\-\>!ims', array('AWP_EmbeddedPost','embed_pages'), $content);
	}

	function embed_pages(){
		global $id;
	//wp_list_pages('sort_column=post_title&child_of='.$id.'&depth=1&title_li=');

	$r = array('depth' => 0, 'show_date' => '', 'date_format' => get_option('date_format'),
		'child_of' => $id, 'exclude' => '', 'title_li' =>'', 'echo' => 1, 'authors' => '', 'sort_column' => 'menu_order, post_title');

	// Query pages.
	$pages = get_pages($r);

		foreach($pages as $page){
			if($page->post_status != 'draft' && $page->post_status !='future'){
			$output .= "\n".'<h3>'.apply_filters('the_title', $page->post_title).'</h3>'."\n";
			$output .= AWP_EmbeddedPost::embedded_post($page->ID);}
		}
		return $output;
	}

	function embedded_post_tag($content){
		return preg_replace_callback('!\<\!\-\-embed=([0-9]*)\-\-\>!ims', array('AWP_EmbeddedPost','embedded_post'), $content);
	}

	function embedded_post($pid){
		global $awpall,$awped_posts,$wpdb,$id,$awp_options;
		if(!is_array($pid)){
			$return = 1;
			$pid = $pid;
		}elseif(is_array($pid)){
			$return = 1;
			$pid = $pid[1];
		}
		if($id != $pid){

			$good_post = $wpdb->get_row("SELECT ID, post_author, post_date FROM $wpdb->posts WHERE post_status = 'publish' && ID = '$pid'",ARRAY_A);

			if(is_array($good_post)){
				$pid = $good_post;
				$links = AWP_EmbeddedPost::get_link_texts($pid);

				$ops[doit] = "'id': '$pid[ID]', 'type': 'embed'" ;
				$ops[_class] = "awppost_link";
				$ops[id] = 'awpembed_link_'.$pid[ID];
				$ops[anchor] = stripslashes($links[show]);
				$ops[URL] = get_permalink($pid[ID]);

				$ops = apply_filters('awp_link_array',$ops, 'post');

				$add = 	'<div id="awpembed_'.$pid[ID].'" class="post_content" style="display:none;"></div>'.AWP::links($ops);

				if($return){
					return $add;
				}else{
					echo $add;
				}
			}
		}

	}

	function get_link_texts ($pid=0){
		global $aWP, $id;
		if($aWP[options]['link_embed_show_text']){
			$link_show_text=$aWP[options]['link_embed_show_text'];
		}

		if($aWP[options]['link_embed_hide_text']){
			$link_hide_text=$aWP[options]['link_embed_hide_text'];
		}

		if(!$link_show_text){
			$link_show_text=__('Click to continue reading','awp');
		}

		if(!$link_hide_text){
			$link_hide_text=__('Click to hide post','awp');
		}
		$links = array();
		$link_show_text= str_replace("&#039;",'&#8217;',$link_show_text);
		$link_hide_text= str_replace("&#039;",'&#8217;',$link_hide_text);
		$links[show] =  AWP::process_text($link_show_text,$pid);
		$links[hide] =  AWP::process_text($link_hide_text,$pid);

		return $links;
	}

	function admin(){
	global $awpall, $aWP;
	ob_start();
?>
			<menu id="embeddedposts">
				<title><?php _e('Embedded Posts Options','awp');?></title>
				<name><?php _e('Embedded Posts','awp');?></name>
				<submenu>
					<item type="text" important="2" open="1" d='<?php _e('Show text (for embedded posts): %s','awp');?>' name="link_embed_show_text"/>
					<item type="text" name="link_embed_hide_text" d='<?php _e('Hide text (for embedded posts): %s','awp');?>'/>
				</submenu>
				</menu>

<?php


	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);
	}

	function set_defaults(){
	global $awpall;
		$awpall[embeddedposts] = 'Enabled';
		$awpall[link_embed_show_text] = 'Load "%title"';
		$awpall[link_embed_hide_text] = 'Hide "%title"';

		update_option('awp',$awpall);
	}

	function rm_defaults(){
	global $awpall;
		unset($awpall[link_embed_show_text]);
		unset($awpall[link_embed_hide_text]);

		update_option('awp',$awpall);
	}

	function awp_get_options($i){
		$i[texts][] = 'link_embed_hide_text';
		$i[texts][] = 'link_embed_show_text';
		$i[selects][] = 'embeddedposts';
		return $i;
	}
}
?>