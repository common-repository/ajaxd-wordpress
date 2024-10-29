<?php
	/*
	Plugin Name: Rich Text Editor
	Plugin URI: http://ajaxedwp.com
	Description: Uses tinyMCE to provide a WYSIWYG interface for the comment form.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

$awp_init[] = 'AWP_richtext';

register_activation_hook(__file__,array(&$this,'set_defaults'));
class AWP_richtext {

	function init(){
	global $awpall;
		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
			add_action('awp_admin_commentform',array(&$this,'admin'));
			add_filter('awp_get_options',array(&$this,'awp_get_options'));
		}elseif($awpall['richtext'] == 'Enabled'){
			add_filter('awp_ajax_commentform_actions', array(&$this,'update_richtext'));
			add_filter('comment_form', array(&$this,'add_js'));
			add_filter('awp_commentform_on_submit', array(&$this,'add_submit'),5);
			add_action('awp_js_core',array(&$this,'JS'));
			add_action('awp_js_vars',array(&$this,'JS_vars'));
			add_action('wp_head', array(&$this,'include_js'),9);
			add_action('aWP_JS', array(&$this,'aWP_JS'));
		//	add_action('awp_commentform_before_submit', array(&$this,'awp_commentform_before_submit'));
		}


	}
	function JS(){
?>

	richtext: function(inputsuffix){
		try{
  			var inst = tinyMCE.getInstanceById('comment'+inputsuffix);
			if(inst.getHTML){
				document.getElementById('comment'+inputsuffix).value = inst.getHTML().replace(/<br \/>/g,"\n");
			}else{
				document.getElementById('comment'+inputsuffix).value = tinyMCE.get('comment'+inputsuffix).getContent();
			}
		}catch(e){}
	},

	richtextstart: function(inputsuffix,id){
		try{
			_a['richeditors'][id] = 'comment'+inputsuffix;
			tinyMCE.execCommand('mceAddControl', false, 'comment'+inputsuffix);
		}catch(e){}
	},


	richtexttoggle: function(){
		try{
			if(_a['richeditors'][_d[i].id]){
				if ((tinyMCE.getInstanceById && tinyMCE.getInstanceById(_a['richeditors'][_d[i].main])) || (tinyMCE.get && tinyMCE.get(_a['richeditors'][_d[i].main]))) {
					tinyMCE.execCommand("mceRemoveControl", false, _a['richeditors'][_d[i].main]);
				}else{
					tinyMCE.execCommand('mceAddControl', false, _a['richeditors'][_d[i].main]);
				}
			}
		}catch(e){}

	},

<?php
	}

	function JS_vars(){
?>
		_a['richeditors']	= {};
		_a['aftermove'] = function(){aWP.richtexttoggle()};
		_a['beforemove'] = function(){aWP.richtexttoggle()};
<?php
	}

	function awp_commentform_before_submit(){
	global $id;

		echo AWP::maybe_JS('<input type="button" value="'.__('Toggle WYSIWYG','awp').'" class="ed_button" onclick="aWP.richtexttoggle('.$id.'); return false;" />');
	}

	function get_buttons(){
	global $awpall;

		$buttons = "bold,italic,underline,separator,strikethrough,undo,redo,link,unlink,code,emotions,spellchecker";
	return $buttons;
	}

	function aWP_JS(){
	global $awpall;

		$buttons = $this->get_buttons();

		$init = "wpEditorInit = function() {};tinyMCE.init({
				mode : 'none',
				theme : 'advanced',
				theme_advanced_buttons1 : '$buttons',
				theme_advanced_buttons2 : '',
				theme_advanced_buttons3 : '',
				force_p_newlines : false,
				force_br_newlines : true,
				gecko_spellcheck : true,
				content_css : '',
				theme_advanced_toolbar_location : 'top',
				theme_advanced_toolbar_align : 'left',
				language : 'en',
				entity_encoding : 'raw',
				plugins : 'spellchecker,safari',
				extended_valid_elements : 'a[name|href|title],font[face|size|color|style],span[class|align|style]'
});";
		echo $init;

	}

	function update_richtext($actions){
	global $awpall,$id;
		$input_suffix = apply_filters('awp_input_suffix','');
		$actions[] = 'setTimeout("aWP.richtextstart(\''.$input_suffix.'\','.$id.');",1000);';

	return $actions;
	}

	function add_submit($submit){
	global $input_suffix;
		$submit .= "aWP.richtext('$input_suffix');";
	return $submit;
	}

	function include_js(){
		ob_start();
		wp_print_scripts('tiny_mce');
		$script = ob_get_contents();
		ob_end_clean();

		$script = str_replace("<script type='text/javascript' src='http://lo/trunk/wp-admin/js/editor.js?ver=20080325'></script>",'',$script);
		echo $script;
		//echo '<script type="text/javascript" src="'.get_option('siteurl').'/wp-includes/js/tinymce/tiny_mce.js"></script>';
	}

	function add_js(){
	global $id,$input_suffix;
		if(is_singular()){
	?>
		<script type="text/javascript">
			setTimeout("try{aWP.richtextstart('<?php echo $input_suffix;?>',<?php echo $id;?>);}catch(e){}",1000);
		</script>

	<?php
		}
	}
	function admin(){
	global $aWP, $awpall;

		ob_start();
?>
		<menu id="richtext">
			<title><?php _e('AWP RichText Options.','awp');?></title>
			<name><?php _e('AWP Richtext Support','awp');?></name>
			<desc><?php _e('This module adds the abiliuty to use a WYSIWYG interface with the comment form on single pages.','awp');?></desc>
		</menu>
<?php
		$menu =	 ob_get_contents();
		ob_end_clean();
		do_action('awp_build_menu',$menu);
	}

	function awp_get_options($i){
		$i[selects][] = 'richtext';
		return $i;
	}

	function set_defaults(){
		global $awpall;
		$awpall[richtext] = 'Enabled';
		update_option('awp',$awpall);
	}
}

?>
