<?php

	// Create table to log potential spammers (if it doesn't already exist)
	global $wpdb;
	$table_name = $wpdb->prefix . 'comment_warning';
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		// create the table if it doesn't already exist
		$sql = "CREATE TABLE " . $table_name . " (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			date DATETIME NOT NULL,
			localurl VARCHAR(255) NOT NULL,
			ip VARCHAR(16) NOT NULL,
			referer VARCHAR(255) NOT NULL,
			rule VARCHAR(255) NOT NULL,
			UNIQUE KEY id (id)
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	// get current options, assign defaults if not already set, update the options db
	$comment_warning_options = get_option('comment_warning_options');
	if (!isset($comment_warning_options ['message']) || $comment_warning_reset==true){ $comment_warning_options ['message'] = '
		<p><strong>COMMENT WARNING: Please read my comment policy below</strong></p>
		<p>You are seeing this message because you arrived at this site from a webpage containing "[cwterm]" in the URL. Although this may be a valid, it may also be an indication that you are a potential comment spammer. I apologise if this is not the case, but ask your indulgence in reading my comment policy below:</p>
		<ul>
		<li>Leave your name! No keywords in the Name field.</li>
		<li>Comments must contribute to the discussion  (no \'great post\' comments).</li>
		<li>Comments must be related to the post and show you\'ve taken the time to read the post.</li>
		<li>No inappropriate or offensive comments.</li>
		<li>No links to inappropriate or offensive sites.</li>
		</ul>
		<p>Failure to comply with any of the above points will result in your comment being marked as spam and deleted.</p>
		<p>Although I appreciate people taking the time to comment on my blog, I want genuine comments rather than borderline comment spam. Thank you for your patience.</p>
		<p><small>Powered by <a target="_blank" href="http://www.scratch99.com/wordpress-plugin-comment-warning/">Comment Warning</a></small><p>
		';}
	if (!isset($comment_warning_options ['triggers']) || $comment_warning_reset==true){ $comment_warning_options ['triggers'] = array('powered+by+wordpress', 'leave+a+comment', 'commentluv', 'comment+luv', 'in+comments', 'comment+on','keywordluv', 'keyword+luv', 'key+wordluv', 'key+word+luv', 'yourkeywords','your+keywords','your%20keywords','dofollow', 'do+follow', 'nofollow', 'no+follow','011831068587400451950', 'backlinkmagic.com', 'www.online-utility.org/webmaster/backlink_domain_analyzer.jsp', 'forums.digitalpoint.com/showthread.php?t=1011238', 'courtneytuttle.com/blogs-that-follow/', 'courtneytuttle.com/d-list', 'nicusor.com/do-follow-list','forums.digitalpoint.com/showthread.php?t=1006727', 'forums.digitalpoint.com/showthread.php?t=1003675', 'ishabhsood.net', 'rasimcoskun.com', 'smartpagerank.com');}
	if (!isset($comment_warning_options ['redirect_threshold']) || $comment_warning_reset==true){ $comment_warning_options ['redirect_threshold'] = 0;}
	if (!isset($comment_warning_options ['redirect_url']) || $comment_warning_reset==true){ $comment_warning_options ['redirect_url'] = 'http://www.disney.com/';}
	update_option('comment_warning_options ', $comment_warning_options );
?>