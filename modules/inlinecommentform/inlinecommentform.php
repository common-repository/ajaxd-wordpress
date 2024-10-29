<?php
/*
	Plugin Name: Inline Comment Form
	Plugin URI: http://ajaxedwp.com
	Description: This module controls all aspects of inline comment form and is required by modules that hook into AWP comment form.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

$awp_init[] = 'AWP_commentform';
register_activation_hook(__file__,array('AWP_commentform','set_defaults'));
//register_deactivation_hook(__file__,array(&$this,'rm_options'));

class AWP_commentform{

	function init(){

		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
			add_action('awp_admin_commentform',array(&$this,'admin'));
			add_filter('awp_get_options',array(&$this,'awp_get_options'));
		}elseif(AWP::enabled('commentform')){
			/*Template Functions*/
			add_action('awp_commentform', array(&$this,'div'));
			add_action('awp_commentform_link', array(&$this,'links'));
			add_action('awp_commentform_quickclose', array(&$this,'quickclose'));

			/*Internal Functions*/
			add_action('awp_commentform_before_comment', array(&$this,'integrate'));
			add_filter('awp_commentform_on_submit', array(&$this,'on_submit'));
			add_action('awp_ajax_type_submit_commentform', array(&$this, 'submit_form'));
			add_action('awp_ajax_type_commentform', array(&$this, 'AJAX'));
			add_action('awp_simple_comments_last', array(&$this,'div'));
			add_action('awp_js_start',array(&$this,'awp_js_start'));
			add_action('awp_js_toggle',array(&$this,'awp_js_toggle'));
			add_action('awp_js_finish',array(&$this,'awp_js_finish'));
			add_filter('awp_input_suffix',array(&$this,'input_suffix'));
		}

	}

	function awp_js_finish(){
	global $awpall;
	?> //<script>
			submit_commentform : function(){

				$('comment_result_'+_d[i].main).innerHTML = _d[i].response;

				if(!_d[i].error){
					if(_d[i].show){
						var num = 1;
						if(_p[i].prev_link){
							num = _p[i].prev_link;
						}

						link_text('awpcommentform_link'+num+'_'+_d[i]['id'],_d[i].show)
					}
					/*If the comment form is inside of the comment div, reloading will destroy it, so we move it.*/
<?php	if(!$awpall['comment_form_nevermove']){	?>
					var moveto;

					if($('awpcommentform_anchor_'+_d[i].main)){
						moveto = 'awpcommentform_anchor_'+_d[i].main;
					}else{
						moveto ='awpcomments_'+_d[i].main;
						$('awpcommentform_'+_d[i].main).style.display='none';
					}

					if(moveto){
						if(_a['beforemove'])
							_a['beforemove']();
						try{move( moveto,'awpcommentform_'+_d[i].main);}catch(e){}
						if(_a['aftermove'])
							_a['aftermove']();
					}
<?php 	}?>

<?php 	if(AWP::enabled('threadedcomments')){	?>
					try{$('comment_parent_'+_d[i].main).value = 0;}catch(e){}
<?php 	}	?>
					try{$('comment_'+_d[i][_d[i].primary]).value = '';}catch(e){}
					try{$(_d[i].type+'_'+_d[i][_d[i].primary]).disabled = false;} catch(e){}

<?php	 if(AWP::enabled('inlinecomments')){?>
					if($('awpcomments_none_'+_d[i].main)){
							if($('awpcomments_none_'+_d[i].main).style.display != 'none'){
								$('awpcomments_none_'+_d[i].main).style.display = 'none';
								$('awpcomments_link_'+_d[i].main).style.display = 'inline';
							}
					}

					var temp = 0
					if($('awpcomments_'+_d[i].id).innerHTML.length > 0){
						temp = 1;
					}

					try{
						if(_p['awpcomments_'+_d[i].id] && _p['awpcomments_'+_d[i].id].hide){
							link_text('awpcomments_link_'+_d[i].id, _p['awpcomments_'+_d[i].id].hide);
						}
					}catch(e){}
						aWP.doit({'id': _d[i].id, 'type': 'comments', 'force': temp, 'focus': 'comment-'+_d[i].mrc});
<?php 	}	?>
				}
			},
<?php
	}


	function awp_js_toggle(){
	global $awpall;

?>
			commentform: function(){
				var comparent;
				var moveto;
				var sib;
				var style = arguments[1];
	<?php 		if(AWP::enabled('threadedcomments')){	?>
					try{ comparent = $('comment_parent_'+_d[i].main).value
					$('comment_parent_'+_d[i].main).value = _d[i].com_parent;}catch(e){}

	<?php 		}	?>

					try{if(_d[i].replyto){$('comment_'+_d[i].id).innerHTML = "@" + _d[i].replyto;}}catch(e){}

					if(!_p[i].nomove && style != 'none' && !_d[i].nomove){
						if(!$('awpcommentform_anchor_'+_d[i].main)){
							var div = document.createElement('div');
							div.id = 'awpcommentform_anchor_'+_d[i].main;
							div.style.display = 'none';
							try{$('awpcommentform_'+_d[i].main).parentNode.insertBefore(div, $('awpcommentform_'+_d[i].main).nextSibling);}catch(e){}
						}
					}else{
						if(_d[i].nomove)
							_p[i].nomove = 1;
					}

				if(_p[i].prev_link != _d[i].link_num || _d[i].faked ){
					var will_move = 1;
					 moveto = 'awpcommentform_link'+_d[i].link_num+'_'+_d[i].main;
					 sib = 'sib'
				}
				if((style == 'none' || _d[i].quickclose == 1 || _p[i].nomove) && !will_move){
					var will_hide = 1;
				}
				if(_p[i].prev_link == _d[i].link_num && !_p[i].nomove && !will_move){
					var will_remove = 1;
				}

				link_text('awpcommentform_link'+_d[i].link_num+'_'+_d[i].main,_d[i].show,_d[i].hide);

				if(will_remove){
					_d[i].no_jump = 1;
					_d[i].link_num =0;
					will_move = 1
					moveto = 'awpcommentform_anchor_'+_d[i].main
					sib = '';

	<?php 	if(AWP::enabled('threadedcomments')){	?>
					try{$('comment_parent_'+_d[i].main).value = 0;}catch(e){}
	<?php 	}	?>

				}

				if(will_move == 1){
					var pos1 = pos(i);
<?php 				if(!$awpall['comment_form_nevermove']){	?>

						if(_a['beforemove'])
							_a['beforemove']();

						move(moveto,i,sib);

						if(_a['aftermove'])
							_a['aftermove']();

				<?php do_action('awp_commentform_aftermove');?>

<?php			}?>

					var pos2 = pos(i);

					if(pos1 == pos2)
						will_hide = 1;
				}

				if(_p[i].last_show && (pos1 != pos2  || _d[i].quickclose == 1)){
					link_text('awpcommentform_link'+_p[i].prev_link+'_'+_d[i].main,_p[i].last_show);
				}

				if(will_hide == 1){
					aWP.toggle.pick_switch();
					_d[i].no_jump = 1;
				}



				try{$('submit_commentform_'+_d[i].main).disabled = false;} catch(e){}

				_p[i].last_show = _d[i].show;
				_p[i].prev_link = _d[i].link_num;
			},
	<?php

	}

	function awp_js_start(){
?>

			commentform: function(postobj){

				if(isNaN(_p[i].prev_link)){
					_p[i].prev_link = 1;
					_d[i].faked = 1;
				}

				if(isNaN(_d[i].com_parent)){
					_d[i].com_parent = 0;
				}

			return postobj;
			},

<?php
	}

	function AJAX(){
	global $id, $post,$awpall,$user_ID ;

		ob_start();
			include (AWP::template('inlinecommentform','commentform'));
		$response = ob_get_contents();
		ob_end_clean();
		/*Check for error message.*/


		AWP::make_response($response);
	}

	function submit_form(){
	global $wpdb, $post,$id,$awpall,$wp_actions, $user_ID;
		add_filter('comment_post_redirect', array(&$this, 'remove_redirect'));

		 $wp_actions[] = 'admin_head'; //Yay for hacking Wordpress.

		ob_start("awp_trynodie");
			require_once(AWP::find_path('wp-comments-post.php').'wp-comments-post.php');
		ob_end_clean();

		if(!$_POST['comment_parent'])
			$vars = $this->get_link_texts();

		if($awpall[nomove])
			$vars[nomove] = 1;

		$vars[update_next] = 'aWP.complete';

		$response[] = __('Your comment has been submitted','awp');
		$response['mrc'] = $comment_id;

		AWP::make_response($response,$vars);

	}

	function remove_redirect(){
	return;
	}

	function div(){
	global $id, $post,$awpall,$user_ID;
			if($awpall['comment_template'] == 2){
				return;
			}
		if ( comments_open() ) {
			if ( (is_single() && $awpall['show_commentform_single'] == 1) || (is_page() && $awpall['show_commentform_page'] == 1 )|| (is_home() && $awpall['show_commentform_home'] == 1 )){
				echo "\n".'<div id="awpcommentform_'.$id.'" class="commentform">';
					include(AWP::template('inlinecommentform','commentform'));
				echo '</div>';
			}else{
				echo "\n".'<div id="awpcommentform_'.$id.'" class="commentform" style="display:none;"></div>';
			}
		}
	}

	function get_link_texts($extra=''){
		global $awpall;
		$texts = array();
		$defaults = array();

		$texts[show] = $awpall['commentform_open'];
		$texts[hide] = $awpall['commentform_hide'];

		$defaults[show] = __('Add a Comment','awp');
		$defaults[hide] = __('Cancel reply','awp');

		return AWP::link_texts($texts,$defaults,'comment');
	}


	function links($return=false){
	global $id,$awpall,$awp_link_count;
	global $is_page, $is_single;


		if(!$awp_link_count[$id]){
			$awp_link_count[$id] = 1;
		}

		if($awpall['comment_template'] == 2){
			return;
		}

		if ( comments_open() ) {
			$links = $this->get_link_texts();

			$anchor = $links[show];

			$link = '';
			if ( ($is_single && $awpall['show_commentform_single'] == 1) || ($is_page && $awpall['show_commentform_page'] == 1 )){
				$anchor = $links[hide];
			}

			$ops[_class] = "commentform_link";
			$ops[id] = 'awpcommentform_link'.$awp_link_count[$id].'_'.$id;
			$ops[anchor] = stripslashes($links[show]);
			$ops[URL] = get_permalink($id).'#respond';
			$ops[doit] = "'id': '$id', 'type': 'commentform', 'show': '".js_escape($links[show])."', 'hide': '".js_escape($links[hide])."', 'link_num': '$awp_link_count[$id]'";
			$ops[anchor] =$anchor;

			$ops = apply_filters('awp_link_array',$ops, 'comment_form');

			if($awpall[nomove] || !is_singular())
				$ops[doit] .= ", 'nomove' : 1";

			$awp_link_count[$id]++;

			$link = AWP::links($ops,$return);

		}else{
			//$link .= ($awpall['closed_comments'] == '')? __('Comments are closed','awp') : AWP::process_text($awpall['closed_comments']);

		}

		if($return){
			return $link;
		}else{
			echo $link;
		}
	}

	function quickclose(){
	global $id;
		$links = $this->get_link_texts();
		$ops[_class] = "commentform_link";
		$ops[id] = 'awpcommentform_link0_'.$id;
		$ops[URL] = get_permalink($id).'#respond';
		$ops[doit] = "'id': '$id', 'type': 'commentform', 'quickclose' : 1, 'show': '".js_escape($links[show])."', 'hide': '".js_escape($links[hide])."'";
		$ops[anchor] = '<img src="'.WP_CONTENT_URL.'/plugins'.AWP_MODULES.'/inlinecommentform/close_normal.gif" alt="Click here to close" />';

		echo AWP::maybe_JS(AWP::links($ops));
	}

	function on_submit($on_submit){
	global $id,$awpall,$input_suffix;

			return 	$on_submit."aWP.doit({'id': $id, 'type': 'submit_commentform', 'submit': 'TRUE'}); return false;";

	}

	function input_suffix(){
	global $awpall,$id;
		$input_suffix = str_replace('%ID',$id,$awpall['commentform_input_suffix']);

		return $input_suffix;
	}


	function integrate(){
		if(function_exists('display_cryptographp')){
	?>
		<p><?php display_cryptographp(); ?></p>

	<?php
		}

		if(function_exists('show_subscription_checkbox')){
			show_subscription_checkbox();
		}
	}

	function admin(){
	global $aWP, $awpall;
	ob_start();
?>
	<menus>
		<menu id="commentform">
			<title><?php _e('Add Comment Box Options','awp');?></title>
			<name><?php _e('Ajax Comment Form','awp');?></name>
			<submenu>
				<item type="text" important="2" open="1" d='<?php _e('Show text (show comment form)','awp');?>' name="commentform_open"/>
				<item type="text" open="1" name="commentform_hide" d='<?php _e('Hide text (hide comment form)','awp');?>'/>
			</submenu>
			<submenu>
				<item type="checkbox" value="1" name="nomove"  d='<?php _e('Always hide comment form when canceled?','awp');?>'>
					<desc><?php _e('Normally, the comment form is never hidden on single post pages, and is only moved back to its original location. This option will force the comment to hide when the comment form is canceled rather than moving. ','awp');?></desc>
				</item>

				<item type="checkbox" value="1" name="comment_form_nevermove"  d='<?php _e('Do not move comment form to links.','awp');?>'>
					<desc><?php _e('Normally, the comment form is moved directly after the link that calls it. Selecting this option will never move the form.','awp');?></desc>
				</item>
			</submenu>

			<submenu>
					<desc><?php _e('If the following options are selected then on all single posts the add comment box will already be open when the page loads, so users do not have to look for it. It still can be hidden and moved.','awp');?></desc>
					<item type="checkbox" open="1" name="show_commentform_single" d="<?php _e('Have Add Comment Box open by default on single post pages?','awp');?>"/>
					<item type="checkbox" open="1" name="show_commentform_page"  d="<?php _e('Have Add Comment Box open by default on pages?','awp');?>"/>
					<item type="checkbox" name="show_commentform_home" d="<?php _e('Have Add Comment Box open by default on index page?','awp');?>"/>

					<action>awp_admin_commentform_options</action>
				</submenu>

			</menu>
		</menus>
<?php


/*
The following will be moved into the menu eventually. Currently unable to be modified.
			<submenu>
				<desc><?php _e('To retain XHTML validity, each comment form and element should have a unique id. Do not change this unless required.','awp');?></desc>
				<item type="text"  name="commentform_input_suffix" d='<?php _e('Comment form input suffix.','awp');?>'/>
			</submenu>

*/
	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);
	}

	function awp_get_options($i){
		$i[selects][] = 'commentform';
		$i[checkboxes][] = 'show_commentform_page';
		$i[checkboxes][] = 'show_commentform_home';
		$i[checkboxes][] = 'show_commentform_single';
		$i[checkboxes][] = 'nomove';
		$i[checkboxes][] = 'comment_form_nevermove';
		$i[texts][] = 'commentform_input_suffix';
		$i[texts][] = 'commentform_open';
		$i[texts][] = 'commentform_hide';
		return $i;
	}

	function set_defaults(){
		global $awpall;
		$awpall[commentform] = 'Enabled';
		$awpall[commentform_input_suffix] = '_%ID';
		$awpall[commentform_open] = __('Add a Comment','awp');
		$awpall[commentform_hide] = __('Cancel reply','awp');
		$awpall[show_commentform_page] = 1;
		$awpall[show_commentform_single] = 1;

		update_option('awp',$awpall);
	}
}


function awp_trynodie($error){
global $awpall;
	header('Content-type: text/xml; charset=' . get_option('blog_charset'), true);
		$err = '<?xml version="1.0"?>

		<awp>
			<variables>
				<var name="error"><![CDATA[TRUE]]></var>
				<var name="update_next"><![CDATA[aWP.complete]]></var>
			</variables>
			<responses><response><![CDATA[';

	preg_match('@<p>(.*?)</p>@', $error,$errs);
	if(!$errs[1]){
		$errs[1] = 'Error Recieved:'.strip_tags($error);

	}
	$err.= $errs[1].']]></response></responses>';

	if(AWP::enabled('recaptcha')){
		$err .= '
				<actions>
					<action><![CDATA[Recaptcha.reload();]]></action>
				</actions>';
	}

	$err .=	'</awp>';

	return $err;
}
?>