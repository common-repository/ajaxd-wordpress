// <script>(to trick editors into using javascript syntax)

var aWP_livepreview = function(){
//Private Methods and Attributes are only accessible internally.
	<?php do_action('awp_live_preview_vars');?>
		/* private attributes */

		var characters = new Array('---', ' -- ', '--', 'xn&#8211;', '\\.\\.\\.', '\\s\\(tm\\)','(\\d+)"',"(\\d+)'","(\\w)('{2}|\")",'(`{2}|")(\\w)',"(\\w)'","'([\\s.]|\\w)");
		var replacements = new Array('&#8212;', ' &#8212; ', '&#8211;', 'xn--', '&#8230;',' &#8482;','$1&#8243;', '$1&#8242;','$1&#8221;','&#8220;$2','$1&#8216;','&#8217;$1');
		var char_regex = [];
		var charcount =characters.length;
		for(x=0; x< charcount; x++){
			char_regex[x] = new RegExp(characters[x], "g")
		}
		var delay = 0;

		var element = [];

		var _cleanhtml = function(text){
			<?php
				if($awpall['live_preview_no_tags']){
					$badtags = explode(' ', $awpall['live_preview_no_tags']);
					if(count($badtags) > 0){
						?>

					text = text.replace(/<(\s*<?php foreach($badtags as $badtag){ echo $badtag.'|'; }?>@%@%@)/g, '&lt;$1');
						<?php


					}
				}
			?>
		return text;
		};

/* private Methods */

	/*
	// Direct translation of wptexturize from php to javascript
	// Cleaned and optimized for speed and actual usage.*/
		var _js_wptexturize =  function(text) {
			var next = true;
			var output = '';
			var curl = '';
				text = text.replace(/(<[^>]*>)/g, '@%@%@$1@%@%@');
				var textarr = text.split('@%@%@');
				var stop = textarr.length;
				var i = 0;
			while (stop > i) {
				curl = textarr[i];
					if (curl.charAt(0) != '<' && next) { // If it's not a tag

						var x = charcount;
						while(x--){
							if(curl.match(char_regex[x])){
								curl = curl.replace(char_regex[x],replacements[x]);
							}
						}
					} else if ( curl.indexOf('<code') == 0 || curl.indexOf('<pre') == 0) {
						next = false;
					} else {
						next = true;
					}
				curl = curl.replace('/&([^#])(?![a-zA-Z1-4]{1,8};)/g', '&#038;$1');
				output += curl;
				i++;
			}
		return output;

		};

	<?php
	/*
	// originally from: /wp-includes/js/tinymce/plugins/wordpress/editor_plugin.js
	// Modified to compress size.
	// If you want your users to use tables uncomment the next line:*/
	/*$rest .='table|thead|tfoot|tbody|tr|td|th|div|';*/
	$rest .= 'dl|dd|dt|ul|ol|li|pre|blockquote|p|h[1-6]';
	?>

		var _js_wpautop = function (pee) {

			pee = pee + "\n\n";
			pee = pee.replace(/<br \/>\s*<br \/>/gi, "\n\n");
			pee = pee.replace(/(<(?:<?php echo $rest;?>)[^>]*>)/gi, "\n$1");
			pee = pee.replace(/(<\/(?:<?php echo $rest;?>)>)/gi, "$1\n\n");
			pee = pee.replace(/\r\n|\r/g, "\n");
			pee = pee.replace(/\n\s*\n+/g, "\n\n");
			pee = pee.replace(/([\s\S]+?)\n\n/gm, '<p>$1 </p>\n');
			pee = pee.replace(/<p>\s*?<\/p>/gi, '');
			pee = pee.replace(/(<p>)*\s*(<\/?(?:<?php echo $rest;?>|hr)[^>]*>)\s*(<\/p>)*/gi, "$2");
			pee = pee.replace(/<p>(<li.+?)<\/p>/i, "$1");
			pee = pee.replace(/<p><blockquote([^>]*)>/gi, "<blockquote$1><p>");
			pee = pee.replace(/<\/blockquote><\/p>/gi, '</p></blockquote>');
			pee = pee.replace(/\s*\n/gi, " <br />\n");
			pee = pee.replace(/(<\/?(?:<?php echo $rest;?>)[^>]*>)\s*<br \/>/gi, "$1");
			pee = pee.replace(/'<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)/gi, '$1');
			pee = pee.replace(/^((?:&nbsp;)*)\s/gm, '$1&nbsp;');

			return pee;
		};

		var _update = function (id,suf){
			var comment = '';
				if(!element[id]){
					if(suf){

						element[id] = document.getElementById('comment'+suf)
					}else{
						base = document.getElementById('awpsubmit_commentform_'+id).getElementsByTagName('textarea');
						x = base.length;
						for(i=0; i<x; i++){
							if(base[i].id = 'comment'){
								element[id] = base[i];
								i = x;
							}
						}
					}
				}

				comment = element[id].value
				if(comment != ''){
					comment = _js_wpautop(comment);
					comment = _js_wptexturize(comment);
					comment = _cleanhtml(comment);

					<?php do_action('awp_live_preview_filters');?>

      <?php if($awpall['live_preview_html'] == 1){ echo "comment = comment.replace(/</g, '&lt;'); comment = comment.replace(/>/g, '&gt;');"; }?>
					document.getElementById('add_comment_live_preview_'+id).innerHTML = comment;
				}

		};

	<?php do_action('awp_live_preview_private_methods');?>

	/* Public Methods */
	return {
		preview: function (id,suf) {
			if(delay >= 0){
				_update(id,suf);
				delay = 0;
			}else{
				delay++;
			}
		}
	}
}();