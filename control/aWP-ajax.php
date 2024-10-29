<?php
	/*
	Plugin Name:  AWP AJAX and Effects
	Plugin URI: http://ajaxedwp.com
	Description: Adds the ability to use javascript effects. <strong>This is a control module and may not be deactivated or activated.</strong>
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/
	if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
		add_action('awp_admin_ajax',array('AWP_ajax','admin'));
		add_filter('awp_get_options',array('AWP_ajax','awp_get_options'));
	}else{
		add_action('prototype_effects', array('AWP_ajax','prototype_effects'));
		add_action('jquery_effects', array('AWP_ajax','jquery_effects'));
		//add_action('mootools_effects', array('AWP_ajax','mootools_effects'));
		add_action('prototype_js_lib', array('AWP_ajax','prototype_js_lib'));
		add_action('jquery_js_lib', array('AWP_ajax','jquery_js_lib'));
		add_action('mootools_js_lib', array('AWP_ajax','mootools_js_lib'));
		add_action('prototype_js', array('AWP_ajax','prototype_js'),10,2);
		add_action('jquery_js', array('AWP_ajax','jquery_js'),10,2);
		add_action('mootools_js', array('AWP_ajax','mootools_js'),10,2);
		add_action('sack_js', array('AWP_ajax','sack_js'),10,2);
		add_action('aWP_JS',array('AWP_ajax','JS'));
		add_action('awp_effects', array('AWP_ajax','effects'));
		add_action('awp_get_library',array('AWP_ajax','get_library'));
		add_action('pick_ajax', array('AWP_ajax','pick_ajax'));
	}

class AWP_ajax{

	function get_library(){
	global $awpall;
		$ajax = $awpall['js_library'];
		$a = apply_filters($ajax.'_js_lib',$ajax);
		$ajax = ($a)? $a : $ajax;

		if(!$awpall[no_load_library])
			wp_print_scripts($ajax);
	}

	function pick_ajax($type){
	global $awpall,$home,$awpsuffix;
		static $aWP_ajax;

		if(!is_array($aWP_ajax)){
			$aWP_ajax = AWP_ajax::get_ajax();
		}
		echo $aWP_ajax[$type];
	}

	function get_ajax(){
	global $awpall;
		$ajax = $awpall['js_library'];
		return apply_filters($ajax.'_js',$aWP_ajax);
	}

	function JS(){
	global $awpall;
			echo "\n"."\n".'/* start effects */'."\n";
			include('effects.js');
	}

	function effects(){
	global $awpall;


		if($awpall['special_effects'] == 'Enabled'){

			if($awpall[$awpall['js_library'].'_show'] && $awpall[$awpall['js_library'].'_hide']){

				do_action($awpall['js_library'].'_effects');

			}else{
				if($awpall['effects'] == 'Fade'){
					$extra = ", 'background': '$awpall[background_color]'";
				}

			?>

				AOI_eff.start(i, {'eff': '<?php echo $awpall['effects'];?>'<?php echo $extra;?>});

<?php
			}
		}else{ ?>
				if($(i).style.display === 'block'){

					$(i).style.display= 'none';

				}else {

					$(i).style.display = 'block';
				}

<?php	} ?>

	<?php
	}

	function sack_js($aWP_ajax){
		$aWP_ajax[basic] =
			"
				get_throbber();
				aWP.ajax = null;

				aWP.URL = '".AWP_AJAX."';
				if(!_d[i].url)
					_d[i].url = aWP.URL;

				aWP.ajax = new sack(_d[i].url);
				if(!_d[i].sack){
					aWP.sack = function(){_d[i].result = aWP.ajax.responseXML; if(_d[i].result) {aWP.update();}}
				}
				aWP.ajax.method = 'POST';
				aWP.ajax.onCompletion = aWP.sack;
				for (k in postobj) {
					aWP.ajax.encVar( k, postobj[k]);
				}

				aWP.ajax.runAJAX();
			";
		return $aWP_ajax;
	}

	function jquery_js($aWP_ajax){
		$aWP_ajax[basic] =
			"
				get_throbber();
				if(!_d[i].jQuery){
					aWP.jQuery = function(r){_d[i].result = r;  if(_d[i].result) {aWP.update();}}
				}

				aWP.URL = '".AWP_AJAX."';
				if(!_d[i].url)
					_d[i].url = aWP.URL;

				jQuery.ajax({
				type: 'POST',
				url: _d[i].url,
				data:  postobj,
				success:aWP.jQuery,
				async:false
				});

			";
		return $aWP_ajax;
	}

	function mootools_js($aWP_ajax){
		$aWP_ajax[basic] =
			"	if(!_d[i].mootools){
					aWP.mootools = function(text,xml){_d[i].result = xml;  if(_d[i].result) {aWP.update();}}
				}
				get_throbber();

				aWP.URL = '".AWP_AJAX."';
				if(!_d[i].url)
					_d[i].url = aWP.URL;

				var ajax = new Ajax(_d[i].url ,
					{
						method: 'post',
						data: postobj,
						onComplete:aWP.mootools

					}).request();
				ajax = null;
			";
		return $aWP_ajax;
	}

	function prototype_js($aWP_ajax){
		$aWP_ajax[basic] =
			"
				if(!_d[i].prototype){
					aWP.prototype = function(r){_d[i].result = r.responseXML;  if(_d[i].result) {aWP.update();}}
				}
				var ajax = new Ajax.Request('".AWP_AJAX."',
					{
						method: 'post',
						parameters: postobj,
						onLoading:get_throbber,
						onComplete:aWP.prototype

					});
				ajax = null;
			";
		return $aWP_ajax;
	}

	function prototype_js_lib($ajax){
	global $awpall;
		if($awpall['special_effects'] == 'Enabled' && !$awpall[no_load_library]){
			wp_print_scripts('scriptaculous-effects');
		}
	return $awpall['js_library'];
	}

	function jquery_js_lib($ajax){
	global $awpall,$home;
		if($wp_version == 2.1){
			if(file_exists($home.'/wp-includes/js/jquery.js')){
				wp_register_script('jquery', $home.'/wp-includes/js/jquery.js', false, '1');
			}else{
				return 'sack';
			}
		}
	return $awpall[js_library];
	}

	function mootools_js_lib($ajax){
	global $awpall;

		wp_register_script('mootools', WP_CONTENT_URL.'/plugins/'.AWP_BASE.'/js/mootools.js', false, '1.11');

	return $awpall[js_library];
	}

	function prototype_effects(){
	global $awpall;
	?>
		if($(i).style.display === 'block'){
			Effect.<?php echo $awpall['prototype_hide'];?>(i);
		}else{
			Effect.<?php echo $awpall['prototype_show'];?>(i);
		}
	<?php
	}

	function jquery_effects(){
	global $awpall;

		$hide_effect = explode('-',$awpall['jquery_hide']);
		$show_effect = explode('-',$awpall['jquery_show']);
	?>
		if($(i).style.display === 'block'){
			jQuery('#'+i).<?php echo $hide_effect[0];?>("<?php echo $hide_effect[1];?>");
		}else{
			jQuery('#'+i).<?php echo $show_effect[0];?>("<?php echo $show_effect[1];?>");
		}

	<?php
	}

	function mootools_effects(){
	global $awpall;
	?>
		var myFx = null;
		if($(i).style.display === 'block'){
			myFx = new Fx.Style(i, 'opacity').set(1);
			myFx = new Fx.Style(i, 'opacity').start(1,0);
			$(i).style.display = 'none';
		}else{
			myFx = new Fx.Style(i, 'opacity').set(0);
			$(i).style.display = 'block';
			myFx = new Fx.Style(i, 'opacity').set(0).start(0,1);
		}

	<?php
	}

	function admin(){
	global $awpall, $aWP;
	ob_start();
?>
<menus>
	<menu>
		<title><?php _e('AJAX Options','awp');?></title>
		<submenu>
			<desc><?php _e('What Javascript Library do you want to use?','awp');?></desc>
			<item type="radio" open="1" value="sack" global="1" name="js_library">
				<d><?php _e('Use TWSack Library','awp');?></d>
				<desc><?php _e('This library is only 5KB and is the default.','awp');?></desc>
			</item>
			<item type="radio" open="1" value="jquery" global="1" name="js_library">
				<d><?php _e('Use JQuery','awp');?></d>
				<desc><?php _e('(This library is 19KB, includes effects, and is the prefered "advanced" library.)','awp');?></desc>
			</item>
			<item type="radio" open="1" value="mootools" global="1" name="js_library">
				<d><?php _e('Use MooTools','awp');?></d>
				<desc><?php _e('(This library is 25KB, includes no effects by default.)','awp');?></desc>
			</item>
			<item type="radio" value="prototype" global="1"  name="js_library">
				<d><?php _e('Use Prototype.js','awp');?></d>
				<desc><?php _e('(This library is 55KB, is not suggested, and uses Scriptaculous for effects )','awp');?></desc>
			</item>
		</submenu>
		<submenu>
			<desc><?php _e('Advanced AJAX Options','awp');?><?php _e('Use the following at your own risk.','awp');?></desc>
			<item type="checkbox" open="1" global="1" name="no_load_library">
				<d><?php _e('Use the JS library selected above, but DO NOT load it.','awp');?></d>
			</item>
		</submenu>
		<action>awp_admin_get_ajax</action>
	</menu>

	<menu id="special_effects">
		<title><?php _e('Special Effect Options','awp');?></title>
		<name><?php _e('Special Effects','awp');?></name>
		<desc><?php _e('Would you like special effects? Without them there is an abrupt transition when new content is loaded.','awp');?></desc>
		<submenu>
		<desc><![CDATA[<?php _e('The following effects are built into the plugin. There is no other way to have effects on posts. These effects will add no extra javascript to your webpage.','awp');?>]]></desc>
			<item name="do_effect" open="1" type="select">
				<d><?php _e('Which built in Post effect do you want?','awp');?></d>
				<desc><?php _e('Slide Up scrolls the container up and down, Scroll Left pushes all of the text to the left and pulls it back to the right, Expand stretches and shrinks the text (not the container) horizontally and also vertically (if no child element has a set line height)','awp');?></desc>
				<option value="" name="<?php _e('None','awp');?>"/>
				<option value="Expand" name="<?php _e('Expand','awp');?>"/>
				<option value="SlideUp" name="<?php _e('Slide Up','awp');?>"/>
				<option value="Fade" name="<?php _e('Fade','awp');?>"/>
				<option value="ScrollLeft" name="<?php _e('Scroll Left','awp');?>"/>
			</item>
			<item type="text" name="background_color">
				<d><?php _e('What is the background color of your posts?','awp');?></d>
				<desc><?php _e('In hex format (#FFFFFF) Due to a bug in IE you must set a background color for your posts if you want to use the fade effect. This only needs to be set if your post backgrounds are not white.','awp');?></desc>
			</item>
		</submenu>

		<submenu>
		<desc><![CDATA[<?php _e('The following effect will be used for showing and hiding the comment form, comments and all show/hide combinations except for posts. This option is overruled by setting a library-specific effect below.','awp');?>]]></desc>
			<item name="effects" type="select">
				<d><?php _e('What effect would you like to use for your non-post show/hides?','awp');?></d>
				<desc><?php _e('Slide Up scrolls the container up and down, Scroll Left pushes all of the text to the left and pulls it back to the right, Expand stretches and shrinks the text (not the container) horizontally and also vertically (if no child element has a set line height)','awp');?></desc>
				<option value="" name="<?php _e('None','awp');?>"/>
				<option value="Expand" name="<?php _e('Expand','awp');?>"/>
				<option value="SlideUp" name="<?php _e('Slide Up','awp');?>"/>
				<option value="Fade" name="<?php _e('Fade','awp');?>"/>
				<option value="ScrollLeft" name="<?php _e('Scroll Left','awp');?>"/>
			</item>
		</submenu>

		<submenu>
			<desc><![CDATA[<?php _e('The following effects are only used when the JQuery AJAX Library is selected.','awp');?>]]></desc>
			<item name="jquery_show" open="1" type="select">
				<option value="" name="<?php _e('None','awp');?>"/>
				<d><?php _e('JQuery show comments/add comments effect','awp');?></d>
				<option value="slideDown-Slow" name="<?php _e('Slide Down','awp');?> <?php _e('Slow','awp');?>"/>
				<option value="slideDown-Fast" name="<?php _e('Slide Down','awp');?> <?php _e('Fast','awp');?>"/>
				<option value="slideDown-Normal" name="<?php _e('Slide Down','awp');?>"/>
				<option value="fadeIn-Slow" name="<?php _e('Fade In','awp');?> <?php _e('Slow','awp');?>"/>
				<option value="fadeIn-Fast" name="<?php _e('Fade In','awp');?> <?php _e('Fast','awp');?>"/>
				<option value="fadeIn-Normal" name="<?php _e('Fade In','awp');?>"/>
				<option value="show-Slow" name="<?php _e('Show','awp');?> <?php _e('Slow','awp');?>"/>
				<option value="show-Fast" name="<?php _e('Show','awp');?> <?php _e('Fast','awp');?>"/>
				<option value="show-Normal" name="<?php _e('Show','awp');?>"/>
			</item>
			<item name="jquery_hide" type="select">
				<d><?php _e('and hide effect','awp');?></d>
				<option value="" name="<?php _e('None','awp');?>"/>
				<option value="slideUp-Slow" name="<?php _e('Slide Up','awp');?> <?php _e('Slow','awp');?>"/>
				<option value="slideUp-Fast" name="<?php _e('Slide Up','awp');?> <?php _e('Fast','awp');?>"/>
				<option value="slideUp-Normal" name="<?php _e('Slide Up','awp');?>"/>
				<option value="fadeOut-Slow" name="<?php _e('Fade Out','awp');?> <?php _e('Slow','awp');?>"/>
				<option value="fadeOut-Fast" name="<?php _e('Fade Out','awp');?> <?php _e('Fast','awp');?>"/>
				<option value="fadeOut-Normal" name="<?php _e('Fade Out','awp');?>"/>
				<option value="hide-Slow" name="<?php _e('Hide','awp');?> <?php _e('Slow','awp');?>"/>
				<option value="hide-Fast" name="<?php _e('Hide','awp');?> <?php _e('Fast','awp');?>"/>
				<option value="hide-Normal" name="<?php _e('Hide','awp');?>"/>
			</item>

		</submenu>

		<submenu>
			<desc><![CDATA[<?php _e('The following effects are only used when the Prototype AJAX Library is selected.','awp');?>]]></desc>
			<item name="prototype_show" open="1" type="select" d="<?php _e('Scriptaculous show comments/add comments effect','awp');?>">
				<option value="" name="<?php _e('None','awp');?>"/>
				<option value="SlideDown" name="<?php _e('Slide Down','awp');?>"/>
				<option value="BlindDown" name="<?php _e('Blind Down','awp');?>"/>
				<option value="Appear" name="<?php _e('Appear','awp');?>"/>
				<option value="Grow" name="<?php _e('Grow','awp');?>"/>
			</item>

			<item name="prototype_hide" type="select" d="<?php _e('Hide effect','awp');?>">
				<option value="" name="<?php _e('None','awp');?>"/>
				<option value="SlideUp" name="<?php _e('Slide Up','awp');?>"/>
				<option value="BlindUp" name="<?php _e('Blind Up','awp');?>"/>
				<option value="Fade" name="<?php _e('Fade out','awp');?>"/>
				<option value="Shrink" name="<?php _e('Shrink','awp');?>"/>
				<option value="Puff" name="<?php _e('Puff','awp');?>"/>
				<option value="SwitchOff" name="<?php _e('Switch Off','awp');?>"/>
				<option value="DropOut" name="<?php _e('Drop Out','awp');?>"/>
				<option value="Squish" name="<?php _e('Squish','awp');?>"/>
				<option value="Fold" name="<?php _e('Fold','awp');?>"/>
			</item>
		</submenu>

		<action>awp_admin_get_effects</action>
	</menu>
</menus>
<?php
		$menu =	 ob_get_contents();
		ob_end_clean();

		do_action('awp_build_menu',$menu);
	}

	function awp_get_options($i){
		$i[selects][] = 'special_effects';
		$i[radios][] = array('js_library','sack');
		$i[selects][] = 'effects';
		$i[checkboxes][] = 'no_load_library';
		return $i;
	}
}
?>