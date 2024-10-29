<?php
	/*
	Plugin Name: Live Preview
	Plugin URI: http://ajaxedwp.com
	Description: Adds a live preview after your comment form. (Does not work with the Rich Text Editor.)
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

$awp_init[] = 'AWP_livepreview';
register_activation_hook(__file__,array('AWP_livepreview','set_defaults'));

Class AWP_livepreview{

	function init(){
	global $awpall;
		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
			add_action('awp_admin_commentform',array(&$this,'admin'));
			add_filter('awp_get_options',array(&$this,'awp_get_options'));
			add_action('awp_livepreview_admin_options', array(&$this,'smilies_admin'));
		}elseif($awpall['live_preview'] == 'Enabled'){
			add_action('awp_commentform_after_form', array(&$this,'div'));
			add_action('awp_commentform_comment', array(&$this,'onkeyup'));
			add_action('aWP_JS', array(&$this,'js'));

			if($awpall['live_preview_smilies'] == 1){
				add_action('awp_live_preview_vars',array(&$this,'vars'));
				add_action('awp_live_preview_filters', array(&$this,'filters'));
				add_action('awp_live_preview_private_methods', array(&$this,'methods'));
			}
		}
	}

	function js(){
			echo "\n"."\n".'/* start live preview */'."\n";
			include('../modules/livepreview/live-preview.js.php');

	}

	function onkeyup(){
	global $id, $input_suffix;
		echo 'onkeyup="aWP_livepreview.preview('.$id.',\''.$input_suffix.'\')"';
	}

	function div($return=0){
	global $awpall,$id;

			if($awpall['live_preview_nocomment']){
				$preview .= $awpall['live_preview_before'];
				$preview .= '<div id="add_comment_live_preview_'.$id.'" class="add_comment_live_preview main_border">Go ahead and start typing.</div>';
				$preview .= $awpall['live_preview_after'];
			}else{
				$preview .= '<div class="awpcomments">';
				$preview .= $awpall['comment_all_tag'];
				$preview .= str_replace(array('%ID','%alt','%auth'),'',$awpall['comment_tag']);
				$preview .= $awpall['comment_title_tag'];
				$preview .= __('You will post the following soon.','awp');
				$preview .= $awpall['comment_title_tag_end'];
				$preview .= '<div id="add_comment_live_preview_'.$id.'">';
				$preview .= __('Go ahead and start typing.','awp');
				$preview .= '</div>';
				$preview .= $awpall['comment_tag_end'];
				$preview .= $awpall['comment_all_tag_end'];
				$preview .= '</div>';
			}
			if($return == 1){
				return $preview;
			}else{
				echo $preview;
			}
	}

/**
Smilies
**/


	function vars(){
	global $smilie_url;

		if(function_exists('csm_convert')){
			global $wpdb, $table_prefix;

				// Get emoticons from DB, order by length
				$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}smileys` ORDER BY length(Emot) DESC");

				// Find and Replace
				foreach ( $result as $object ) {
					$smilies .= "'".$object->Emot."',";
					$files .= "'".$object->File."',";
				}
				echo 'var smilies = ['.$smilies.'];';
				echo 'var smiliesfiles = ['.$files.'];';
				echo 'var smiliesalt = ['.$smilies.'];';
			$smilie_url = get_option("csm_path").'/';
		}else{

			$smilie_url = 'smilies/icon_';
	?>
			var smilies = [':mrgreen:', ':neutral:', ':twisted:', ':arrow:', ':shock:', ':smile:', ':???:', ':cool:', ':evil:', ':grin:', ':idea:', ':oops:', ':razz:', ':roll:', ':wink:', ':cry:', ':eek:', ':lol:', ':mad:', ':sad:', '8-/?)', '8-/?O', ':-/?(', ':-/?)', ':-/??', ':-/?D', ':-/?P', ':-/?o', ':-/?x', ':-/?|', ';-/?)', ':!:', ':?:'];

			var smiliesfiles = ['mrgreen', 'neutral', 'twisted', 'arrow', 'eek', 'smile', 'confused', 'cool', 'evil', 'biggrin', 'idea', 'redface', 'razz', 'rolleyes', 'wink', 'cry', 'surprised', 'lol', 'mad', 'sad', 'cool', 'eek', 'sad', 'smile', 'confused', 'biggrin', 'razz', 'surprised', 'mad', 'neutral', 'wink', 'exclaim', 'question'];

			var smiliesalt = [':mrgreen:', ':neutral:', ':twisted:', ':arrow:', ':shock:', ':smile:', ':???:', ':cool:', ':evil:', ':grin:', ':idea:', ':oops:', ':razz:', ':roll:', ':wink:', ':cry:', ':eek:', ':lol:', ':mad:', ':sad:', '8-)', '8-O', ':-(', ':-)', ':-?', ':-D', ':-P', ':-o', ':-x', ':-|', ';-)', ':!:', ':?:'];

	<?php
		}
	?>

		var smiliescount = 0;
		var x = smiliescount = smilies.length;
		var smil_reg = [];

		while(x--){
				smilies[x] = smilies[x].replace(/([\\\^\$*+[\]?{}.=!:(|)])/g,"\\$1");
				smilies[x] = smilies[x].replace(/(\/\\?)/g,"?");
				smil_reg[x] = new RegExp('(>|\\s|^)'+smilies[x]+'(\\s|$|<)', "gm");
		}
	<?php

	}

	function filters(){
	?>
		 comment = _convertsmilies(comment);
	<?php
	}

	function methods(){
	global $home,$smilie_url;
	?>
		var _convertsmilies = function(text){
			var x = smiliescount;
				while(x--){
					if(text.match(smil_reg[x])){
						text = text.replace(smil_reg[x],'$1<img src="<?php echo $home;?>/wp-includes/images/<?php echo $smilie_url;?>'+smiliesfiles[x]+'.gif" alt="'+smiliesalt[x]+'" class="wp-smiley" />$2');
					}
				}
			return text;
		};
	<?php
	}



	function admin(){
	global $awpall, $aWP;

	ob_start();
?>
			<menus>
				<menu id="live_preview">
					<title><?php _e('Live Preview Options','awp');?></title>
					<name><?php _e('Live Preview','awp');?></name>
					<submenu>
						<item type="checkbox" important="5" open="1" d="<?php _e('Do not show any html in the live preview. Kinda defeats the purpose of the live preview.','awp');?>" name="live_preview_html"/>
							<item type="text" name="live_preview_no_tags" >
							<d><?php _e('Do not display the following tags %s Use just the tag names separated with spaces. (form table input strong etc.)','awp');?></d>
							</item>
							<action>awp_livepreview_admin_options</action>
					</submenu>

					<submenu>
						<desc><?php _e('By default AWP styles the preview as if it were a comment','awp');?></desc>

						<item type="checkbox" open="1" name="live_preview_nocomment" d="<?php _e('Do not style live preview as comment.','awp');?>"/>
						<item type="text" open="1" size="35" d="<?php _e('Text or XHTML before live comment preview. %s You may use XHTML code or text. Example: &amp;lt;p>You say:&amp;lt;/p>','awp');?>" name="live_preview_before"/>
						<item type="text" size="35" d="<?php _e('Text or XHTML after live comment preview. %s You may use XHTML code or text.','awp');?>"  name="live_preview_after" />
					</submenu>
				</menu>
			</menus>
<?php
	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);

	}

	function smilies_admin(){
	global $aWP, $awpall;
		 ob_start();
?>
			<item type="checkbox" name="live_preview_smilies">
				<d><?php _e('Automatically convert smilies to images? (May cause a small amount of lag for fast typers)','awp');?></d>
   		 		<desc></desc>
			</item>
<?php
	$menu =	 ob_get_contents();
	ob_end_clean();
	do_action('awp_build_menu',$menu);
	}

	function set_defaults(){
	global $awpall;
		$awpall[live_preview] = 'Enabled';
		$awpall[live_preview_no_tags] = 'form input textarea script';
		update_option('awp',$awpall);
	}

	function awp_get_options($i){
		$i[selects][] = 'live_preview';
		$i[texts][] = 'live_preview_no_tags';
		$i[texts][] = 'live_preview_before';
		$i[texts][] = 'live_preview_after';
		$i[checkboxes][] = 'live_preview_html';
		$i[checkboxes][] = 'live_preview_ajax';
		$i[checkboxes][] = 'live_preview_nocomment';
		$i[checkboxes][] = 'live_preview_smilies';
		return $i;
	}

}

?>
