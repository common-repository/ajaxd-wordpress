<?php

	global $awpall, $aWP;

	if( $aWP[simplecomments] == 1){

			do_action('awp_simple_comments_first');

			echo '<!--aWP Simple Comments-->';

			do_action('awp_comments');

			do_action('awp_simple_comments_last');

	}else{

		die(__('Do not load this file directly.','awp'));

	}
?>