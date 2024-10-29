<?php
/*
	Plugin Name: qTranslate Support
	Plugin URI: http://ajaxedwp.com
	Description: Allows you to use qTranslate bbcodes in link texts. E.G. [lang_en]Show one Comment[/lang_en][lang_de]Zeige ein Kommentar[/lang_de] Only tested under qTranslate 1.1.4.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

	//	add_action('init',array('AWP_qTranslate','plugins_loaded'));
	if( function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')){
		add_filter('awp_text_filters', array('AWP_qTranslate','qTranslate'));
		add_filter('awp_link_array',array('AWP_qTranslate','awp_link_array'),10,2);
	}
$awp_init[] = 'AWP_qTranslate';
Class AWP_qTranslate{

	function init(){
	global $q_config;
		if($_REQUEST['lang']){
			$q_config['language'] = $_REQUEST['lang'];
		} 

	}

	function awp_link_array($ops, $type){
	global $q_config;
		$ops[doit] .= ", 'lang': '$q_config[language]'";

		return $ops;
	}

	function qTranslate($link_text){

		$link_text = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($link_text);

	return $link_text;
	}
}

?>