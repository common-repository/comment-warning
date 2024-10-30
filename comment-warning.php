<?php
/*
Plugin Name: Comment Warning
Plugin URI: http://www.scratch99.com/wordpress-plugin-comment-warning/
Description: Detects if visitors arrived via a Dofollow list or search for KeywordLuv, CommentLuv or DoFollow and warns them not to spam
Version: 1.12
Date: 18th August 2009
Author: Stephen Cronin
Author URI: http://www.scratch99.com/

   Copyright 2009  Stephen Cronin  (email : sjc@scratch99.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Acknowledgements: This plugin is based, in part, on code by the following people:
- RT Cunningham (http://connectcontent.com/blog/filtering-out-non-search-visitors-in-wordpress/) 
- Donncha O Caoimh's (http://ocaoimh.ie/win-a-trip-to-disneyland/)
*/

// ****** SETUP ACTIONS AND FILTERS ******
// note more filters included below if comment warning is triggered
add_action('activate_' . dirname(plugin_basename(__FILE__)) . '/' . basename(__FILE__), 'create_comment_warning_options');
add_action('admin_menu', 'comment_warning_admin');
add_filter('comment_notification_text', 'comment_warning_email', 10, 2 );
add_filter('comment_moderation_text', 'comment_warning_email', 10, 2 );
// **************************************

// ****** FUNCTION TO CREATE OPTIONS AND DEFAULTS ON ACTIVATION ******
// in separate file so it's not loaded for the average visitor
function create_comment_warning_options() {
	require_once('comment-warning-activate.php');
}
// ******************************************************************

// ****** FUNCTION TO ADD ADMIN PAGEs ******
// in separate file so it's not loaded for the average visitor
function comment_warning_admin() {
	// load the css for the log, but only for pages with cwpage in the URL
	if (isset($_GET['cwpage'])){
		$plugin_name = dirname(plugin_basename(__FILE__));
		wp_enqueue_style('comment-warning-log', plugins_url($plugin_name.'/includes/comment-warning-log.css'));
	}
	require_once('comment-warning-admin.php');
}
// ****************************************

// ****** CODE TO ADD SETTINGS LINK TO PLUGIN PAGE (2.8 Only) ******
function comment_warning_settings_link($links, $file) {
	// Static so we don't call plugin_basename on every plugin row. Thanks to Joost de Valk's Sociable for this code.
	static $this_plugin;
	if (!$this_plugin) {
		$this_plugin = plugin_basename(__FILE__);
	}
	if ($file == $this_plugin) {
		$settings_link = '<a href="options-general.php?page=comment-warning-admin.php">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
 	return $links;
}
// Add the filters for the Settings link for 2.8
add_filter( 'plugin_action_links', 'comment_warning_settings_link', 10, 2 );
// ****** END CODE TO ADD SETTINGS LINK TO PLUGIN PAGE ******

// check if spammer potential spammer
if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
	if (!stristr($_SERVER['HTTP_REFERER'],get_bloginfo('url'))) {
		// Get the options
		$comment_warning_options = get_option('comment_warning_options');

		// loop through all the potential triggers and see if any match
		foreach($comment_warning_options['triggers'] as $value) {
			if (stristr($_SERVER['HTTP_REFERER'],$value) && !stristr($_SERVER['REQUEST_URI'],$value)) {
				// Update database with record of triggered visit, but only if table exists
				global $wpdb;
				$table_name = $wpdb->prefix . 'comment_warning';
				if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
					$insert = "INSERT INTO " . $table_name . " (date, localurl, ip, referer, rule) " . "VALUES (now(), '" . addslashes($_SERVER['REQUEST_URI']) . "','" . addslashes($_SERVER['REMOTE_ADDR']) . "','" . addslashes($_SERVER['HTTP_REFERER']) . "','". $value ."')";
					$results = $wpdb->query( $insert );
				}

				// check if we're redirecting
				if ($comment_warning_options['redirect_threshold'] == 1) {
					header('Location: '.$comment_warning_options['redirect_url']);
					die();
				}
				elseif ($comment_warning_options['redirect_threshold'] > 1) {  // negative will be treated as 0
					if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
						$prior_convictions = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE ip = '" .$_SERVER['REMOTE_ADDR'] ."'");
						if ($comment_warning_options['redirect_threshold'] <= $prior_convictions) {
							header('Location: '.$comment_warning_options['redirect_url']);
							die();
						}
					}
				}
			
				// show warning to anyone who's left (ie not redirected)
				$plugin_name = dirname(plugin_basename(__FILE__));
				wp_enqueue_style('jqModal-style', plugins_url($plugin_name.'/includes/jqModal.css'));
				wp_enqueue_script('jquery');
				wp_enqueue_script('jqModal', plugins_url($plugin_name.'/includes/jqModal.js'), array('jquery'));
				add_filter('the_content', 'comment_warning',100000);
				add_action('wp_footer', 'comment_warning_footer');
				$comment_warning = '
				<div class="jqmWindow" id="dialog">
				<a href="#" class="jqmClose">Close</a>
				' . html_entity_decode($comment_warning_options['message']) . '
				</div>
				';
				$comment_warning = str_replace('[cwterm]',$value,$comment_warning);

				// There's no need to check further, so break out of loop
				break;
			}
		}
	}
}

// ****** START COMMENT_WARNING: ADD WARNING TO CONTENT FOR POSTS & PAGES ******
function comment_warning($content) {
	if (is_single() || is_page()){
		global $comment_warning;
		return $comment_warning . $content;
	}
	else {
		return $content;
	}
}
// ****** END COMMENT_WARNING: ADD WARNING TO CONTENT FOR POSTS & PAGES ******

// ****** START COMMENT_WARNING_FOOTER: ADD STUFF TO FOOTER ******
function comment_warning_footer() {
	// add warning to footer for everything that's not a page or post (and to front page if static)
	if ( (!is_single()&&!is_page()) || (is_front_page()) ){
		global $comment_warning;
		echo $comment_warning;
	}
	// add JavaScript to all pages that are triggered (work for above and comment_warning function)
	echo '
	<script type="text/javascript">
		jQuery().ready(function($){
			$(\'div#dialog\').hide();
			$(\'div#dialog\').jqm({modal:true});
			$(\'div#dialog\').jqmShow();
			$(document).keydown( function( e ) {
				if( e.which == 27) {  // escape, close box
					$(".jqmWindow").jqmHide();
				}
			}); 
		});
	</script>
	';
}
// ****** END COMMENT_WARNING_FOOTER: ADD STUFF TO FOOTER ******

// ****** END CODE TO ADD WARNED TO COMMENT AUTHOR NAME ******
function comment_warning_warned($author) {
	global $comment, $wpdb;
	$table_name = $wpdb->prefix . 'comment_warning';
	if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
		$comment_author_IP = $comment->comment_author_IP;
		$warned = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE ip = '$comment_author_IP'");
		if ($warned > 0) {
			$author .= " (WARNED)";
		}
	}
	return $author;
}
if (is_admin) {
	add_filter ('comment_author', 'comment_warning_warned');
}
// ****** END CODE TO ADD WARNED TO COMMENT AUTHOR NAME ******

// ****** START COMMENT_WARNING_EMAIL: ADD 'WARNED' TO EMAILS ABOUT COMMENTS ******
function comment_warning_email($text, $comment_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'comment_warning';
	if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
		// should probably use a join instead of two DB calls	
		$comment_author_IP = $wpdb->get_var("SELECT comment_author_IP FROM $wpdb->comments WHERE comment_ID='$comment_id'");
		$warned = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE ip = '$comment_author_IP'");
		if ($warned > 0) {
			$text .= "\r\nWARNED: This comment author has been warned by Comment Warning (at some point in the past).\r\n";
		}
	}
	return $text;
}
// ****** END COMMENT_WARNING_EMAIL: ADD 'WARNED' TO EMAILS ABOUT COMMENTS ******

?>