<?php
// This entire file may be modified.
	if($depth == 1){
		$cn++;
		$alt = ($cn % 2 == 0) ? 'alt' : '';
	}else{
		$alt= '';
	}
	$auth = ($comment->comment_author_email == get_the_author_email()) ? 'authorcomment' : '';
?>

	<?php echo "\n\n".str_replace(array('%ID','%alt','%auth'),array($comment->comment_ID,$alt,$auth),$awpall['comment_tag']); /* Tag before each comment. */ ?>

		<?php if($auth){ /* If author comment */ ?>
			<!--<div class="<?php echo $auth; ?>">-->
		<?php } ?>


		<?php echo $awpall['comment_title_tag']; /*Meta bar*/ ?>

			<?php //The sprintf is for language support.?>
			<?php echo sprintf(__('%1$s posted the following on %2$s at %3$s.','awp'),get_comment_author_link(), get_comment_date(),get_comment_time());?>

			<?php edit_comment_link(__('- Edit','awp'),'',''); ?>

		<?php echo $awpall['comment_title_tag_end']; /*Meta bar*/ ?>
		<?php if(function_exists('get_avatar')){ ?> <span style="float:right"> <?php echo get_avatar(get_comment_author_email(),25,'identicon');?> </span> <?php } ?>

		<?php do_action('awp_inlincomments_beforecommenttext'); // Leave this for AWP features ?>

			<?php if($comment->comment_approved == 0){?>

				<p>
					<em> <?php _e('This comment is awaiting moderation.','awp');?> </em>
				</p>

			<?php } ?>

			<?php comment_text(); ?>

		<?php do_action('awp_inlincomments_aftercommenttext');  // Leave this for AWP features?>

		<?php if($auth){ /* If author comment */?>
			<!--</div>-->
		<?php } ?>

		<?php /*If you do not use the same end tag as what is set in the ADmin panel, set it here to avoid invalid XHTML.*/?>

		<?php //$awpall['comment_tag_end'] = ''; ?>

		<?php $no_tag = apply_filters('awp_inlinecomments_end', $depth); // Leave this for AWP features ?>

	<?php if(!$no_tag){// leave this in. If there is a $no_tag do not show the ending tag.?>

		<?php echo $awpall['comment_tag_end']; /* Tag after each comment. *//* Yay Double Negative*/ ?>

	<?php } ?>