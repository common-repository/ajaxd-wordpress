<?php
	/*
	Plugin Name: Quick Tags and Smilies
	Plugin URI: http://ajaxedwp.com
	Description: Adds Quicktags and/or clickable smilies  support to the comment form.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

$awp_init[] ='AWP_quicktags';
register_activation_hook(__file__,array('AWP_quicktags','set_defaults'));
Class AWP_quicktags{

	function init(){
	global $awpall;

		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
			add_action('awp_admin_commentform_options',array(&$this,'quicktags_admin'));
			add_action('awp_admin_commentform_options',array(&$this,'clickablesmilies_admin'));
			add_filter('awp_get_options',array(&$this,'awp_get_options'));
		}elseif($awpall['quicktags'] == 'Enabled' || $awpall['clickablesmilies'] == 'Enabled'){

			add_filter('aWP_JS', array(&$this,'js'));

			if($awpall['quicktags'] == 'Enabled')
				add_action('awp_commentform_before_comment', array(&$this,'quicktags'));

			if($awpall['clickablesmilies'] == 'Enabled')
				add_action('awp_commentform_before_comment', array(&$this,'clickablesmilies'));
		}
	}

	function js(){
		echo "\n"."\n".'/* start quicktags */'."\n";
		include(ABSPATH . PLUGINDIR . AWP_MODULES . '/quicktags/quicktags.js');
	}

	function quicktags_admin(){
	global $awpall, $aWP;
	ob_start();
?>

		<item name="quicktags" type="select">
			<d><![CDATA[<?php _e('<strong>Quicktags</strong> -- %s Show buttons above comment form to add html tags such as &lt;strong&gt; and &lt;code&gt;','awp');?>]]></d>
		</item>

<?php
	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);

	}

	function clickablesmilies_admin(){
	global $awpall, $aWP;
	ob_start();
?>

		<item name="clickablesmilies" type="select">
			<d><![CDATA[<?php _e('<strong>Clickable Smilies</strong> -- %s Show all available smilies above comment form, and automatically add them to comments when clicked.','awp');?>]]></d>
			<desc><![CDATA[<?php _e('You must have WordPress smilies enabled for this feature to be useful.','awp');?>]]></desc>
		</item>

<?php
	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);

	}

	function awp_get_options($i){
		 $i[selects][] = 'quicktags';
		 $i[selects][] = 'clickablesmilies';
		return $i;
	}

	function set_defaults(){
	global $awpall;
		$awpall[quicktags] = 'Enabled';
		$awpall[clickablesmilies] = 'Enabled';
		update_option('awp',$awpall);
	}

	function quicktags(){
	global $awpall,$id,$input_suffix;
		 if($awpall['quicktags'] == 'Enabled'){
		ob_start();
	?>
			<p id="quicktags_<?php echo $id;?>">
				<input id="ed_strong_<?php echo $id;?>" accesskey="b" class="ed_button" onclick="aWP_qt.edInsertTag(<?php echo $id;?>, 0,'<?php echo $input_suffix;?>');" value="b" type="button" />
				<input id="ed_em_<?php echo $id;?>" accesskey="i" class="ed_button" onclick="aWP_qt.edInsertTag(<?php echo $id;?>, 1,'<?php echo $input_suffix;?>');" value="i" type="button" />
				<input id="ed_link_<?php echo $id;?>" accesskey="a" class="ed_button" onclick="aWP_qt.edInsertLink(<?php echo $id;?>, 2,'<?php echo $input_suffix;?>');" value="link" type="button" />
				<input id="ed_block_<?php echo $id;?>" accesskey="q" class="ed_button" onclick="aWP_qt.edInsertTag(<?php echo $id;?>, 3,'<?php echo $input_suffix;?>');" value="b-quote" type="button" />
				<input id="ed_img_<?php echo $id;?>" accesskey="m" class="ed_button" onclick="aWP_qt.edInsertImage(<?php echo $id;?>,'<?php echo $input_suffix;?>');" value="img" type="button" />
				<input id="ed_ul_<?php echo $id;?>" accesskey="u" class="ed_button" onclick="aWP_qt.edInsertTag(<?php echo $id;?>, 5,'<?php echo $input_suffix;?>');" value="ul" type="button" />
				<input id="ed_ol_<?php echo $id;?>" accesskey="o" class="ed_button" onclick="aWP_qt.edInsertTag(<?php echo $id;?>, 6,'<?php echo $input_suffix;?>');" value="ol" type="button" />
				<input id="ed_li_<?php echo $id;?>" accesskey="l" class="ed_button" onclick="aWP_qt.edInsertTag(<?php echo $id;?>, 7,'<?php echo $input_suffix;?>');" value="li" type="button" />
				<input id="ed_code_<?php echo $id;?>" accesskey="c" class="ed_button" onclick="aWP_qt.edInsertTag(<?php echo $id;?>, 8,'<?php echo $input_suffix;?>');" value="code" type="button" />
				<!--<input id="ed_quote_<?php echo $id;?>" accesskey="" class="ed_button" onclick="aWP_qt.edInsertTag(<?php echo $id;?>, 9,'<?php echo $input_suffix;?>');" value="Quote" type="button" />-->
				<!--<input type="button" id="ed_close_<?php echo $id;?>" class="ed_button" onclick="aWP_qt.edCloseAllTags(<?php echo $id;?>,'<?php echo $input_suffix;?>');" title="<?php _e('Close all open tags');?>" value="<?php _e('Close Tags');?>" />-->
			</p>
	<?php
		$quicktags = ob_get_contents();
		ob_end_clean();

		echo AWP::maybe_JS($quicktags);
		}
	}

	function clickablesmilies(){
	global $awpall,$id,$input_suffix,$wpsmiliestrans,$wp_smiliesreplace;

		if(!is_array($wpsmiliestrans))
			return;

		 if($awpall['clickablesmilies'] == 'Enabled'){
			$smiled = array();

			echo "<p id='clickablesmilies_$id'>";

				// The following code is based upon wp-grins by Alex King, http://alexking.org/projects/wordpress
				foreach ($wpsmiliestrans as $tag => $grin) {
					if(!$smiled[$grin] || strlen($tag) < strlen($smiled[$grin]))
						$smiled[$grin] = $tag;
				}

				// So other plugins can modify where smilies are held. We pick a random smilie and get its URL.
				preg_match('/(http.*?\/\/.*\/).+\.(gif|png|jpg)\'/',$wp_smiliesreplace[1],$urls);
				$url = $urls[1];

				foreach ($smiled as $grin => $tag) {
						$tag = str_replace(' ', '', $tag);
						$smiley_masked = htmlspecialchars(trim($tag), ENT_QUOTES);
					?>
						<img src="<?php echo $url.$grin;?>" alt='<?php echo $smiley_masked;?>' title="<?php echo $smiley_masked;?>" class='wp-smiley' onclick="aWP_qt.edInsertSmilie(<?php echo $id;?>, ' <?php echo $tag;?> ','<?php echo $input_suffix;?>');" />
					<?php
				}

			echo '</p>';
		}
	}

}

?>