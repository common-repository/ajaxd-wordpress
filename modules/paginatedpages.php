<?php
	/*
	Plugin Name: Paginated Pages
	Plugin URI: http://ajaxedwp.com
	Description: Permits Persons to Purposefully Paginate Pages. -- This plugin requires that Inline Posts be activated and enabled. (However, you may deselect the "use simple posts option" to avoid having posts loaded inline.)
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/
	if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
			add_action('awp_admin_posts',array('AWP_PaginatedPages','admin'));
			add_filter('awp_get_options',array('AWP_PaginatedPages','awp_get_options'));
		}elseif($awpall['paginatedpages'] == 'Enabled' && $awpall['inlineposts'] == 'Enabled'){

				//If inline posts rejects the post and moves on or never loads it, we will force it to work on the page.

				add_filter('the_content',array('AWP_PaginatedPages','filter'), -9,1);
				add_filter('the_excerpt',array('AWP_PaginatedPages','filter'), -9,1);
				add_action('awp_ajax_type_post', array('AWP_PaginatedPages', 'maybeeditoptions'),-10);
		}

register_activation_hook(__file__,array('AWP_PaginatedPages','set_defaults'));
//register_deactivation_hook(__file__,array('AWP_PaginatedPages','rm_options'));

class AWP_PaginatedPages {

	function filter($default_content){
	global $awpall, $id, $pages, $post,$page, $aWP;
		if(is_page() && (empty($post->post_password) || stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) == $post->post_password) && ( strpos($post->post_content, '<!--nextpage-->') != false || strpos($post->post_content, '<!--newpage-->') != false)){

			add_filter('awp_options', array('AWP_PaginatedPages', 'get_custom'),99);

			if(!is_numeric($awped_posts[$id]))
				do_action('awp_paginate');

			if(is_singular())
				$page = get_query_var('page');

			if($page==0)
				$page = 1;

			// Start the output.
			$output = '<div id="awppost_'.$id.'" class="awppost">'.'<div id="awppost_'.$page.'_'.$id.'" class="awppage" style="'.$style.'">'."\n".chr(13)."\n".$pages[$page-1]."\n".chr(13)."\n".'</div></div>';
			if(!$aWP[options]['awp_pages'])
				$output .= apply_filters('awp_pages','');

			$aWP[options]['inlinepaginatedposts'] = 'Disabled';

			return $output;

		}else{
			return $default_content;
		}
	}


	function get_custom($awpall){
		global $id, $aWP;
		$awpall['inlinepaginatedposts'] = 'Enabled';
		$awpall['strip_excerpt'] = 0;
		$awpall['beforepages']=$awpall['pp_beforepages'];
		$awpall['pagelinks']=$awpall['pp_pagelinks'];
		$awpall['afterpages']=$awpall['pp_afterpages'];
		$awpall['beforepage']=$awpall['pp_beforepage'];
		$awpall['afterpage']=$awpall['pp_afterpage'];
		$awpall['page_sep']=$awpall['pp_page_sep'];
		return $awpall;
	}

	function maybeeditoptions(){
	global $awpall, $id, $pages, $post, $aWP;
		if($post->post_type == 'page' && (empty($post->post_password) || stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) != $post->post_password)&& ( strpos($post->post_content, '<!--nextpage-->') != false || strpos($post->post_content, '<!--newpage-->') != false)){
			add_filter('awp_options', array('AWP_PaginatedPages', 'get_custom'),99);
		}

	}

	function admin(){
	global $awpall, $aWP;
	ob_start();
?>
	<menu id="paginatedpages">
		<title><?php _e('Paginated Pages Options','awp');?></title>
		<name><?php _e('Paginated Pages','awp');?></name>
		<submenu custom="1">
			<desc><?php _e('The following options will style your default page links (if you have any). Each may contain XHTML','awp');?></desc>
				<item important='5' open="1" >
					<intro><![CDATA[<?php _e('Combined the example options with CSS styling yield: <br /><ul class="examplemenu" style="display:inline !important;"> <li><a>Page 1 </a></li><li><a>Page 2 </a></li><li><a>Page 3 </a></li></ul>','awp');?>]]></intro>
				</item>
					<item nobreak="1" type="text" size="4" name="pp_beforepages" d='<?php _e(' Before page list.','awp');?>'>
						<desc><![CDATA[<?php _e('(&lt;ul&gt;)','awp');?>]]></desc>
					</item>
						<item open="1" type="text" size="4" name="pp_afterpages" d='<?php _e(' After page list.','awp');?>'>
							<desc><![CDATA[<?php _e('&lt;li&gt;','awp');?>]]></desc>
						</item>
						<item nobreak="1" type="text" size="4" name="pp_beforepage" d='<?php _e(' Before individual page links.','awp');?>'>
							<desc><![CDATA[<?php _e('(&lt;li&gt;)','awp');?>]]></desc>
						</item>
							<item open="1" type="text" size="4" name="pp_afterpage" d='<?php _e(' After individual page links.','awp');?>'>
								<desc><![CDATA[<?php _e('(&lt;li&gt;)','awp');?>]]></desc>
							</item>
						<item nobreak="1" type="text" size="4" name="pp_pagelinks" d='<?php _e('The page links','awp');?>'>
							<desc><![CDATA[<?php _e('A % sign will be replaced with the page number (Page % )','awp');?>]]></desc>
						</item>
						<item type="text" size="4" name="pp_page_sep" d='<?php _e('Separator between individual page links','awp');?>' />
			</submenu>
		</menu>

<?php


	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);
	}

	function set_defaults(){
	global $awpall;
		update_option('awp',$awpall);
	}

	function rm_defaults(){
	global $awpall;

		update_option('awp',$awpall);
	}

	function awp_get_options($i){
		$i[texts][] = 'pp_beforepages';
		$i[texts][] = 'pp_pagelinks';
		$i[texts][] = 'pp_afterpages';
		$i[texts][] = 'pp_beforepage';
		$i[texts][] = 'pp_afterpage';
		$i[texts][] = 'pp_page_sep';
		$i[selects][] = 'paginatedpages';
		return $i;
	}
}
?>