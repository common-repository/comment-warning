=== Plugin Name ===
Contributors: StephenCronin
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=sjc@scratch99.com&currency_code=&amount=&return=&item_name=WP-PD/
Tags: comment_spam, spam, commentluv, keywordluv, dofollow, no-nofollow, nofollow 
Requires at least: 2.7.0
Tested up to: 2.8.4
Stable tag: 1.12

Checks where visitors come from and gives them a warning if they are likely to be comment spammers. For DoFollow and exDoFollow blogs.

== Description ==

[Comment Warning](http://www.scratch99.com/wordpress-plugin-comment-warning/ "WordPress plugin to help deal with comment spam") is a plugin for blogs that currently use a DoFollow plugin, CommentLuv, or KeywordLuv (or have used such a plugin in the past). It detects visitors arriving from URLs that indicate that they are likely to be potential comment spammers and 'warns' them of the blog's comment policy, via a JavaScript modal 'popup' (not a real popup).

Both the list of triggers and the message are customisable, allowing you to control who is shown the message and what they are shown.

It is also possible to redirect potential spammers to a URL of your choice, either immediately (bypassing the warning) or after a certain number of potential spam visits from the same IP address. Note: the number of visits calculated only includes visits where a trigger is tripped (not if the same IP address visits your site via a different method) and includes page refreshes.

Other Features:

* Records visitors who have been warned, allowing you too see instances of warnings (in the Log).
* Allows you to navigate directly from the Log to the Edit Comments page for warned commentators IP address, so you can see any comments they've left.
* Allows you to see if a comment author has been warned, when browsing comments in the Admin panel.
* Allows you to see if a comment author has been warned when you get new comment and moderation required emails.

Compatibility:
I suspect that this **plugin will NOT be compatible with WP Super Cache** at this point. I will be doing some testing in the near future and, if necessary, changing the plugin so it does work with WP Super Cache. 

Support:
This plugin is officially not supported (due to my time constraints), but if you leave a comment on the [Comment Warning Support page](http://www.scratch99.com/wordpress-plugin-comment-warning/comment-warning-support-page/) or [contact me](http://www.scratch99.com/contact/), I should be able to help.

== Installation ==

1. Download the plugin file and unzip it.
1. Upload the `comment-warning` folder to the `wp-content/plugins/` folder.
1. Activate the Comment Warning plugin within WordPress.

Alternatively, you can install the plugin automatically through the WordPress Admin interface.

== Frequently Asked Questions ==

= Are there likely to false postives? =

The default set of triggers have been chosen to be as 'wide' as possible, catching as many potential comment spammers as possible.

One side effect of this is that there may be some false positives. In trialling the plugin, I have encountered almost no false positives, but the possibility exists. It is therefore not recommended to redirect users immediately. The default warning message acknowledges that false positives are possible and asks the user's indulgence in reading the comment policy.

To limit false positives, the comment warning (or redirection) will not trigger for terms that are in both the refering URL and in your page's URL. If you have a post with dofollow in the URL and a visitor arrives from another site with dofollow in the URL, there is a decent chance that they are not a spammer.

== Screenshots ==

No screenshots exist at this time, but go to the [Comment Warning demo page](http://www.scratch99.com/comment-warning-demo/) for a demo of the plugin.

== Changelog ==

= 1.12 =
* Added a count of comments left by the potential spammer (in the Log).

= 1.11 =
* Changed Powered by Comment Warning Link in default message to open in new tab rather than the current one.
* Added (WARNED) to the end of the comment author name in the Admin panel, so you can see if they've been warned.
* Added a message to new comment and moderation required emails (to admin), so you can see if they've been warned.

= 1.1 =
* Moved the Clear Log button from the Log page to the Settings page.
* Changed the default log order from Database record order to Date order.
* Changed the date order (in the log) to descending, so that the latest entries show first.
* Linked the IP address in the log to the Edit Comments page for that IP address, so you can see what comments they've left.

= 1.0 =
* Initial Release

== Credits ==

This plugin builds upon code from the following sources:

* RT Cunningham's [How to Reduce AdSense Impressions while Improving CTR](http://www.untwistedvortex.com/2008/09/30/how-to-reduce-adsense-impressions-while-improving-the-click-through-rate-ctr/) post.
* Donncha O Caoimh's [Win a trip to Disneyland](http://ocaoimh.ie/win-a-trip-to-disneyland/) post.
* Donncha O Caoimh's [Comment Referrers plugin](http://ocaoimh.ie/tag/comment-referrers/).
