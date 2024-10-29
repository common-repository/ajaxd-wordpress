<?php
	/*
	Plugin Name: Threaded Comments
	Plugin URI: http://ajaxedwp.com
	Description: This threaded comments attempts to automatically thread comments without making modifications to your theme. It requires more resources to function.
	Author: Aaron Harun
	Version: 1.0
	AWP Release: 1.0
	Author URI: http://anthologyoi.com/
*/

$awp_init[] = 'AWP_threadedcomments';
register_activation_hook(__file__,array('AWP_threadedcomments','set_defaults'));

class AWP_threadedcomments {

	function init(){
	global $awpall,$AWP_inlinecomments;

		if($awpall['threadedcomments'] == 'Enabled'){
			/*Threaded Comments Functions*/
			add_action('delete_comment', array(&$this,'delete_parent'));
			add_filter('preprocess_comment', array(&$this,'set_parent'));
			add_action('comment_post', array(&$this,'get_parent'));
			//add_action('wp_set_comment_status', array(&$this,'delete_parent'));
		}

		if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false){

			add_action('awp_admin_comments',array(&$this,'admin'));
			add_filter('awp_get_options',array(&$this,'awp_get_options'));

		}elseif($awpall['threadedcomments'] == 'Enabled'){

			if(!$AWP_inlinecomments || ($awpall[simple_threading] == 1 && (!$awpall[simple_comments] || $awpall['comment_template'] != 1))){
				add_filter('comment_text', array(&$this,'add_children'),15);
				add_filter('comments_array', array(&$this,'filter_comments'));
			}else{
				remove_action('awp_print_comments',array($AWP_inlinecomments,'the_comments'));
				add_action('awp_print_comments',array(&$this,'start_thread'));
			}
				/*internal functions*/
				add_action('awp_inlincomments_aftercommenttext', array(&$this,'thread_links'));
				add_filter('awp_inlinecomments_end', array(&$this,'thread_next'));


			if(AWP::enabled('inlinecomments')){
				add_action('awp_ajax_type_comment_children', array(&$this, 'comment_children'));
			}

			add_action('comment_form',array(&$this,'threaded_comments'));

		}

	}

	function add_children($text){
	global $comment, $awpall,$id;
		static $depth = array();
		$text .= '<p>'.$this->links($comment->comment_ID,true).'</p>';
		$comments = $this->comments($comment->comment_ID);

		if(!$comments)
			return $text;

		$depth[$id]++;
		ob_start();
			include(TEMPLATEPATH . '/comments.php');
			$response = ob_get_contents();
		ob_end_clean();

		if(stripos($response,'<!--AWP_inlinecomments--><') && stripos($response,'><!--AWP_inlinecomments-->')){
			preg_match('@((\<\!--AWP_inlinecomments[^>]*--\>\<[^>]*\>)([\S\s]*)(\<\/[^>]*\>\<\!--AWP_inlinecomments[^>]*--\>))@',$response,$children);
		}else{
			preg_match('@((\<ol[^>]*\>)([\S\s]*)(\<\/ol\>))@',$response,$children);
		}

		if($children[1]){
			if($depth[$id] < $awpall['comment_threaded_depth']){
				$text .= $children[1];
			}else{ echo $depth[$id];
				$text .= $children[3];
			}
		}

		$depth[$id]--;
		return $text;
	}

	function filter_comments($acomments){
	global $AWP_inlinecomments;
		remove_filter('comments_array', array(&$this,'filter_comments'));

		if(class_exists('AWP_inlinecomments'))
			$acomments = $AWP_inlinecomments->get_comments();

		$comments = $this->comments(0,$acomments);
		add_filter('comments_array', array(&$this,'filter_comments'));

		return $comments;
	}

	function comments($parent=0,$acomments=false){
		static $noparents = array();
		static $parents = array();

		if($acomments){
			$parents = null;
			$noparents = null;
			foreach($acomments as $comment){
				if(!$comment->comment_parent){
					$noparents[] = $comment;
				}else{
					$parents[$comment->comment_parent][] = $comment;
				}
			}
		}

		if($parent == 0)
			return $noparents;

		return $parents[$parent];
	}

	function start_thread(){
	global $AWP_inlinecomments;
		if($AWP_inlinecomments)
			$acomments = $AWP_inlinecomments->get_comments();

		$comments = $this->comments(0,$acomments);

		$this->thread();
	}

	function thread($comment_parent_id=0) {
		global $id, $comment,$awpall;
		static $depth = array();
		static $crawl_up;

		$comments = $this->comments($comment_parent_id);

		if(is_array($comments)){

			if($awpall['hide_old_child_comments'] == 1){
				if(strtotime($comment->comment_date_gmt) < (time()-(60*60*24*$awpall['old_comment_days'])) ){
					$old=1;
				}
			}

			if(($awpall['hide_child_comments'] == 1 || $old== 1) && $comment_parent_id > 0){
				echo "\n" . '<a id="awpcomment_children_link_' . $comment->comment_ID . '" class="awpcomments_link" href="' . get_permalink($id) . '#comments" onclick="aWP.doit('."{'id': '$id', 'type': 'comment_children', 'show': 'Show Replies', 'hide': 'Hide Replies', 'comment_parent': '$comment_parent_id', 'primary': 'comment_parent'}" . '); return false;" rel="nofollow">' . __('Show Replies to this Comment', 'awp') . '</a>';

				//echo "\n".'<div id="awpcomment_children_'.$comment->comment_ID.'" class="awpcomments" ></div>';

				echo str_replace('>','id="awpcomment_children_'.$comment->comment_ID.'" style="display:none;">',$awpall['comment_reply_tag']).$awpall['comment_reply_tag_end'];

			}else{
   				 $depth[$id]++;

				$temp = new AWP_threader;
				$temp->template($comments, $depth[$id]);
			}

			if($depth[$id] >1)
				$depth[$id]--;

				$comments = null;
				$crawl_up = null;
		}
	}


	//loads children for comment. AJAX function.

	function comment_children(){
	global $awpall,$post;
		if(is_numeric($_POST['comment_parent'])){
			$awpall['hide_old_child_comments'] = $awpall['hide_child_comments'] = 0; /*Disable temporarily*/
			ob_start();
				$this->thread($_POST['comment_parent']);
			$response = ob_get_contents();
			ob_end_clean();
			$actions[] = '_p[i].show = _d[i].show;_p[i].hide = _d[i].hide;';
			AWP::make_response($response, $vars,$actions);
		}else{
			die(__('Invalid parent', 'awp'));
		}
	}

	function get_parent($id,$reset_parent=false,$new_parent=0) {
		global $wpdb;

		if(!$reset_parent){
			$comment_parent = $_POST['comment_post_parent'];
		}else{
			$comment_parent = $new_parent;
		}

		if(is_numeric($comment_parent) || $reset_parent == 1) {
			$result = $wpdb->query("UPDATE $wpdb->comments SET comment_parent = '$comment_parent' WHERE comment_ID = '$id'");
		}
	}

	function set_parent($commentdata){
		$comment_parent = (int) $_POST['comment_parent'];
		if(is_numeric($comment_parent) && empty($commentdata['comment_parent'])){
			$commentdata['comment_parent'] = $comment_parent;
		}
		return $commentdata;
	}

	function delete_parent($parent_ID, $mode='delete'){
	global $wpdb,$awpall,$id,$AWP_inlinecomments;
	static $loaded = 0;
		if($mode != 'delete')
			return;

			$comment = get_commentdata($parent_ID,1,true);
			$id = $comment['comment_post_ID'];
			$parent = $comment['comment_parent'];

		if(is_numeric($parent_ID)){
			if(!$loaded)
				$loaded = $this->comments(0,$AWP_inlinecomments->get_comments());

			$comments = $this->comments($parent_ID);

			if(!is_array($comments))
				return;

			if($awpall['on_parent_delete'] == 'delete'){

				foreach($comments as $c){
					wp_delete_comment($c->comment_ID);
				}

			}elseif($awpall['on_parent_delete'] == 'reset'){
				foreach($comments as $c){
					$this->get_parent($c->comment_ID,1);
				}
			}elseif($awpall['on_parent_delete'] == 'shift'){
				foreach($comments as $c){
					$this->get_parent($c->comment_ID,1,$parent);
				}
			}
		}
	}

	function thread_links($depth){
	global $awpall, $comment, $aWP;

			if($depth <= $awpall['comment_threaded_depth'] || 1 == 1){

				$this->links($comment->comment_ID);

			}
	}

	function thread_next($depth){
	global $awpall, $comment, $aWP;

		$no_end = 0;

			if($depth <= $awpall['comment_threaded_depth'] || 1 == 1){

				if($depth > $awpall['comment_threaded_depth']-1){

					echo $awpall['comment_tag_end']; $no_end = 1;

				}

			}else{

				echo $awpall['comment_tag_end']; $no_end = 1;

			}

			$this->thread($comment->comment_ID);

		return $no_end;
	}
	function get_link_texts($extra=''){
		global $awpall;
		$texts = array();
		$defaults = array();

		$texts[show] = $awpall['commentform_reply_open'];
		$texts[hide] = $awpall['commentform_reply_hide'];

		$defaults[show] = __('Add a Comment','awp');
		$defaults[hide] = __('Cancel reply','awp');

		return AWP::link_texts($texts,$defaults,'comment');
	}


	function links($extra='',$return=false){
	global $id,$awpall,$awp_link_count;
	global $is_page, $is_single;

		if ( !comments_open() || (!is_user_logged_in() && get_option('comment_registration')))
			return;

		if(!$awp_link_count[$id]){
			$awp_link_count[$id] = 10;
		}

		$links = $this->get_link_texts($extra);

		$show2 = $links[show];
		$show = $links[show];

		$link = '';

			$ops[_class] = "commentform_link";
			$ops[id] = 'awpcommentform_link'.$awp_link_count[$id].'_'.$id;
			$ops[anchor] = stripslashes($links[show]);
			$ops[URL] = get_permalink($id).'#respond';
			$ops[doit] = "'id': '$id', 'type': 'commentform', 'show': '".js_escape($links[show])."', 'hide': '".js_escape($links[hide])."', 'link_num': '$awp_link_count[$id]' ";

			if($awpall[nomove] || !is_singular())
				$ops[doit] .= ", 'nomove' : 1";

			$ops[doit] .= ", 'com_parent': '$extra'";
			$ops[anchor] = $links[show];

			$awp_link_count[$id]++;

			$link = AWP::links($ops,$return);


		if($return){
			return $link;
		}else{
			echo $link;
		}
	}

	function threaded_comments(){
	global $awpall,$id;
	?>
			<input type="hidden" name="comment_parent" value="0" id="comment_parent_<?php echo $id; ?>" />
	<?php
	}

	function admin(){
	global $aWP, $awpall;
	ob_start();
?>
<menus>
 	<menu id="threadedcomments">
		<name><?php _e('Threaded Comments','awp');?></name>
		<title><?php _e('Threaded Comments Module.','awp');?></title>
		<submenu>
			<item important="2" type="checkbox" name="simple_threading">
				<d><?php _e('Use Simple Threading to thread comments on your theme\'s default comments.php?','awp');?></d>
				<desc><?php _e('"Simple Threading" will attempt to use your default comment template to thread comments.','awp');?> <?php _e('If you use this method, your theme must use a standard template that is based on &amp;lt;ol> tags or have been edited for compatibility','awp');?> <?php _e('To make a file not based on an &amp;lt;ol> list compatible, you must add &amp;lt;!--AWP_inlinecomments--> directly before the tag that starts your comment list and directly after the tag that ends it. There must be no characters or blank spaces between the two.','awp');?> <?php _e('Certain features may not be available.','awp');?></desc>
			</item>
		</submenu>
			<submenu>
				<item type="text" important="5"  open="1"  name="commentform_reply_open" d='<?php _e('Show comment form text for replies to comments','awp');?>'/>
					<item type="text" name="commentform_reply_hide" d='<?php _e('Hide comment form text for replies to comments','awp');?>' />

				<item type="text" size="4"  name="comment_threaded_depth">
				<d><?php _e('Thread comments %s replies deep.','awp');?></d>
				<desc><?php _e('Depth is the number of child replies that will be shown before they are no longer nested inside of each other. (A number of 1 will thread comments, but will show them without nesting. This is useful for older posts, so readers can read comments in context.)','awp');?></desc>
			</item>
		</submenu>
		<submenu>

			<desc><?php _e('When using threaded comments what do you want done with children comments when their parents are deleted?','awp');?></desc>

			<item type="radio" open="1" value="" name="on_parent_delete">
				<d><?php _e('Do nothing.','awp');?></d>
				<desc><?php _e('Child comments will be stranded and will not be displayed.','awp');?></desc>
			</item>

			<item type="radio" open="1" value="shift" name="on_parent_delete">
				<d><?php _e('Shift child comments up a level.','awp');?></d>
				<desc><?php _e('Child comments will become the children of the parent comment\'s parent or be a top-level comment.','awp');?></desc>
			</item>

			<item type="radio" open="1" value ="reset"  name="on_parent_delete">
				<d><?php _e('Reset child comments to have no parent.','awp');?></d>
				<desc><?php _e('Replies to the child comments will remain threaded to the child comments.','awp');?></desc>
			</item>

			<item type="radio" value ="delete" name="on_parent_delete">
				<d><?php _e('Delete all child comments.','awp');?></d>
				<desc><?php _e('Including the replies to child comments.','awp');?></desc>
			</item>
		</submenu>
		</menu>
	</menus>
<?php
/*
		<submenu>
				<desc><?php _e('By default all child comments are shown open. The following options will collapse the comment and it will have to be opened by clicking on the title bar.','awp');?></desc>
				<item type="checkbox" name="hide_child_comments">
					<d><?php _e('Hide child comments?','awp');?></d>
					<desc><?php _e('If child comments are hidden then the reader will have to click the top parent to load all child comments below it.','awp');?></desc>
				</item>

				<item type="checkbox" nobreak="1" name="hide_old_child_comments"/>

				<item type="text" size="4" name="old_comment_days" >
					<d><?php _e('Hide child comments more than than %s days old.','awp');?></d>
					<desc><?php _e('If the top most parent comment is over a certain numbers of days old, its child comments will be hidden.','awp');?></desc>
				</item>
		</submenu>*/

	$menu =	 ob_get_contents();
	ob_end_clean();

	do_action('awp_build_menu',$menu);
	}

	function awp_get_options($j){
		$j[texts][] = 'comment_threaded_depth';
		$j[texts][] = 'old_comment_days';
		$j[checkboxes][] = 'hide_old_child_comments';
		$j[checkboxes][] = 'hide_child_comments';
		$j[checkboxes][] = 'simple_threading';
		$j[selects][] = 'threadedcomments';
		$i[texts][] = 'commentform_reply_open';
		$i[texts][] = 'commentform_reply_close';
		$j[radios][] = array('on_parent_delete','shift');
		return $j;
	}
	function set_defaults(){
	global $awpall;
		$awpall[threadedcomments] = 'Enabled';
		$awpall[comment_threaded_depth] = 4;
		update_option('awp',$awpall);
	}
}

class AWP_threader{

	function template($comments,$depth){
	global $awpall,$comment, $post,$id, $aWP;
		include(AWP::template('inlinecomments','comments'));
	}

}

?>
