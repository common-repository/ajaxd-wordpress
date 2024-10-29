<?php
	/*
	Plugin Name: Use Default Commentform
	Plugin URI: http://ajaxedwp.com
	Description: If you want to use your theme's default comment template, this module attempts to modify it to add AJAX features. You must have comment form module and your ajax library set to jQuery. <em>This module either works or it doesn't.</em> This module does not add quicktag support or support for other features.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/
	if(AWP::enabled('commentform') && $awpall['js_library'] == 'jquery'){
		add_filter('awp_ajax_comments_actions', array('AWP_defaultcomform','update_default_form'));
		add_filter('comment_form', array('AWP_defaultcomform','change_default_form'));
	}

class AWP_defaultcomform {

	function update_default_form($actions){
		$actions[] = 'setTimeout("try{convertform();}catch(e){}",2000);';
		return $actions;
	}

	function change_default_form(){
	global $id,$aWP,$awpall;
		$aWP[nomaybejs] = 1;

		$onsubmit = apply_filters('awp_commentform_on_submit',$on_submit);
/*
		ob_start();
			do_action('awp_commentform_before_comment');
		$before = str_replace(array("'","\n"),array("\'",''),ob_get_contents());
		ob_end_clean();

		ob_start();
			do_action('awp_commentform_after_comment');
		$after = str_replace(array("'","\n","\t"),array("\'",''),ob_get_contents());
		ob_end_clean();

		ob_start();
			do_action('awp_commentform_before_submit');
		$submit = str_replace(array("'","\n","\t"),array("\'",''),ob_get_contents());
		ob_end_clean();
		jQuery('#comment').before('<?php echo $before;?>');
		jQuery('#comment').after('<?php echo $after;?>');
		jQuery('#submit').before('<?php echo $submit;?>');*/
	?>
		<script type="text/javascript">
				function convertform(){
					jQuery('#comment').parents('form').attr({'id': "awpsubmit_commentform_<?php echo $id;?>"});
					jQuery('#comment').after('<span id="comment_result_<?php echo $id;?>" style="float:right; height:1em; color:red;"></span>');

<?php  if(AWP::enabled('inlinecomments') && $onsubmit){ ?>
					jQuery('#awpsubmit_commentform_<?php echo $id;?>').attr({'onsubmit' : "<?php echo $onsubmit;?>"});
<?php } ?>
					jQuery('#awpsubmit_commentform_<?php echo $id;?>').after('<div id="awpcommentform_<?php echo $id;?>"></div>');
					jQuery("#awpsubmit_commentform_<?php echo $id;?>").appendTo("#awpcommentform_<?php echo $id;?>");
					jQuery("#submit").attr({'id': "submit_commentform_<?php echo $id;?>"});
				}
			jQuery(document).ready(function(){convertform()});
		</script>
<?php
		$aWP[nomaybejs] = 0;
	}
}

?>
