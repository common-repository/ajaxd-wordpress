<?php
	/*
	Plugin Name: Inline Comments
	Plugin URI: http://ajaxedwp.com
	Description: This module controls all aspects of inline comments and is required by modules that hook into AWP comments.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

$awp_init[] = 'AWP_inlinecomments';
register_activation_hook(__file__,array(&$this,'set_defaults'));

class AWP_inlinecomments{

	function init(){
	if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
		add_action('awp_admin_comments',array(&$this,'admin'));
		add_filter('awp_get_options',array(&$this,'awp_get_options'));
	}elseif(AWP::enabled('inlinecomments')){
		$awp_link_count = array();
		$this->awp_live();
		add_action('awp_die',array(&$this,'awp_die'));
		add_action('awp_live',array(&$this,'awp_live'));

		/*Template Functions*/
		add_action('awp_comments', array(&$this,'div'));
		add_action('awp_comments_link', array(&$this,'links'));

		/*Internal Functions*/
		add_action('awp_comments_do', array(&$this,'comments'));
		add_action('awp_ajax_type_comments', array(&$this, 'AJAX'));
		add_action('awp_js_start',array(&$this,'awp_js_start'));
		add_action('awp_print_comments',array(&$this,'the_comments'));
	}

	$this->default_options();


	}

	function awp_die(){
	global $awpall;
			if($awpall['simple_comments'] == 1){
				remove_filter('comments_template', array(&$this,'filter'),1);
			}
	}

	function awp_live(){
	global $awpall,$aWP;
	static $started;

		if(!$started || $aWP['die']){ /* We do not want to do this several times.*/
			if($awpall['simple_comments'] == 1){
				add_filter('comments_template', array(&$this,'filter'),1);
			}
		}
		$started = 1;
	}

	function AJAX(){
	global $awpall;
		$awpall['split_comments'] = $awpall['split_inline_comments'];
		ob_start();
			do_action('awp_comments_do');
		$response = ob_get_contents();
		ob_end_clean();

		$actions[] = '_p[i].show = _d[i].show';
		$actions[] = '_p[i].hide = _d[i].hide';

		$vars = $this->get_link_texts();

		AWP::make_response($response, $vars,$actions);

	}

	function awp_js_start(){

?>
			comments: function(postobj){

				if(_d[i].show){
					_p[i].show = _d[i].show;
				}

				if(_d[i].hide){
					_p[i].hide = _d[i].hide;
				}

				return postobj;
			},
<?php

	}

	function div(){
	global $id, $post,$awpall,$comments;
			$type = 'total';
			if($awpall[split_comments] == 3)
				$type = 'comments';

		if (AWP::get_true_comment_count($id, $type) || comments_open()){
			if ( ((is_single() && $awpall['show_comments_single'] == 1) || (is_page() && $awpall['show_comments_page'] == 1 ) || (is_home() && $awpall['show_comments_home'] == 1 )) && (AWP::get_true_comment_count($id, $type) > 0 || $awpall['comment_template'] == 2)){
				echo "\n".'<div id="awpcomments_'.$id.'" class="awpcomments">';
					do_action('awp_comments_do');
				echo '</div>';
			}else{
				echo "\n".'<div id="awpcomments_'.$id.'" class="awpcomments" style="display:none;"></div>';
			}
		}
	}

	function get_link_texts(){
		global $awpall;
		$texts = array();
		$defaults = array();

		$texts[show] = $awpall['comment_open'];
		$texts[hide] = $awpall['comment_hide'];
		$defaults[show] = __('Show Posts Comments','awp');;
		$defaults[hide] = __('Hide Post Comments','awp');

		return AWP::link_texts($texts,$defaults,'comment');
	}

	function links($return=false){
		global $id,$post,$awpall;

		$links = $this->get_link_texts();

		$comment_count = AWP::get_true_comment_count();

		$show = $links[show];
		// this is the most cheating way to do it, but it makes it Soooo easy.
		if ( (is_single() && $awpall['show_comments_single']) || (is_page() && $awpall['show_comments_page']) || (is_home() && $awpall['show_comments_home'])){
			$show = $links[hide];
			$ops[doit] = "'show':'".js_escape($links[show])."', 'hide':'".js_escape($links[hide])."',";
		}

			$ops[doit] .= "'id': '$id', 'type': 'comments'" ;
			$ops[_class] = "comments_link";
			$ops[id] = 'awpcomments_link_'.$id;
			$ops[anchor] = $show;
			$ops[URL] = get_permalink($id).'#comments';
			$ops[rel] = 'nofollow';
			
			$ops = apply_filters('awp_link_array',$ops, 'comment');
		$link = '';
		if ($comment_count == 0 && comments_open() && $awpall['comment_template'] != 2){

			$link .= "\n".'<span id="awpcomments_none_'.$id.'" class="comments_link">';
			$link .= ($awpall['no_comments'] == '')? __('No Comments','awp') : AWP::process_comment_text($awpall['no_comments']);
			$link .= '</span> ';

			$ops = apply_filters('awp_link_array',$ops, 'comment');

			$ops[style] = "display:none;";
			$ops[anchor] = $links[hide];

			$link .= "\n".AWP::links($ops);

		}elseif($comment_count == 0 && !comments_open()){
			$link .= ($awpall['closed_comments'] == '')? __('Comments are closed','awp') : AWP::process_comment_text($awpall['closed_comments']);
		}else{

			$ops = apply_filters('awp_link_array',$ops, 'comment');

			$link .= "\n".AWP::links($ops);
		}

		if($return){
			return $link;
		}else{
			echo $link;
		}
	}

	function filter($i){
	global $id, $post,$awpall,$aWP;
		$aWP[simplecomments] = 1;
		return(AWP::template('inlinecomments','simplecomments'));
	}

	function comments(){
	global $wpdb, $awpall,$id,$comments,$post,$comment,$user_ID,$user_identity;
		if (!empty($post->post_password)) { // if there's a password
			if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie

				echo '<p class="nocomments">'.__('This post is password protected. Enter the password to view comments.','awp').'</p>';

				return;
			}
		}

		if ( file_exists( TEMPLATEPATH . '/comments.php') && $awpall['comment_template'] == 2){

			$comments = $this->get_comments();
			include(TEMPLATEPATH . '/comments.php');

		}elseif ( file_exists( TEMPLATEPATH . '/comments.php') && $awpall['comment_template'] == 4){

			$comments = $this->get_comments();
			ob_start();
				include(TEMPLATEPATH . '/comments.php');
				$response = ob_get_contents();
			ob_end_clean();
				if(strpos($response,'<!--awp_comments--><') && strpos($response,'><!--awp_comments-->')){
					preg_match('@((\<\!--awp_comments[^>]*--\>\<[^>]*\>)([\S\s]*)(\<\/[^>]*\>\<\!--awp_comments[^>]*--\>))@',$response,$children);
				}else{
					preg_match('@((\<ol[^>]*\>)([\S\s]*)(\<\/ol\>))@',$response,$children);
				}
			echo $children[1];
		}elseif ( file_exists( TEMPLATEPATH . '/awp-comments.php') && $awpall['comment_template'] == 3){

			$comments = $this->get_comments();
			include(TEMPLATEPATH . '/awp-comments.php');

		}else{

			$this->print_comments();

		}
	}

	/* Modified comments_template() */
	function get_comments(){
	global $wp_query, $withcomments, $post, $wpdb, $id, $comment, $user_login, $user_ID, $user_identity, $awpall,$aWP;
		if(!$id){
			$id=$post->ID;
		}

		$req = get_option('require_name_email');
		$commenter = wp_get_current_commenter();
		extract($commenter, EXTR_SKIP);

		$order = 'ASC';
		if($awpall[comment_order] == 1){ $order = 'DESC';}

		$where = apply_filters('awp_comments_where','');

		if ( $user_ID && !$aWP['basic_comments']) {

			$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$id' AND (comment_approved = '1' OR ( user_id = '$user_ID' AND comment_approved = '0' ) ) $where  ORDER BY comment_date $order");

		} else if ( empty($comment_author) || $aWP['basic_comments']) {

			$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$id' AND comment_approved = '1' $where ORDER BY comment_date $order");

		} else {

			$author_db = $wpdb->escape($comment_author);
			$email_db  = $wpdb->escape($comment_author_email);
			$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$id' AND ( comment_approved = '1' OR ( comment_author = '$author_db' AND comment_author_email = '$email_db' AND comment_approved = '0' ) ) $where ORDER BY comment_date $order");

		}

		// keep $comments for legacy's sake (remember $table*? ;) )
		$comments = $wp_query->comments = apply_filters( 'comments_array', $comments, $post->ID );
		$wp_query->comment_count = count($wp_query->comments);

		if(function_exists('update_comment_cache')){
			update_comment_cache($comments);
		}

	return $comments;

	}

	function print_comments(){
		do_action('awp_print_comments'); //By default we use internal methods, but we want it to be pluggable.
	}

	function the_comments(){
	global $awpall,$comment, $comments, $post,$id, $aWP;
		$comments = $this->get_comments();
		include(AWP::template('inlinecomments','comments'));
	}

	function admin(){
	global $aWP, $awpall;


	ob_start();
?>

<menus>
 	<menu id="inlinecomments">
		<name><?php _e('Inline Comments','awp');?></name>
		<title><?php _e('Inline Comments Module.','awp');?></title>
		<submenu>
			<item important="2" type="checkbox" name="simple_comments">
				<d><?php _e('Use Simple Comments?','awp');?></d>
				<desc><?php _e('"Simple Comments" allows you to continue using comments_template in your theme files, and will automatically replace your default template with AWP comments and the AWP comment form.','awp');?></desc>
			</item>
		</submenu>

		<submenu>
			<desc><?php _e('The following texts are for comments. You can use the tags %title, %author, %date, %count, %trackbacks, %time, and %total to show their respective data in the following textboxes.','awp');?></desc>

			<item important="2" type="text" open="1" d='<?php _e('Show text: %s','awp');?>' name="comment_open"/>
				<item type="text" d='<?php _e('Hide text: %s','awp');?>' name="comment_hide"/>

			<item important="2" type="text" open="1" d='<?php _e('No Comments Text: %s','awp');?>' name="no_comments"/>
				<item type="text" d='<?php _e('Comments closed text: %s','awp');?>' name="closed_comments"/>

		</submenu>

		<submenu>
			<desc><?php _e('On pages and single posts you may have the comments open by default. This will allow the user to refresh the comments and post using AJAX, but allows search engines and users without Javascript to see the comments by default.','awp');?></desc>

			<item important="2" type="checkbox" open="1" name="show_comments_single">
				<d><?php _e('Have Comments open by default on single post pages?','awp');?></d>
			</item>

			<item type="checkbox" open="1" name="show_comments_page">
				<d><?php _e('Have Comments open by default on pages?','awp');?></d>
			</item>

			<item type="checkbox" name="show_comments_home">
				<d><?php _e('Have Comments open by default on the index page?','awp');?></d>
			</item>

		</submenu>

		<submenu>
			<desc><?php _e('Comments work best when used with AWP\'s default template; however it can be changed with some restrictions: if you set it to use the theme\'s default comment template then the add comment box will be disabled and the show/hide comments link will work for both add comments and comments, and you will no longer be able to use AJAX comments.','awp');?></desc>

				<item type="radio" open="1" value="1" name="comment_template">
					<d><?php _e('Use the default template for AWP that is built into the plugin.','awp');?></d>
					<desc><?php _e('(This can be customized below.)','awp');?></desc>
				</item>

				<item type="radio" open="1" value ="4" name="comment_template">
					<d><?php _e('Attempt to modify your theme\'s default comment template.','awp');?></d>
					<desc><?php _e('If you use this method, your theme must use a standard template that is based on &amp;lt;ol> tags or you must edit it for compatibility.','awp');?> <?php _e('To make a file not based on an &amp;lt;ol> list compatible, you must add &amp;lt;!--awp_comments--> directly before the tag that starts your comment list and directly after the tag that ends it. There must be no characters or blank space between the two.','awp');?> <?php _e('Certain features may not be available.','awp');?></desc>
				</item>

				<item type="radio" open="1" value ="2" name="comment_template">
					<d><?php _e('Use your theme\'s default comment template.','awp');?></d>
					<desc><?php _e('Template must be named comments.php and be a standard template.','awp');?> <?php _e('Certain features may not be available.','awp');?></desc>
				</item>

				<item type="radio" value ="3" name="comment_template">
					<d><?php _e('Use a custom awp comment template for your theme.','awp');?></d>
					<desc><?php _e('This should be named awp-comments.php and must reside inside your default theme folder. This is separate from the overall option to move templates into your theme folder. This option is for when you want to basically use the default comments.php file with just a couple tweaks.','awp');?></desc>
				</item>
			<item type="checkbox"  name="comment_order" d="<?php _e('Show newest comments first?', 'awp'); ?>"/>
		</submenu>

		<submenu>

			<desc><?php _e('Separate Pingbacks/trackbacks from normal comments (ones loaded with the rest of the page):','awp');?></desc>

			<item type="radio" open="1" value="1" name="split_comments">
				<d><?php _e('Do not separate comments from trackbacks.','awp');?></d>
			</item>

			<item type="radio" open="1" value ="2"  name="split_comments">
				<d><?php _e('Separate trackbacks from comments.','awp');?></d>
				<desc><?php _e('This will move the trackbacks to the bottom of the comment list.','awp');?></desc>
			</item>

			<item type="radio" value ="3" name="split_comments">
				<d><?php _e('Do not show Trackbacks.','awp');?> </d>
				<desc><?php _e('Trackbacks will not show up at all when the comments are loaded through AWP.','awp');?></desc>
			</item>
		</submenu>
		<submenu>
			<desc><![CDATA[<?php _e('Separate Pingbacks/trackbacks from <i>inline</i> comments (ones loaded after the rest of the page loads):','awp');?>]]></desc>

			<item type="radio" open="1" value="1" name="split_inline_comments">
				<d><?php _e('Do not separate comments from trackbacks.','awp');?></d>
			</item>

			<item type="radio" open="1" value ="2"  name="split_inline_comments">
				<d><?php _e('Separate trackbacks from comments.','awp');?> </d>
				<desc><?php _e('This will move the trackbacks to the bottom of the comment list.','awp');?></desc>
			</item>

			<item type="radio" value ="3" name="split_inline_comments">
				<d><?php _e('Do not show Trackbacks.','awp');?> </d>
				<desc><?php _e('Trackbacks will not show up at all when the comments are loaded through AWP.','awp');?></desc>
			</item>
		</submenu>

		<submenu>
			<desc><?php _e('If you need the default template to fit in specifically with your theme, then you may set the specific elements here','awp');?>.</desc>

				<item type="text" nobreak="1" name="comment_all_tag" d="<?php _e(' Tag before/after all comments: %s /','awp');?>"/>
				<item type="text" open="1" name="comment_all_tag_end" >
					<desc><![CDATA[<?php _e('Suggested tags for this level: ol or div. To have indentation without using an ordered list (to fit in with your theme.) Use divs and add the following to your style.css file. <code>.post_comments div * > div {padding-left:1em;}</code>','awp');?>]]></desc>
				</item>

				<item type="text" nobreak="1" name="comment_reply_tag" d="<?php _e(' Tag before/after reply comments: %s /','awp');?>" />
				<item type="text" open="1" name="comment_reply_tag_end" >
					<desc><?php _e('Suggested tags for this level: ol or div.','awp');?></desc>
				</item>

				<item type="text" nobreak="1" d="<?php _e(' Tag before/after each comment: %s /','awp');?>" name="comment_tag"/>
				<item type="text" open="1" name="comment_tag_end" >
					<desc><?php _e('Suggested tags for this level: li or div.','awp');?></desc>
				</item>

				<item type="text" nobreak="1" d="<?php _e(' Tag before/after Title Bar: %s /','awp');?>"  name="comment_title_tag"/>
				<item type="text" name="comment_title_tag_end" >
					<desc><?php _e('Suggested tags for this level: p, div or span','awp');?></desc>
				</item>
			</submenu>
		</menu>
	</menus>
<?php


	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);
	}

	function awp_get_options($j){

		$j[texts][] = 'comment_open';
		$j[texts][] = 'comment_hide';
		$j[texts][] = 'commentform_reply_hide';
		$j[texts][] = 'comment_tag';
		$j[texts][] = 'comment_all_tag';
		$j[texts][] = 'comment_reply_tag';
		$j[texts][] = 'comment_body_tag';
		$j[texts][] = 'comment_title_tag';
		$j[texts][] = 'comment_tag_end';
		$j[texts][] = 'comment_all_tag_end';
		$j[texts][] = 'comment_reply_tag_end';
		$j[texts][] = 'comment_body_tag_end';
		$j[texts][] = 'comment_title_tag_end';
		$j[checkboxes][] = 'simple_comments';
		$j[checkboxes][] = 'show_comments_single';
		$j[checkboxes][] = 'show_comments_home';
		$j[checkboxes][] = 'comment_order';
		$j[checkboxes][] = 'show_comments_page';
		$j[selects][] = 'inlinecomments';
		$j[radios][] = array('split_inline_comments',3);
		$j[radios][] = array('split_comments',1);
		$j[radios][] = array('comment_template',1);
		return $j;
	}

	function default_options(){
	global $awpall;
		//if these options aren't set it can cause trouble so we make sure they are.
		$awpall['comment_all_tag'] = ($awpall['comment_all_tag'] != '') ? $awpall['comment_all_tag'] : '<ol class="comments commentlist">';
		$awpall['comment_reply_tag'] = ($awpall['comment_reply_tag'] != '') ? $awpall['comment_reply_tag'] : '<ol class="reply">';
		$awpall['comment_tag'] = ($awpall['comment_tag'] != '') ? $awpall['comment_tag'] : '<li  class="comment %alt" id="comment-%ID">';
		$awpall['comment_title_tag'] = ($awpall['comment_title_tag'] != '') ? $awpall['comment_title_tag'] : '<span class="commentbar">';
		$awpall['comment_body_tag'] = ($awpall['comment_body_tag'] != '') ? $awpall['comment_body_tag'] : '<div>';

		$awpall['comment_all_tag_end'] = ($awpall['comment_all_tag_end'] != '') ? $awpall['comment_all_tag_end'] : '</ol>';
		$awpall['comment_reply_tag_end'] = ($awpall['comment_reply_tag_end'] != '') ? $awpall['comment_reply_tag_end'] : '</ol>';
		$awpall['comment_tag_end'] = ($awpall['comment_tag_end'] != '') ? $awpall['comment_tag_end'] : '</li>';
		$awpall['comment_title_tag_end'] = ($awpall['comment_title_tag_end'] != '') ? $awpall['comment_title_tag_end'] : '</span>';
		$awpall['comment_body_tag_end'] = ($awpall['comment_body_tag_end'] != '') ? $awpall['comment_body_tag_end'] : '</div>';

		$awpall['comment_threaded_depth'] = (intval($awpall['comment_threaded_depth']) > 0) ? $awpall['comment_threaded_depth'] : 1;

		$awpall['split_inline_comments'] = ($awpall['split_inline_comments']) ? $awpall['split_inline_comments'] : 3;

	}

	function set_defaults(){
	global $awpall;
		$awpall[closed_comments] = __('Comments are closed','awp');
		$awpall[no_comments] = __('No Comments','awp');
		$awpall[comment_open] = __('Show Comments','awp');
		$awpall[comment_hide] = __('Hide Comments','awp');
		$awpall[inlinecomments] = 'Enabled';
		$awpall[show_comments_single] = 1;
		$awpall[show_comments_page] = 1;
		$awpall[split_inline_comments] = 3;
		update_option('awp',$awpall);
	}
}
?>
