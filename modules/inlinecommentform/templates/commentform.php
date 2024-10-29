<?php
// Do not remove
	if (!empty($post->post_password)) { // if there's a password
		if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
			?>

			<p class="nocomments"><?php _e('This post is password protected. Enter the password to comment.','awp');?></p>

			<?php
			return;
		}
	}

	if ('open' == $post->comment_status) {
		if (! is_user_logged_in() && get_option('comment_registration')){
			$link = '<a href="' . get_option('siteurl') . '/wp-login.php">' . __('Login') . '</a>';
			if ( get_option('users_can_register') )
				$link .= ' or <a href="' . get_option('siteurl') . '/wp-login.php?action=register">' . __('Register') . '</a>';

			printf(__('Sorry, you must %s to post a comment.', 'awp'), $link);	return;
		}
	}

	sanitize_comment_cookies();
	$commenter = wp_get_current_commenter();
	extract($commenter, EXTR_SKIP);
/**
* Do not remove above.
**/
?>

<?php
/**
Everything below this block is editable just like any other add comment form; however, the following elements and IDs are required:
<form id="awpsubmit_commentform_<?php echo $id;?>"> //The form itself.
<span id="comment_result_<?php echo $id;?>"> // will be updated with results.
<input type="hidden" name="comment_post_ID"  value="<?php echo $id; ?>" /> //Post ID
<input name="submit" type="submit" id="submit_commentform_<?php echo $id;?>"> // Submit button.

**/
?>

<hr/>

<?php

global $input_suffix;
$input_suffix = apply_filters('awp_input_suffix','');
/*
If this comment form will be shown on the index page or an archive page.
To ensure XHTML validity, always give new elements an ID that ends with "<?php echo $input_suffix;?>".
If the form will always appear on a single post, you may remove the following line.
*/
?>

<h3><?php _e('Leave a reply', 'awp'); ?></h3>

<div style="float:right;width:30px;border:0;"><?php do_action('awp_commentform_quickclose');?></div>


<?php do_action('awp_commentform_before_form'); //Leave for AWP features?>
<a id="respond"></a>
<form
	id="awpsubmit_commentform_<?php echo $id;?>"
	class="comment_form"
	action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php"
	method="post"
	onsubmit=" <?php echo apply_filters('awp_commentform_on_submit',$on_submit); //Leave for AWP features?>"
>

	<?php do_action('awp_commentform_before_userinfo'); //Leave for AWP features?>

	<?php if ( $user_ID ) { global $user_identity;?>

		<p><?php echo sprintf(__('Logged in as <a href="%1$s/wp-admin/profile.php">%2$s</a>', 'awp'), get_option('siteurl'), $user_identity); //sprintf for Japanese support. ?></p>

	<?php } else { ?>

		<p><input type="text" name="author" id="author<?php echo $input_suffix;?>" value="<?php echo $comment_author; ?>" size="22"/>
		<label for="author<?php echo $input_suffix;?>"><small><?php _e('Name');?> <?php if ($req) _e('(required)'); ?></small></label></p>

		<p><input type="text" name="email" id="email<?php echo $input_suffix;?>" value="<?php echo $comment_author_email; ?>" size="22"/>
		<label for="email<?php echo $input_suffix;?>"><small><?php _e('Mail');?> <?php _e('(will not be published)');?> <?php if ($req) _e('(required)'); ?></small></label></p>

		<p><input type="text" name="url" id="url<?php echo $input_suffix;?>" value="<?php echo $comment_author_url; ?>" size="22"/>
		<label for="url<?php echo $input_suffix;?>"><small><?php _e('Website');?></small></label></p>

	<?php } ?>

	<?php do_action('awp_commentform_after_userinfo'); //Leave for AWP features?>

	<?php do_action('awp_commentform_before_comment'); //Leave for AWP features?>

	<p>
		<label for="comment<?php echo $input_suffix;?>"><?php _e('Comment')?>:</label>
		<textarea class="commentbox" name="comment" id="comment<?php echo $input_suffix;?>" style="width:95%;" rows="10" cols="10" <?php do_action('awp_commentform_comment');  //Leave for AWP features?> ></textarea>
	</p>


	<?php do_action('awp_commentform_after_comment'); //Leave for AWP features?>

	<?php do_action('comment_form', $post->ID); //Leave for WordPress features?>

	<p id="comment_result_<?php echo $id;?>" style="float:right; height:1em; color:red;"></p>
	<p>
		<input type="hidden" name="comment_post_ID"  value="<?php echo $id; ?>" />

		<?php do_action('awp_commentform_before_submit'); //Leave for AWP features?>

		<input name="submit" type="submit" class="submit" id="submit_commentform_<?php echo $id;?>" value="<?php _e('Submit Comment');?>" />

	</p>


</form>

<?php do_action('awp_commentform_after_form'); //Leave for AWP features?>

