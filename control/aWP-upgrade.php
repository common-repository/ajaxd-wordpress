<?php
	/*
	Plugin Name: AWP Upgrade
	Plugin URI: http://ajaxedwp.com
	Description: Processes upgrades based.
	Author: Aaron Harun
	Version: 1.0
	Author URI: http://anthologyoi.com/
*/

add_filter('awp_startup',array('AWP_upgrade','init'));

class AWP_upgrade{

	function init($options){
	global $aWP;
		$now = $aWP[version]; //Version of the current file.
		$last = get_option('awp_version');

		if($now == $last || $last == '')
			return $options;

		if($last < 1180){
			add_option('awp_version',$aWP[version], 'The current AWP version.');
			add_option('awp_messages','', 'Important messages regarding AWP.');


			$options['commentform_input_suffix'] = '_%ID';

			$messages[] = __('Threaded comments is now its own module, and it no longer requires inline comments for functionality.','awp');
			if($options['comment_threaded']){

				unset($options['comment_threaded']);
				$options['threadedcomments'] = 'Enabled';
				$awp_mods = get_option('awp_mods');
				$awp_mods[] = 'threaded_comments.php';
				sort($awp_mods);
				update_option('awp_mods', $awp_mods);

				$messages[] = __('Threaded comments module has been activated.','awp');
			}

			if($awpall[give_credit])
				$messages[] = __('"AJAXed With AWP" link is now only displayed on the homepage.','awp');

			$messages[] = __('Comment counts now return actual comment count. New tags have been added for trackbacks and total "comments."','awp');
		}else{
			$messages = get_option('awp_messages');
		}

		if($last < 1192){
			$messages[] = __('Version 1.19.2 fixes bugs with AJAX Nav and Inline Posts module.','awp');
			$messages[] = __('Admin panel tabs that show empty panels are now hidden after load.','awp');
		}

		if($last < 1195){
			$messages[] = __('Preview comment module now works with Rich Text Editor.','awp');
		}

		if($last < 1195){
			add_option('awp_news',array('last' => time()-4000, 'id' => 799177830), 'Time that AWP news was last checked and id of the article .');
			$messages[] = __('News from the AWP twitter stream is now automatically updated here.','awp');

		}
		if($last < 1201){
			if($awpall['lightbox'] == 'Enabled'){
				unset($awpall['lightbox']);
				$options['comp_'.$awpall[lightbox_type]] = 1;
				unset($awpall[lightbox_type]);
				$awp_mods = get_option('awp_mods');
				$awp_mods[] = 'compatabilities.php';
				sort($awp_mods);
				update_option('awp_mods', $awp_mods);
				$messages[] = __('Threaded comments module has been activated.','awp');
			}
		}

		if($last < 1232){
			update_option('awp_news',array('last' => time()-4000, 'id' => 1221765925));
		}

		if($last < 1290){
			$messages[] = __('If you use the AJAX Nav module to AJAX the entire website, disable it and enable the AJAX Navigator module.','awp');
			$messages[] = __('The AJAX Navigator module is far more advanced and works far better than the AJAX Nav module.','awp');
			$options[default_css] = <<<block

/* Comment Stuff */
.awpcomments ol.comments{
	padding-left:0;
	margin-left:0;
	list-style-type:none !important;
}

.awpcomments ol.comments * > ol.reply{
	list-style-type:none !important;
	padding-left:1.3em;
	margin-left:0;
}

.awpcomments * ol.reply{
	list-style-type:none !important;
}

.awpcomments * .authorcomment {
	border:1px solid #c0c0c0;
	line-height:1.5em;
	margin:3px;
	padding:4px;
}

.awpcomments * .commentbar {
	display:block;
	margin:0 !important;
	padding:5px 5px 10px 5px !important;
	font-weight:400;
	text-align:left;
}

.awpcomments * .commentbar cite{
	font-style:normal;
}

.comment_form input[type=text], .comment_form textarea, .input {
	font-size: 1.1em;
	padding: 3px;
	border: 2px solid #B3B3B3 !important;
}

.comment_form input[type=text]:focus, .comment_form  textarea:focus, .input:focus {
	background: #fff;
	color: #333;
	border: 2px solid #B3B3B3 !important;
}

.preview_comment{
	border: 2px solid #B3B3B3 !important;
	padding-top: 10px;
}
.comment_form input[type=text] {
	width: 45%;
	margin: 5px 5px 1px 0;
}

.comment_form textarea {
	height: 250px;
	width: 95% !important;
	margin-right:4% !important;
	font-size: 1.2em;
}

.ed_button, .submit, .comment_form input[type=submit],.comment_form input[type=button] {
	border:1px solid #B2B2B2;
	font-size:1em;
	margin-right:2px;
	padding:3px;
	width:auto;
}

.wp-smiley{
	vertical-align:middle;
	border:0;
}
.bigthrobber{
	right:30%;
	position:absolute;
	z-index:100;
}


block;
		}
		//$messages[] = __('','awp');
		if($messages){
			$messages[] = time();
			update_option('awp_messages',$messages);
		}

		$options[last_modified] = time();

		update_option('awp_version',$now);
		update_option('awpoptions',$options);

		
		include_once(ABSPATH . PLUGINDIR . '/'.AWP_BASE  . '/control/aWP-news.php');
		AWP_news::init();

		return $options;
	}



}

?>