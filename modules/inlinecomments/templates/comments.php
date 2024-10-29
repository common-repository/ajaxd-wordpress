<?php
$counts = AWP::get_true_comment_count($id, false); //Array comment count and trackback count;

if($counts[comments]){ // If there are any comments.

	if($depth <= 1){ //if this is a top-level comment
# Add content here to be before the list of comments.
		echo '<a id="comments"></a>';
		echo $awpall['comment_all_tag'];

# Add content here to be the first item in the list of comments.

	}elseif($depth <= $awpall['comment_threaded_depth']){ //if this is a reply

		echo $awpall['comment_reply_tag'];

	}

// Comment loops
	foreach ($comments as $comment){

		if (('trackback' == $comment->comment_type || 'pingback' == $comment->comment_type) && $awpall['split_comments'] == 1) {
				include(AWP::template('inlinecomments','trackback'));
		}elseif('' == $comment->comment_type || 'comment' == $comment->comment_type){ //Fix by claus, http://bakkelund.dk/
				include(AWP::template('inlinecomments','comment'));
		}else{
			$trackback++;
		}

	}

//end comment loops

	if($depth <= 1){
	# Add content here to be the last item in the list of comments.

		echo $awpall['comment_all_tag_end'];

	# Add content here to be after the list of comments.

	}elseif($depth <= $awpall['comment_threaded_depth']){
		echo $awpall['comment_reply_tag_end'];
	}


}else{// End has comments.
	$trackback = 1;
}

/**
If post has trackbacks, trackbacks are split, and there are trackbacks at this depth.
**/

if($awpall['split_comments'] == 2 && $counts[trackbacks] && $trackback){
	$cn = 0;
	reset($comments);
	include(AWP::template('inlinecomments','trackbacks'));
}

?>