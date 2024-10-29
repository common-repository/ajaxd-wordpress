=== AJAXed Wordpress ===
Contributors: Aaron Harun
Donate link: http://ajaxedwp.com/go/donate/
Tags: ajax, inline posts, inline comments, add comment, ajax pages, posts, comments, comment, recaptcha, lightbox, threaded comments, comment form, quicktags, smilies
Requires at least: 2.1
Tested up to: 2.8
Stable tag: 1.28

A highly customizable plugin to add AJAX to your blog. AWP uses AJAX to load posts inline, paginate posts, load and submit comments, and more.

== Description ==

AJAXed Wordpress (aWP) is an extremely powerful plugin that harnesses the power of AJAX and Wordpress to improve the user experience, the administration capabilities and the design potential of any Wordpress based blog.

AWP’s basic features include inline paginated posts, inline comments, threaded comments, the ability to submit comments with AJAX, pagination of your homepage, live comment preview and much more, but it does not, however, force you to use any feature, and it also allows all aspects of the plugin to be easily customized through a single Administration panel. It also has special features that will ensure compatibility with many other plugins.

Major Features:

1. Loads posts, comments and the comment form inline.
1. AJAX Submit comments
1. **Threaded Comments**
1. Highly customizable post excerpts including having multiple inline pages.
1. **Embed posts into posts and pages** and load them inline.
1. Live Comment Preview or AJAX full comment preview.
1. **Rich Text Editor** for the comment form.
1. **Japanese, French (83% completed), Spanish, and Italian translations.**
1. Ajax previous/next post and pages.
1. **Full AJAX Navigation**
1. Clickable **quicktags and smilies.**
1. **Major features work without theme edits.**
1. Easily customized templates.
1. Supports multiple themes.
1. Options can be customized on a post-by-post basis.
1. Supports the **TW-Sack, JQuery, Mootools, and Prototype.js.**
1. Powerful Admin Panel.
1. Includes **Lightbox, Slimbox, Lightview, and reCAPTCHA support.**
1. Works with [WP AJAX Edit Comments](http://www.raproject.com/wordpress/wp-ajax-edit-comments/) to allow **inline comment editing**.
1. Excellent support and active development.
1. Me.

==Installation==

Basic Installation tips. Read the [full AJAXed Wordpress instructions](http://anthologyoi.com/forum/awp-documentation/) for more information.

1. Download aWP, unzip and upload the entire aWP folder and its contents to your Wordpress plugins folder (/wp-content/plugins/), and activate the plugin in Wordpress.z
2. **Note that I said upload the entire folder.**

* To Enable threaded comments, select the checkbox on the "comments" tab of the admin panel.
* To use inline comments on single post pages, select the "simple comments" checkbox on the "comments" tab of the AWP admin panel.
* To paginate posts without using more tags, select a "split mode" such as "By word count" on the "post and pages" tab of the admin panel and set a default number of words or paragraphs.

If you want to add inline comments or a comment form to your index.php or archive.php file:

1. Open your index.php (or post.php depending on your theme) in your themes folder.
1. To display comments in-line add `<?php do_action('awp_comments'); ?>` where you want the comments to appear when they are loaded and add `<?php do_action('awp_comments_link');?>` where you want the show/hide comments link to appear.
1. To display the add comment form in-line add `<?php do_action('awp_commentform'); ?>` where you want the add comment box to appear and add `<?php do_action('awp_commentform_link');?>` where you want the show/hide add comment box link to appear.
1. Save the file and upload it to your current theme folder. This process can be repeated with other files.

If you need more help, feel free to [ask](http://ajaxedwp.com/forum/)

==Screenshots==

1. AJAXed WordPress administration panel in WordPress 2.5
2. Some Inline Comment Form options in WordPress 2.5
3. The module selection screen on WordPress 2.5
4. A screenshot of the administration panel on WordPress 2.3
5. The post option screen on WordPress 2.3
