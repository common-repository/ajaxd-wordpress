<?php
/*
Plugin Name: reCAPTCHA AJAXed WordPress Module
Plugin URI: http://ajaxedwp.com
Description: Integrates a reCAPTCHA with wordpress, and has been modified to integrate with aWP. You may not run both versions at the same time. The full version can be found <a href="http://recaptcha.net/plugins/wordpress">here</a>; however, any issues with this module are not the fault of the original authors.
Version: 2.6
Author: Originally by Ben Maurer & Mike Crawford
Email: support@recaptcha.net
Author URI: http://bmaurer.blogspot.com
*/
	require_once (dirname(__FILE__) . '/recaptchalib.php');
	global $recaptcha_opt;
	$recaptcha_opt = get_option('plugin_recaptcha');
	define ("RECAPTCHA_WP_HASH_SALT", "b7e063dvdv8d85f5d7f3694f68e944136d62");

	if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){
		add_action('awp_admin_commentform',array('AWP_recaptcha','admin'));
		add_filter('awp_get_options',array('AWP_recaptcha','awp_get_options'));
	}elseif(AWP::enabled('recaptcha')){
		add_filter('preprocess_comment', array('AWP_recaptcha','check_comment'),0);
		add_filter('awp_ajax_submit_commentform_actions', array('AWP_recaptcha','comment_form_actions'));
		add_filter('awp_ajax_commentform_actions', array('AWP_recaptcha','comment_form_inline'));
		add_action( 'comment_form', array('AWP_recaptcha','comment_form'));
	}

class AWP_recaptcha{

	function get_html($recaptcha_error){
		global $recaptcha_opt;
		return recaptcha_get_html($recaptcha_opt['pubkey'], $recaptcha_error);
	}

	function comment_form_actions($actions){

		$actions[] = "Recaptcha.reload();";

	return $actions;
	}

	function hash_comment ($id)
	{
		global $recaptcha_opt;
		if (function_exists('wp_hash') ) {
			return wp_hash (RECAPTCHA_WP_HASH_COMMENT . $id);
		} else {
			return md5 (RECAPTCHA_WP_HASH_COMMENT . $recaptcha_opt['privkey'] . $id);
		}
	}

	/**
	 *  Embeds the reCAPTCHA widget into the comment form.
	 *
	 */
	function comment_form() {
	global $id,$awpall;

		if(defined('AWP_AJAXED')){
			echo "<p id='recaptcha_$id'></p>";
		}else{
		//modify the comment form for the reCAPTCHA widget
		$recaptcha_js_opts = <<<OPTS
			<script type='text/javascript'>
				var RecaptchaOptions = { theme : '$awpall[recap_color]', lang : '$awpall[recap_lang]'};
			</script>
OPTS;

		echo $recaptcha_js_opts .  AWP_recaptcha::get_html($_GET['rerror']) . $comment_string;
		}
	}

	function comment_form_inline($actions){
	global $id,$recaptcha_opt, $awpall;
		$actions[] = "setTimeout(\"var s = document.createElement('script'); s.src = '".RECAPTCHA_API_SERVER. "/js/recaptcha_ajax.js';document.getElementById('recaptcha_$id').parentNode.appendChild(s);\",1000);";
		$actions[] = "setTimeout(\"Recaptcha.create('$recaptcha_opt[pubkey]', 'recaptcha_$id', {theme : '$awpall[recap_color]', lang : '$awpall[recap_lang]'});\",1500);";
	return $actions;
	}

	function show_captcha_for_comment () {
	        global $user_ID;
	        return true;
	}

	/**
	 * Checks if the reCAPTCHA guess was correct and sets an error session variable if not
	 * @param array $comment_data
	 * @return array $comment_data
	 */
	function check_comment($comment_data) {

		global $user_ID, $recaptcha_opt;
		global $recaptcha_saved_error;

		if ( $comment_data['comment_type'] == '' ) { // Do not check trackbacks/pingbacks

			$challenge = $_POST['recaptcha_challenge_field'];
			$response = $_POST['recaptcha_response_field'];

			$recaptcha_response = recaptcha_check_answer ($recaptcha_opt ['privkey'], $_SERVER['REMOTE_ADDR'], $challenge, $response);
			if ($recaptcha_response->is_valid) {
				return $comment_data;
			}
			else {

				$die = '';
				if(!defined('AWP_AJAXED')){
					$die = '<p>'.__('Please hit back on your browser and try again.','awp').'</p> <p>'. __('You may want to copy your comment to ensure it is saved.','awp').'</p> <textbox>'.$_POST[comment].'</textbox>';
				}

				wp_die(printf(__('ReCaptcha was incorrect. Please try again. %s','awp'), $die));
			}
		}
		return $comment_data;
	}

	function blog_domain ()
	{
		$uri = parse_url(get_settings('siteurl'));
		return $uri['host'];
	}


	function admin(){
	global $aWP, $awpall;


	ob_start();
?>

<menus>
 	<menu id="recaptcha">
		<name><?php _e('AJAXed WordPress reCAPTCHA','awp');?></name>
		<title><?php _e('reCAPTCHA Module.','awp');?></title>
		<desc><![CDATA[<?php _e('reCAPTCHA asks commenters to read two words from a book. One of these words proves that they are a human, not a computer. The other word is a word that a computer could not read. Because the user is known to be a human, the reading of that word is probably correct. So you do not get comment spam, and the world gets books digitized. Everybody wins! For details, visit the <a href="http://recaptcha.net/">reCAPTCHA website</a>.','awp');?>]]></desc>
		<submenu>
			<desc><![CDATA[<?php $link = '<a href="'.recaptcha_get_signup_url (AWP_recaptcha::blog_domain (), 'wordpress').'" target="0"> reCAPTCHA.</a>';?><?php printf(__('reCAPTCHA requires an API key, consisting of a "public" and a "private" key. You can sign up for a free API key for %s .', 'awp'), $link);?> <br/> <?php _e('This key is stored separate from other aWP options, and uses the same administration panel as the main WordPress reCAPTCHA plugin under the options menu.','awp');?>]]></desc>

			<item name="recap_color" type="select" d="<?php _e('Form Color','awp');?>">
				<option value="clean" name="<?php _e('Clean','awp');?>"/>
				<option value="red" name="<?php _e('Red','awp');?>"/>
				<option value="white" name="<?php _e('White','awp');?>"/>
				<option value="blackglass" name="<?php _e('Black Glass','awp');?>"/>
			</item>

			<item name="recap_lang" type="select" d="<?php _e('Form Language','awp');?>">
				<option value="en" name="English"/>
				<option value="nl" name="Nederlands"/>
				<option value="fr" name="Français"/>
				<option value="de" name="Deutsch"/>
				<option value="pt" name="Português"/>
				<option value="ru" name="Russian"/>
				<option value="es" name="Español"/>
				<option value="tr" name="Türkçe"/>
			</item>
		</submenu>
	</menu>
</menus>
<?php


	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);
?>
	<?php
	}

	function awp_get_options($j){
		$j[selects][] = 'recaptcha';

		return $j;
	}
} // end class

if(!function_exists('recaptcha_wp_add_options_to_admin')){
	function recaptcha_wp_add_options_to_admin() {
		if (function_exists('add_options_page')) {
		add_options_page('reCAPTCHA', 'reCAPTCHA', 8, plugin_basename(__FILE__), 'recaptcha_wp_options_subpanel');
		}
	}

	function recaptcha_wp_options_subpanel() {

		$optionarray_def = array(
					'pubkey'	=> '',
					'privkey' 	=> '',
					);

		add_option('plugin_recaptcha', $optionarray_def, 'reCAPTCHA Options');

		/* Check form submission and update options if no error occurred */
		if (isset($_POST['submit']) ) {
			$optionarray_update = array (
				'pubkey'	=> $_POST['recaptcha_opt_pubkey'],
				'privkey'	=> $_POST['recaptcha_opt_privkey'],
			);
			update_option('plugin_recaptcha', $optionarray_update);
		}

		/* Get options */
		$optionarray_def = get_option('plugin_recaptcha');


	?>

	<!-- ############################## BEGIN: ADMIN OPTIONS ################### -->
	<div class="wrap">


		<h2><?php _e('reCAPTCHA Options','awp');?></h2>
		<?php _e('reCAPTCHA asks commenters to read two words from a book. One of these words proves that they are a human, not a computer. The other word is a word that a computer could not read. Because the user is known to be a human, the reading of that word is probably correct. So you do not get comment spam, and the world gets books digitized. Everybody wins! For details, visit the <a href="http://recaptcha.net/">reCAPTCHA website</a>.','awp');?>

		<form name="form1" method="post" style="margin: auto; width: 25em;" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . plugin_basename(__FILE__); ?>&updated=true">


		<!-- ****************** Operands ****************** -->
		<fieldset class="options">
			<legend><?php _e('reCAPTCHA Key','awp');?></legend>
			<p>
				<?php $link = '<a href="'.recaptcha_get_signup_url (AWP_recaptcha::blog_domain (), 'wordpress').'" target="0"> reCAPTCHA.</a>';?>
				<?php printf(__('reCAPTCHA requires an API key, consisting of a "public" and a "private" key. You can sign up for a free API key for %s .', 'awp'), $link);?>
			</p>
			<label style="font-weight:bold" for="recaptcha_opt_pubkey"><?php _e('Public Key','awp');?>:</label>
			<br />
			<input name="recaptcha_opt_pubkey" id="recaptcha_opt_pubkey" size="40" value="<?php  echo $optionarray_def['pubkey']; ?>" />
			<label style="font-weight:bold" for="recaptcha_opt_privkey"><?php _e('Private Key','awp');?>:</label>
			<br />
			<input name="recaptcha_opt_privkey" id="recaptcha_opt_privkey" size="40" value="<?php  echo $optionarray_def['privkey']; ?>" />

		</fieldset>


		<div class="submit">
			<input type="submit" name="submit" value="<?php _e('Update Options', 'awp') ?> &raquo;" />
		</div>

		</form>

		<p style="text-align: center; font-size: .85em;">&copy; Copyright 2007&nbsp;&nbsp;<a href="http://recaptcha.net">reCAPTCHA</a></p>

	</div> <!-- [wrap] -->
	<!-- ############################## END: ADMIN OPTIONS ##################### -->


	<?php


	}


	/* =============================================================================
	Apply the admin menu
	============================================================================= */

	add_action('admin_menu', 'recaptcha_wp_add_options_to_admin');

	}

	if ( !($recaptcha_opt ['pubkey'] && $recaptcha_opt['privkey'] ) && !isset($_POST['submit']) ) {
			function recaptcha_warning() {
			$path = plugin_basename(__FILE__);
					echo "
					<div id='recaptcha-warning' class='updated fade-ff0000'><p><strong>reCAPTCHA is not active</strong> You must <a href='options-general.php?page=" . $path . "'>enter your reCAPTCHA API key</a> for it to work</p></div>
					<style type='text/css'>
					#adminmenu { margin-bottom: 5em; }
					#recaptcha-warning { position: absolute; top: 7em; }
					</style>
					";
			}
			add_action('admin_footer', 'recaptcha_warning');
			return;
	}

?>
