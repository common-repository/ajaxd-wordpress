<?php
	/*
	Plugin Name: Preview Comment
	Plugin URI: http://ajaxedwp.com
	Description: Adds a preview buttom that submits the comment to the server so the preview is exactly as it will appear. It even allows other plugins to format it.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

$awp_init[] = 'AWP_previewcomment';
register_activation_hook(__file__,array('AWP_previewcomment','set_defaults'));

Class AWP_previewcomment{

	function init(){

		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
			add_action('awp_admin_commentform',array(&$this,'admin'));
			add_filter('awp_get_options',array(&$this,'awp_get_options'));
		}elseif(AWP::enabled('previewcomment')){
			add_action('awp_ajax_type_previewcomment', array(&$this,'AJAX'));
			add_action('awp_commentform_before_submit', array(&$this,'preview_button'));
			add_action('awp_commentform_hidden_inputs', array(&$this,'preview_button'));
			add_action('awp_commentform_after_form', array(&$this,'preview_div'));
			add_action('awp_js_toggle',array(&$this,'awp_js_toggle'));
			add_action('awp_js_start',array(&$this,'awp_js_start'));
		}

	}

	function AJAX(){
	global $awpall, $id;
		$preview .= '<div class="awpcomments">';
		$preview .= $awpall['comment_all_tag'];
		$preview .= $awpall['comment_tag'];
		//$preview .= $awpall['comment_title_tag'];
		//$preview .= __('You will post the following soon ','awp');
		//$preview .= $awpall['comment_title_tag_end'];
		$preview .= apply_filters('comment_text', stripslashes($_POST['comment']));
		$preview .= $awpall['comment_tag_end'];
		$preview .= $awpall['comment_all_tag_end'];
		$preview .= '</div>';

		$response[] = $preview;

		AWP::make_response($response, $vars,$actions);
	}

	function awp_js_start(){
	global $awpall, $id, $pages;
	?>		previewcomment: function(postobj){

				if($('comment_'+_d[i].id)){
					postobj['comment'] = $('comment_'+_d[i].id).value;
				}else{
					base = document.getElementById('awpsubmit_commentform_'+id).getElementsByTagName('textarea');
					x = base.length;
					for(j=0; j<x; j++){
						if(base[j].name = 'comment'){
							postobj['comment'] = base[j].value;
							j = x;
						}
					}
				}
			return postobj;
			},
	<?php
	}

	function awp_js_toggle(){
	?>//<script>
			previewcomment: function(){
				$(i).style.height = $('awpsubmit_commentform_'+_d[i].id).offsetHeight+'px';
				$(i).style.top = pos('awpsubmit_commentform_'+_d[i].id)+'px';
				$(i).style.width = $('awpsubmit_commentform_'+_d[i].id).offsetWidth+'px';
				$(i).style.display = 'none';
				$(i).style.overflow = 'auto';
				aWP.toggle.pick_switch();
				$(i).style.position = 'absolute';

			},
	<?php
	}

	function preview_button($return=0){
	global $awpall,$id,$AWP_richtext;
		if(AWP::enabled('richtext'))
			$onclick = $AWP_richtext->add_submit('');
	?>
		<input type="button" value="<?php _e('Preview','awp');?>" class='ed_button' onclick="<?php echo $onclick;?>aWP.doit({'id': '<?php echo $id?>', 'type': 'previewcomment', 'force':1}); return false;" />
	<?php
	}

	function preview_div($return=0){
	global $awpall,$id;
	?>
		<div id="awppreviewcomment_<?php echo $id?>" class="preview_comment" style="position:absolute; display:none;" onclick="this.style.display='none'"></div>
	<?php
	}

	function admin(){
	global $awpall, $aWP;
	ob_start();
?>
		<menus>
			<menu id="previewcomment">
				<name><?php _e('Preview Comment','awp');?></name>
				<title><?php _e('Preview comment Options','awp');?></title>
			</menu>
		</menus>
<?php
	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);

	}

	function set_defaults(){
	global $awpall;
		$awpall[previewcomment] = 'Enabled';
		update_option('awp',$awpall);
	}

	function rm_defaults(){
	global $awpall;
		update_option('awp',$awpall);
	}

	function awp_get_options($i){
		$i[selects][] = 'previewcomment';
		return $i;
	}

}

?>