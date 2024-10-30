<?php

// call the function that creates the Admin page
if (function_exists('add_options_page')) {
	add_options_page('Comment Warning Settings', 'Comment Warning', 10, basename(__FILE__), 'comment_warning_admin_page');
}

// ****** START COMMENT_WARNING_ADMIN_PAGE: CREATE ADMIN PAGE ******
function comment_warning_admin_page(){
	$comment_warning_options = get_option('comment_warning_options');
	// Post the update settings form
	if (isset($_POST['comment_warning_options_submit']) && check_admin_referer('comment_warning_admin_page_submit')) {
		$comment_warning_options['message'] = trim(htmlentities(stripslashes($_POST['cw_message'])));
		$comment_warning_options['triggers'] = explode(',',trim(htmlentities($_POST['cw_triggers'])));
		$comment_warning_options['redirect_threshold'] = trim(htmlentities($_POST['cw_redirect_threshold']));
		$comment_warning_options['redirect_url'] = apply_filters('pre_comment_author_url', trim($_POST['cw_redirect_url']));
		update_option('comment_warning_options', $comment_warning_options);
		echo '<div id="message" class="updated fade"><p><strong>';
		_e('Options saved.'); 	// really need to language-ify the rest as well
		echo '</strong></p></div>';
	}

	// Post the clear log form
	if (isset($_POST['comment_warning_clear_log']) && check_admin_referer('comment_warning_clear_log_submit')) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'comment_warning';
		if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
			$insert = "DELETE FROM " . $table_name;
			$results = $wpdb->query( $insert );
		}		
		echo '<div id="message" class="updated fade"><p><strong>';
		_e('Log Cleared.'); 	// really need to language-ify the rest as well
		echo '</strong></p></div>';
	}

	// Post the reset settings form
	if (isset($_POST['comment_warning_reset']) && check_admin_referer('comment_warning_reset_settings_submit')) {
		$comment_warning_reset = true;
		require_once('comment-warning-activate.php');
		$comment_warning_reset = false;
		echo '<div id="message" class="updated fade"><p><strong>';
		_e('Default settings restored.'); 	// really need to language-ify the rest as well
		echo '</strong></p></div>';
	}

	// Post the uninstall form
	if (isset($_POST['comment_warning_uninstall']) && check_admin_referer('comment_warning_uninstall_and_deacctivate_submit')) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'comment_warning';
		if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
			$insert = "DROP TABLE " . $table_name;
			$results = $wpdb->query( $insert );
		}
		if (delete_option('comment_warning_options')) {
			$active_plugins = get_settings('active_plugins');
			array_splice($active_plugins, array_search(plugin_basename(dirname(__FILE__)).'/comment-warning.php',$active_plugins),1);
			update_option('active_plugins', $active_plugins);
			echo '
				<script type="text/javascript">
				window.location = "plugins.php?deactivate=true";
				</script>
			';
		}		
		else {
			echo '<div id="message" class="updated fade"><p><strong>';
			_e('An error has occurred while trying to delete the options from database. Please try again.'); 	// really need to language-ify the rest as well
			echo '</strong></p></div>';
		}	
	}

	// if URL includes cwpage parameter, then show log (otherwise show settings page)
	if (isset($_GET['cwpage'])) {
	?>
		<div class="wrap">
		<h2>Comment Warning Log</h2>
		<div id="poststuff">

		<p><a href="options-general.php?page=comment-warning-admin.php">Settings</a> | <a href="options-general.php?page=comment-warning-admin.php&cwpage=1">View Log</a></p>

	<?php
		global $wpdb;
		$table_name = $wpdb->prefix . 'comment_warning';
		
		if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
			echo '<p>Error: Could not connect to table. Please try again. If problem persists, please try deactivating then reactivating the plugin</p>';
			return;	// no need to keep going
		}
		
		// set variables for the URL parameters
		$cw_page = htmlentities($_GET['cwpage']); // we already know it is set
		if (isset($_GET['cwno'])){
			$cw_no = htmlentities($_GET['cwno']);
		}
		else {
			$cw_no = 10;	// default
		}
		if (isset($_GET['cworder'])){
			$cw_order = htmlentities($_GET['cworder']);
		}
		else {
			$cw_order = 'date';	// default
		}
		
		// set other variables we'll need
		$offset = ($cw_page-1)*$cw_no;
		$total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
		$no_of_pages = 	ceil($total / $cw_no);
	
		// get the log entries
		if ($cw_order == 'date') {
			$results = $wpdb->get_results("SELECT id, date, localurl, ip, referer, rule FROM $table_name ORDER BY date DESC LIMIT $offset , $cw_no");		
		}
		else {
			$results = $wpdb->get_results("SELECT id, date, localurl, ip, referer, rule FROM $table_name ORDER BY $cw_order LIMIT $offset , $cw_no");
		}
		
		// if there are any results, lets show them!
		if ($results) {
			// show where we are
			echo '<p>Showing records '. ($offset+1) . ' to ';
			if ($cw_page == $no_of_pages && $total % $cw_no!=0) {
				echo $offset+($total%$cw_no);
			}
			else {
				echo $offset+$cw_no;
			}
			echo " of $total, ordered by ". cw_order_desc($cw_order) . "</p>\n";
			
			// drop to HTML to setup table
	?>
			<table class="tablesorter" id="myTable" cellspacing="1">
			<thead>
				<tr class="header">
					<th></th>
					<th><a href="options-general.php?page=comment-warning-admin.php&cwpage=1&cwno=<?php echo $cw_no; ?>&cworder=date">Date</a></th>
					<th><a href="options-general.php?page=comment-warning-admin.php&cwpage=1&cwno=<?php echo $cw_no; ?>&cworder=localurl">Target URL</a></th>
					<th><a href="options-general.php?page=comment-warning-admin.php&cwpage=1&cwno=<?php echo $cw_no; ?>&cworder=ip">IP Address</a></th>
					<th><a href="options-general.php?page=comment-warning-admin.php&cwpage=1&cwno=<?php echo $cw_no; ?>&cworder=referer">Referer</a></th>
					<th><a href="options-general.php?page=comment-warning-admin.php&cwpage=1&cwno=<?php echo $cw_no; ?>&cworder=rule">Triggering Rule</a></th>
				</tr>
			</thead>
			<tbody>
			<?php 
			$i = 1;
			foreach ($results as $key => $value) {
				echo '<tr';
				if ($i % 2 != 0) echo ' class="odd"';			
				echo'><td>'.($offset+$i).'</td>'."\n";
				echo '<td>'. $results[$key]->date.'</td>'."\n";
				echo '<td>'.$results[$key]->localurl.'</td>'."\n";
				$num_of_comments = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_author_IP='".$results[$key]->ip."' AND comment_approved = '1'");
				if (!$num_of_comments) {
					echo '<td>'.$results[$key]->ip.' (0 comments)</td>'."\n";
				} 
				elseif ($num_of_comments == 1) {				
					echo '<td>'.$results[$key]->ip.' (<a href="edit-comments.php?s='.$results[$key]->ip.'&mode=detail">1 comment</a>)</td>'."\n";
				}
				else {
					echo '<td>'.$results[$key]->ip.' (<a href="edit-comments.php?s='.$results[$key]->ip.'&mode=detail">'.$num_of_comments.' comments</a>)</td>'."\n";				
				}
				echo '<td>'.$results[$key]->referer.'</td>'."\n";
				echo '<td>'.$results[$key]->rule.'</td></tr>'."\n";
				$i++;
			}
			?>
			</tbody>
			</table>
	
			<p>Result page no: <?php echo cw_paging($total,$cw_page,$cw_no,$cw_order,$no_of_pages); ?></p>
			<p>Results per page: <?php echo cw_per_page($cw_no,$cw_order); ?></p>
			
	<?php
		} 
		// if nothing found, tell them
		else {
			echo 'No log entries found for this range'; 
		}			
	}
	else {
	// show normal admin page (drop out of php to do this)
	?>
		<div class="wrap">
		<h2>Comment Warning Settings</h2>
		<div id="poststuff">

		<p><a href="options-general.php?page=comment-warning-admin.php">Settings</a> | <a href="options-general.php?page=comment-warning-admin.php&cwpage=1">View Log</a></p>

		<!-- Start General Settings Form (Posts to this page) -->
		<form name="comment_warning_options_form" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__); ?>" method="post">
		<?php if (function_exists(wp_nonce_field)) {wp_nonce_field('comment_warning_admin_page_submit'); }?>

		<!-- The Warning Message section of the option page -->
		<div class="stuffbox">
		<h3>Warning Settings</h3>
		<div class="inside">

		<h4>Warning Message</h4>
		<p>The following message will be displayed to users who come to your site from web pages containing certain terms in the URL, which indicate they are likely to be potential comment spammers. You can change this message below. Note that [cwterm] will automatically be replaced with the term that triggered the warning.</p>
		<textarea style="width: 90%; height: 300px;" id="cw_message" name="cw_message"><?php echo $comment_warning_options['message']; ?></textarea>

		<h4>Warning Triggers</h4>
		<p>The comment warning will be displayed to users who come to your site from web pages with the following terms in the URL. You can add to this list if you want the message to appear for other terms. Terms must be separated by commas.</p>
		<p><strong>Warning: Do not change these unless you know what you are doing. Incorrect formatting could break your website!</strong></p>
		<textarea style="width: 90%; height: 120px;" id="cw_triggers" name="cw_triggers"><?php echo implode(',',$comment_warning_options['triggers']); ?></textarea>
		<!-- Show Bottom Update Button -->
		<div class="submit">
		<input type="submit" name="comment_warning_options_submit" value="<?php _e('Update Options &raquo;') ?>"/>
		</div>
		</div> <!-- class="inside" -->
		</div> <!-- class="stuffbox" -->
		
		<div class="stuffbox">
		<h3>Redirection Settings</h3>
		<div class="inside">
		<p>It is possible to redirect potential spammers to another URL <strong>instead of</strong> serving the comment warning. Redirecting is harsh as there may be <strong>some false positives.</strong></p>

		<h4>Redirection Threshold</h4>
		<p>Controls when redirect takes place (after x number of visits that trigger the warning from the same IP address). If set to 0, visitors will never be redirected. If set to 1, visitors will be redirected immediately without seeing the comment warning. <strong>Page refreshes count towards the threshold.</strong></p>
		<input type="text" size="5" name="cw_redirect_threshold" id="cw_redirect_threshold" value="<?php echo $comment_warning_options['redirect_threshold'];?>" />

		<h4>Redirection URL</h4>
		<p>Controls where the visitor will be taken to.</p>
		<input type="text" size="70" name="cw_redirect_url" id="cw_redirect_url" value="<?php echo $comment_warning_options['redirect_url'];?>" /><br />

		<!-- Show Bottom Update Button -->
		<div class="submit">
		<input type="submit" name="comment_warning_options_submit" value="<?php _e('Update Options &raquo;') ?>"/>
		</div>
		
		<!-- End General Settings Form -->
		</form>
		</div> <!-- class="inside" -->
		</div> <!-- class="stuffbox" -->

		<!-- Start Reset Form (Posts to this page) -->
		<form name="comment_warning_reset_form" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__); ?>" method="post">
		<?php if (function_exists(wp_nonce_field)) {wp_nonce_field('comment_warning_reset_settings_submit'); }?>
		<!-- The Reset section of the option page -->
		<div class="stuffbox">
		<h3>Reset Settings</h3>
		<div class="inside">
		<p>This will reset all settings to their defaults.</p>
		<p><strong>WARNING: Any changes you have made will be lost. This cannot be undone!</strong></p>
		<!-- Show Bottom Update Button -->
		<div class="submit">
		<input type="submit" name="comment_warning_reset" value="<?php _e('Reset Settings &raquo;') ?>"/>
		</div>
		</div> <!-- class="inside" -->
		</div> <!-- class="stuffbox" -->
		<!-- End Reset Form -->
		</form>

		<!-- Start Clear Log Form (Posts to this page) -->
		<form name="comment_warning_clear_log" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__); ?>" method="post">
		<?php if (function_exists(wp_nonce_field)) {wp_nonce_field('comment_warning_clear_log_submit'); }?>
		<!-- The Clear Log section of the option page -->
		<div class="stuffbox">
		<h3>Clear Log</h3>
		<div class="inside">
		<p>This will clear the log file.</p>
		<p><strong>WARNING: All entries in the log file will be removed. This cannot be undone!</strong></p>
		<!-- Show Bottom Update Button -->
		<div class="submit">
		<input type="submit" name="comment_warning_clear_log" value="<?php _e('Clear Log &raquo;') ?>" />
		</div>
		</div> <!-- class="inside" -->
		</div> <!-- class="stuffbox" -->
		<!-- End Clear Log Form -->
		</form>

		<!-- Start Uninstall Form (Posts to this page) -->
		<form name="comment_warning_uninstall_form" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__); ?>" method="post">
		<?php if (function_exists(wp_nonce_field)) {wp_nonce_field('comment_warning_uninstall_and_deacctivate_submit'); }?>
		<!-- The Uninstall section of the option page -->
		<div class="stuffbox">
		<h3>Uninstall</h3>
		<div class="inside">
		<p>Choose this option to uninstall the plugin. Any database tables and options created by this plugin will be removed and the plugin will be deactivated. Use this only if you are not planning to use the plugin again in future. If you are likely to use the plugin again, please deactivate the plugin from the <a href="plugins.php">Plugins page</a> and your settings will be maintained.</p>
		<p><strong>WARNING: All settings and log data lost. will This cannot be undone!</strong></p>
		<!-- Show Bottom Update Button -->
		<div class="submit">
		<input type="submit" name="comment_warning_uninstall" value="<?php _e('Uninstall &raquo;') ?>"/>
		</div>
		</div> <!-- class="inside" -->
		</div> <!-- class="stuffbox" -->
		<!-- End Uninstall Form -->
		</form>

		<!-- Thank You Section -->
		<div class="stuffbox">
		<h3>Thank You</h3>
		<div class="inside">
		<p>Thank you for using the Comment Warning plugin. If you like it, please link to the <a href="http://www.scratch99.com/wordpress-plugin-comment-warning/">plugin's home page</a> so others can find out about it and/or <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=sjc@scratch99.com&currency_code=&amount=&return=&item_name=Buy+Me+A+Drink">make a donation</a>.</p>
		<p>You may also like to check out <a href="http://www.scratch99.com/wordpress-plugins-by-stephen-cronin/">my other WordPress plugins</a> or <a href="http://www.scratch99.com/feed/">subscribe to my feed</a> to learn about new plugins I'm working on.</p> 
		<p>I also provide <a href="http://www.scratch99.com/services/">wordpress and web development services</a>.
		</div> <!-- class="inside" -->
		</div> <!-- class="stuffbox" -->
		<div style="font-size:0.9em">Comment Warning Copyright 2009 by Stephen Cronin. Released under the GNU General Public License (version 2 or later).</div>
		</div>
	<?php
	}
}
// ****** END COMMENT_WARNING_ADMIN_PAGE: CREATE ADMIN PAGE ******

// ****** START CW_PAGING : CREATE PAGING FOR THE LOG FILE ******
function cw_paging($total,$cw_page,$cw_no,$cw_order,$no_of_pages) {
	// initialise the variable we'll return at the end
	$paging = '';
	
	// work out the start page (this is so we can add .... where approprite)
	if ($cw_page <= 4) {
		$start_page = 4;
	} 
	elseif ($cw_page >= $no_of_pages-6){
		$start_page = $no_of_pages-6;
	} 
	else {
		$start_page = $cw_page;
	}
	
	// show page 1 (but no link if we're on it)
	if ($cw_page != 1) {
		$paging .= '<a href="options-general.php?page=comment-warning-admin.php&cwpage=1&cwno='.$cw_no.'&cworder='.$cw_order.'">1</a>'."\n";
	} 
	else {
		$paging .= "1\n";
	}
	
	// loop through the middle 8 pages, separating them with .... or | as appropriate
	for ($i = $start_page-2; $i <= $start_page+5 && $i <= $no_of_pages - 1; $i++) {
		//if ($i <= $cw_page) {
			if ($start_page > 4 && $i ==$start_page-2){
				$paging .= ' .... ';
			} 
			else {
				$paging .= ' | ';
			}
			if ($cw_page != $i) {	
				$paging .= '<a href="options-general.php?page=comment-warning-admin.php&cwpage='.$i.'&cwno='.$cw_no.'&cworder='.$cw_order.'">'.$i.'</a>'."\n";
			} 
			else {
				$paging .= $i."\n";
			}
		//}
	}
	
	// show last page, with appropriate separator preceding it
	if ($no_of_pages > 1) {
		if ($start_page < $no_of_pages-6 && $no_of_pages > 10){
			$paging .= ' .... ';
		}
		else {
			$paging .= ' | ';
		}
		if ($cw_page != $no_of_pages) {
			$paging .= '<a href="options-general.php?page=comment-warning-admin.php&cwpage='.$no_of_pages.'&cwno='.$cw_no.'&cworder='.$cw_order.'">'.$no_of_pages.'</a>'."\n";
		} 
		else {
			$paging .= $no_of_pages."\n";
		}
	}
	// return resulting string
	return $paging;
}
// ****** END CW_PAGING: CREATE PAGING FOR THE LOG FILE ******

// ****** START CW_PER_PAGE : HOW MANY PER PAGE FOR THE LOG FILE ******
function cw_per_page($cw_no,$cw_order) {
	// initialise the variable we'll return at the end
	$per_page = '';
	// what pages we'll have
	$pages = array(10,20,30,50,100);
	foreach ($pages as $key => $value) {
		// show links to change how many entries per page.
		if ($cw_no != $value) {	
			$per_page .= '<a href="options-general.php?page=comment-warning-admin.php&cwpage=1&cwno='.$value.'&cworder='.$cw_order.'">'.$value.'</a>'."\n";
		}
		else {
			$per_page .= $value."\n";
		}
		if ($key+1 < count($pages)){
			$per_page .= ' | ';
		}
	}
	// return resulting string
	return $per_page;
}
// ****** END CW_PER_PAGE : HOW MANY PER PAGE FOR THE LOG FILE ******

// ****** START CW_ORDER_DESC : RETURN ENGLISH DESCRIPTION FOR $CW_ORDER ******
function cw_order_desc($cw_order) {
	$cw_order_desc_array = array('id'=>'Database Record No','date'=>'Date', 'localurl'=>'Target URL', 'ip'=>'IP Address', 'referer'=>'Referer', 'rule'=>'Triggering Rule');
	foreach ($cw_order_desc_array as $key => $value) {
		if ($key == $cw_order) {
			return $value;
		}		
	}
}
// ****** END CW_ORDER_DESC : RETURN ENGLISH DESCRIPTION FOR $CW_ORDER ******
?>