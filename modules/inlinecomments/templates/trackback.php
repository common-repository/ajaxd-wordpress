<?php
	if($depth == 1){
		$cn++;
		$alt = ($cn % 2 == 0) ? 'alt' : '';
	}else{
		$alt= '';
	}
?>
	<?php echo "\n\n".str_replace(array('%ID','%alt','%auth'),array($comment->comment_ID,$alt,$auth),$awpall['comment_tag']); /* Tag before each comment. */ ?>

			<?php echo $awpall['comment_title_tag'];?>

				<?php if ('trackback' == $comment->comment_type){
					printf(__('Trackback from %s', 'awp'), get_comment_author_link());
				} elseif ( 'pingback' == $comment->comment_type) {
					printf(__('Pingback from %s','awp'), get_comment_author_link());
				}?>

			<?php echo $awpall['comment_title_tag_end'];?>

			<?php comment_text();?>

		<?php echo $awpall['comment_tag_end'];?>
