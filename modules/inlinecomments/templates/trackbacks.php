<?php

	if(!$awpall['trackback_all_tag']){
		$awpall['trackback_all_tag'] .= '<h3 style="text-align:center;">';
		$awpall['trackback_all_tag'] .= __('Trackbacks and Pingbacks','awp');
		$awpall['trackback_all_tag'] .= '</h3>';
		$awpall['trackback_all_tag'] .= $awpall['comment_all_tag'];
	}

	if(!$awpall['trackback_all_tag_end'])
		$awpall['trackback_all_tag_end'] = $awpall['comment_all_tag_end'];


# Add content here to be before the list of trackbacks.

	echo $awpall['trackback_all_tag'];

# Add content here to be the first item in the list of trackbacks.

	foreach ($comments as $comment){
		if ('trackback' == $comment->comment_type || 'pingback' == $comment->comment_type) {
			include(AWP::template('inlinecomments','trackback'));
		}
	}

# Add content here to be the last item in the list of trackbacks.

	echo $awpall['trackback_all_tag_end'];

# Add content here to be after the list of trackbacks.

?>