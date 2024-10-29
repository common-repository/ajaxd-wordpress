<?php
global $aWP, $awpall,$AWP_admin;
	if(!$AWP_admin)
	$AWP_admin = new AWP_admin();

	$AWP_admin->start_panel();


if($aWP[admin_message]){
echo '<div class="updated"><p><strong>'.$aWP[admin_message].'</strong></p></div>';
}
?>
<div class="wrap">
	<h2><?php _e('Edit AJAXed Wordpress Configuration', 'awp'); ?></h2>

	<ul class="examplemenu">
		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_main',1); return false;" id="menu_admin_main"><?php _e('Admin', 'awp'); ?></a></li>
		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_modules',1); return false;" id="menu_admin_modules"><?php _e('Modules', 'awp'); ?></a></li>
		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_overall',1); return false;" id="menu_admin_overall"><?php _e('Overall Options','awp'); ?></a></li>
		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_default_css',1); jQuery('#admin_default_css > iframe').height(500); jQuery('#admin_default_css > iframe').width('90%');return false;" id="menu_admin_default_css"><?php _e('Default CSS','awp'); ?></a></li>
		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_posts',1); return false;" id="menu_admin_posts"><?php _e('Posts &amp; Pages', 'awp'); ?></a></li>
		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_comments',1); return false;" id="menu_admin_comments"><?php _e('Comments', 'awp'); ?></a></li>
		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_commentform',1); return false;" id="menu_admin_commentform"><?php _e('Comment Form', 'awp'); ?></a></li>
		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_integration',1); return false;" style="display:none;" id="menu_admin_integration"><?php _e('Integrations', 'awp'); ?></a></li>
		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_ajax',1); return false;" id="menu_admin_ajax"><?php _e('AJAX &amp; Effects', 'awp'); ?></a></li>
		<li><a href="#" onclick="aWP_hide(); aWP_toggle('admin_other',1); return false;" id="menu_admin_other"><?php _e('Other', 'awp'); ?></a></li>
		<?php do_action('awp_admin_more_menu_links');?>
	</ul>

<p style="color:red; width:700px;">Thgank you to all those who have donated to AJAXED WP over the years. If it wasn't for the donations of <a href="http://ajaxedwp.com/supporters/#donators">these people</a> among others, this plugin would never have grown as large nor would I have been able to keep working on it for all this time.</p>

	<table>
		<tr>
			<td width="79%" valign="top" id="awp_menu">
				<div id="admin_main" name="awp_menu" <?php if($_GET['last_screen'] != '' || ($_GET['act'] == 'activated' || $_GET['act'] == 'deactivate')){?> class="Disabled" <?php } ?>>

					<h3><?php _e('Welcome to the AJAXed WordPress Dashboard.', 'awp'); ?></h3>

					<?php if($aWP[messages]){
						echo '<div class="updated"><p><strong>'.$aWP[messages].'</strong></p></div>';
					}?>

					<p><?php _e('This is a quick over-view of AJAXed WordPress (AWP).', 'awp'); ?> <?php _e('In the future, I plan to integrate tools that will make AWP even more useful','awp');?></p>


					<p><?php _e('To add, change or update AWP options, you may use the nav bar above to select between the different screens. Some screens may be empty, but this does not mean anything is wrong.', 'awp'); ?> <strong><?php _e('Screens are only hidden, so you only need to save the options when you are completely finished.', 'awp'); ?></strong></p>

					<ul>
						<li><p><strong><?php _e('Admin', 'awp'); ?></strong> &mdash;  <?php _e('This is the Admin screen, and it is the "dashboard" for AJAXed WordPress.', 'awp'); ?> </p></li>
						<li><p><strong><?php _e('Modules', 'awp'); ?></strong> &mdash; <?php _e('This is similar to the plugins page in WordPress. Most features must first be enabled or disabled here.', 'awp'); ?></p></li>
						<li><p><strong><?php _e('Overall Options', 'awp'); ?></strong> &mdash; <?php _e('These are options built into the core itself and that are used by all or most modules. For example, the throbber you use.', 'awp'); ?></p></li>
						<li><p><strong><?php _e('Posts &amp; Pages', 'awp'); ?></strong> &mdash; <?php _e('These options control the way AWP displays posts or pages including feature such as inline or embedded posts. Any modules that modify posts or pages use this screen.', 'awp'); ?></p></li>
						<li><p><strong><?php _e('Comments', 'awp'); ?></strong> &mdash; <?php _e('The options on this screen control the way AWP displays comments and controls features such as threaded comments. Any modules that modify the way comments are displayed will use this screen.', 'awp'); ?></p></li>
						<li><p><strong><?php _e('Comment Form', 'awp'); ?></strong> &mdash; <?php _e('This screen holds the options and controls the form that allows the user to submit comments. Any modules that modify the comment form will use this screen.', 'awp'); ?></p></li>
						<li><p><strong><?php _e('AJAX &amp; Effects', 'awp'); ?></strong> &mdash; <?php _e('This is primarily used by the AWP Core (not modules), and allows you to select from a variety of effects and JavaScript libraries. ', 'awp'); ?></p></li>
						<li><p><strong><?php _e('Other', 'awp'); ?></strong> &mdash; <?php _e('This page is used for modules that don\'t fit in with the other categories.', 'awp'); ?></p></li>
					</ul>

					<?php _e('Some common queries:', 'awp'); ?>

					<ul>
						<li><p><strong><?php _e('I activated the plugin and nothing is happening. How do I get it working?', 'awp'); ?></strong>

						<?php _e('It depends what features you want to have. When you first start the plugin the only feature that is active is your posts are split on the more tag.', 'awp'); ?>

						<?php _e('If you want comments and the comment form on the front page, for example, you will have to make a few theme edits as described <a href="http://ajaxedwp.com/go/readme">in the readme</a>, or if you want inline posts, you will have to set a method to "split" them and a length in the aWP administration panel under the posts tab.', 'awp'); ?>
					</p></li>
						<li><p><strong><?php _e('When will AWP be translated?', 'awp'); ?></strong>

						<?php _e('When there is someone who is willing to translate it. Could it be you?', 'awp'); ?>
					</p></li>
						<li><p><strong><?php _e('Why do the AWP comments look so bad on my theme?', 'awp'); ?></strong>

						<?php _e('AWP uses a very generic template by default. On some themes it fits in perfectly, some not so good and some it is flat out ugly. However, it is easy to update the styling, and you can also get help in the support forum.', 'awp'); ?>
					</p></li>

					</ul>

					<p><?php _e('I hope that this was helpful to you. Enjoy using AJAXed WordPress.', 'awp'); ?> <br /> Aaron Harun.</p>



					<?php do_action('awp_admin_main');?>
				</div>

				<div id="admin_modules" name="awp_menu" <?php if($_GET['act'] != 'activated' && $_GET['act'] != 'deactivate'){?> class="Disabled" <?php } ?>>
					<fieldset id="modules">
						<h3 class="dbx-handle"><?php _e('Modules','awp');?></h3>
						<div>
							<p><?php _e('AJAXed Wordpress is made up of many smaller plugins called modules.', 'awp'); ?> <?php _e('You may disable or enable modules here.', 'awp'); ?> <?php _e('Some modules may need to have some of their features explicitly enabled after activation.', 'awp'); ?></p>

							<?php echo $AWP_admin->print_modules();?>
						</div>
					</fieldset>
				</div>

				<form method="post" id="ajaxedwp" action="<?php $_SERVER['PHP_SELF']?>">

					<div id="admin_overall" name="awp_menu" <?php if($_GET['last_screen'] != 'admin_overall'){?> class="Disabled" <?php } ?>>
						<?php do_action('awp_admin_overall');?>
					</div>

					<div id="admin_default_css" name="awp_menu" <?php if($_GET['last_screen'] != 'admin_default_css'){?> class="Disabled" <?php } ?>>
						<textarea name="awp[default_css]" class="codepress css" rows="20" cols="70" id="default_css"><?php echo $awpall['default_css'];?></textarea>
					</div>

					<div id="admin_posts" name="awp_menu" <?php if($_GET['last_screen'] != 'admin_posts'){?> class="Disabled" <?php } ?>>
						<?php do_action('awp_admin_posts');?>
					</div>

					<div id="admin_comments" name="awp_menu" <?php if($_GET['last_screen'] != 'admin_comments'){?> class="Disabled" <?php } ?>>
						<?php do_action('awp_admin_comments');?>
					</div>

					<div id="admin_commentform" name="awp_menu" <?php if($_GET['last_screen'] != 'admin_commentform'){?> class="Disabled" <?php } ?>>
						<?php do_action('awp_admin_commentform');?>
					</div>

					<div id="admin_integration" name="awp_menu" <?php if($_POST['last_screen'] != 'admin_integration'){?> class="Disabled" <?php } ?>>
						<?php do_action('awp_admin_integration');?>
					</div>

					<div id="admin_other" name="awp_menu" <?php if($_GET['last_screen'] != 'admin_other'){?> class="Disabled" <?php } ?>>
						<?php do_action('awp_admin_other');?>
						<h3><?php __("If this block is empty it is not an error. It just doesn't have anything to display.",'awp');?></h3>
					</div>

					<div id="admin_ajax" name="awp_menu" <?php if($_GET['last_screen'] != 'admin_ajax'){?> class="Disabled" <?php } ?>>
						<?php do_action('awp_admin_ajax');?>
					</div>

						<?php do_action('awp_admin_more_menus');?>

					<script type="text/javascript">aWP_hide_empty();</script>
					<div id="awp_submit" <?php if($_GET['last_screen'] == '' || $_GET['act'] == 'activated' || $_GET['act'] == 'deactivate'){?> class="Disabled" <?php }?>>

						<input type="hidden" name="last_screen" id="last_screen" value="<?php echo $_GET['last_screen']; ?>" />

						<?php if($aWP[is_test]==false){ ?> <a href="#" onclick="aWP_toggle('awp_test_options'); return false;"><?php _e('Options for testing this configuration.', 'awp');?></a><?php }?>
						<div class="<?php if($aWP[is_test]==false){ ?>Disabled<?php }?>" id="awp_test_options">
							<p><input type="radio" name="awp_test" <?php if($aWP[is_test]==true){ echo 'checked="checked"';}?> value="1"/> <?php _e("Save these settings as a test.", 'awp');?> <?php _e('The settings will not be publicily visible, and you will not be able to edit the actual blog settings until they are deleted or saved.','awp');?></p>
							<p><input type="radio" name="awp_test" value="2"/><?php _e("Delete test settings.",'awp');?> <?php _e("(This reverts the admin panel settings back to the live settings.)",'awp');?></p>
							<p><input type="radio" name="awp_test" <?php if($aWP[is_test]==FALSE){ echo 'checked="checked"';}?> value="3"/><?php _e("Save these settings normally.",'awp');?>
						</div>

						<input type="hidden" name="action" value="saveconfiguration"/>
						<input type="submit" value="<?php _e('Save', 'awp'); ?>" style="width:100%;" />

					</div>
				</form>
			</td>
			<td width="20%" valign="top">
				<p style="text-align:center"><img src="http://anthologyoi.com/wp-content/uploads/2008/02/ajax_wordpress_logo_126x82-wp_loves_ajax.png" alt="AWP -- WordPress Loves AJAX"/></p>

				<fieldset id="plugin_info" >
					<h3 class="dbx-handle"><?php _e('Plugin Info:', 'awp'); ?></h3>
					<div>
						<a href="http://ajaxedwp.com/"><?php _e('AWP Homepage', 'awp'); ?></a>
						<a href="http://ajaxedwp.com/changelog/"><?php _e('AWP Changelog', 'awp'); ?></a>
						<a href="http://ajaxedwp.com/go/readme/"><?php _e('AWP Readme', 'awp'); ?></a>
						<a href="http://ajaxedwp.com/go/themes/"><?php _e('AWP Theme Support', 'awp'); ?></a>
						<a href="http://ajaxedwp.com/support/"><?php _e('Support', 'awp'); ?></a>
						<a href="http://ajaxedwp.com/forum/"><?php _e('Support Forum', 'awp'); ?></a>
						<a href="http://anthologyoi.com/"><?php _e('Author Homepage', 'awp'); ?></a>
						<a href="http://ajaxedwp.com/go/donate/"><?php _e('Donate', 'awp'); ?></a>
					</div>
				</fieldset>

				<fieldset id="Utilities" >
					<h3 class="dbx-handle"><?php _e('Utilities:', 'awp'); ?></h3>
					<a href="#" onclick="aWP_toggle('awp_options'); return false;"><?php _e('Show all current saved AWP options', 'awp');?></a>
					<div class="Disabled" id="awp_options">

						<?php
							$options  = get_option('awp_test');
							$save = array();
							if(is_array($options)){
						?>
							<p><?php _e('The following are your current AWP <em>TEST</em> options:', 'awp'); ?><br/>
								<?php

										while(list($key, $val) = each($options)){
											if($val) //anything not set won't need to be reset;
												$save[$key] = str_replace('"','\"',stripslashes($val));
										}
											echo '<textarea style="width:100%;"  rows="5" cols="5">'.htmlentities(serialize($save)).'</textarea>';
								?>
							</p>
						<?php }?>
							<p><?php _e('The following are your current AWP <em>SAVED</em> options:', 'awp'); ?> <br/>
								<?php
									$options  = get_option('awp');
									$save = array();
									if(is_array($options)){
										while(list($key, $val) = each($options)){
											if($val) //anything not set won't need to be reset;
												$save[$key] = str_replace('"','\"',stripslashes($val));
										}
											echo '<textarea style="width:100%;"  rows="5" cols="5">'.serialize($save).'</textarea>';
									}
								?>
							</p>
						</div>

						<a href="#" onclick="aWP_toggle('restorefrmsite'); return false;"><?php _e('Restore options from a backup.', 'awp'); _?> </a>
						<div class="Disabled" id="restorefrmsite">
							<?php _e('(These options will be stored as test options, so you may review them.)', 'awp'); ?>
							<form method="post" action="<?php $_SERVER['PHP_SELF']?>">
								<p>
									<textarea style="width:100%;" rows="2" cols="5" name="resop"></textarea>
									<input type="hidden" name="action" value="restoreupdate"/>
								</p>
								<p>
									<input type="submit" value="<?php _e('Restore Options', 'awp'); ?>" style="width:80%;" />
								</p>
							</form>
						</div>

						<a href="#" onclick="aWP_toggle('restore'); return false;"><?php _e('Reset to default settings', 'awp'); ?></a>
						<div class="Disabled" id="restore">
							<form method="post" action="<?php $_SERVER['PHP_SELF']?>">
									<input type="hidden" name="action" value="restoredefaults" />
									<input type="checkbox" name="restore" value="1"/> <?php _e('Confirm Reset?', 'awp'); ?>
									<input type="submit" value="<?php _e('Reset Settings', 'awp'); ?>" style="width:80%;" />
								</p>
							</form>
						</div>
						<?php do_action('awp_ajax');?>
					</div>
				</fieldset>

				<fieldset id="please_donate" >
					&nbsp;&nbsp;<?php printf(__('Dear %s,', 'awp') , $user_identity); ?>
					<div><p>&nbsp;&nbsp;
						<?php _e('Have you found aWP useful? ', 'awp'); ?>
						</p><p>&nbsp;&nbsp;
						<?php _e('If so, please consider making a small donation. Most people donate anywhere from $5 - $100, and all donations, <strong>even the small ones</strong>, help to defray my costs and support continued development. (Besides, all donators get a link on the main aWP page and its daily 200 - 500 unique visitors.)', 'awp'); ?></p>
						<p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business=admin%40anthologyoi%2ecom&amp;item_name=aWP%20Donation&amp;no_shipping=0&amp;cn=Optional%20Notes&amp;tax=0&amp;currency_code=USD&amp;lc=US&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8"><img src='http://www.paypal.com/en_US/i/btn/x-click-but04.gif' alt='Donate With Paypal' /></a></p>
					<p>&nbsp;&nbsp;
						<?php _e('If you can\'t donate, please at least consider leaving the small footer link enabled.', 'awp'); ?><br /> <br /><br /> <br />
							&nbsp;&nbsp;&nbsp;Aaron Harun
					</p>
					</div>
				</fieldset>
			</td>
		</tr>
	</table>
</div>
