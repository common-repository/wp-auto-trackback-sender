=== WP-Auto Trackback Sender ===
Contributors: Dan Fratean
Tags: comments, trackback, admin, post, posts, plugin, seo, tracking, tag, tags
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 1.2.7

== Description ==

Because its highly customization and complex searching server side mechanisms, this plugin is one of the best <b>SEO</b> (Search Engine Optimization) tools for wordpress making your <b>site traffic</b> and <b>income</b> rise skyhigh!

= Premium plugin version =

* Up to 40 trackbacks / tag
* Customized trackback messages / post
* Customized trackback messages / tag
* Customized trackback messages / category
* more...

Please visit <a href='http://www.autowordpress.biz/' title='Wordpress Auto Plugins - WP-Auto Trackback Sender page'>http://www.autowordpress.biz/</a> for more info. 

When activated, the plugin will try to send trackbacks towards similar blog posts based on your post tags. If you edit the post, the plugin will send trackbacks only using the new defined tags. You can also tell the plugin to look for specific post language. It will do it! For more options visit WP-Auto Trackback Sender page in Settings sections of your admin page.

== Screenshots ===

1. Logo.
2. Admin panel settings.

== Installation ==

1. Upload the folder `wp-auto-trackback-sender` to the `/wp-content/plugins/` directory
2. Activate the plugin `WP-Auto Trackback Sender` through the 'Plugins' menu in WordPress
3. Configure the plugin
4. Edit or write posts.

== Changelog ==

= Version 1.2.7 =

* Warning fixes (Tx to Robert, Alan and Juan for their reports)

= Version 1.2.6 =

* Changes to our server side software. Released new version to keep version consistency

= Version 1.2.5 =

* Switched all http requests to curl
* Fixed a minor bug in Last 50 tags section (showed only first 50 tags)
* Changed urls to suit our brand new domain: <a href='http://www.autowordpress.biz'>www.autowordpress.biz</a> or <a href='http://www.autowordpress.info'>www.autowordpress.info</a>. Out old url will be online for a couple of months to allow users to upgrade their plugins.

= Version 1.2.4 =

* Minor changes in server side algorithms.

= Version 1.2.3 =

* Your blog no longer returns in trackback blogs list. (You wont send trackbacks you your blog anymore)

= Version 1.2.2 =

* Fixed minor warnings

= Version 1.2.1 =

* Added support for defining custom trackback message / post using custom fields while editiing/adding a new post. (premium)
* Added support for defining custom trackback message / post tag using plugin admin panel. (premium)
* Added support for defining custom trackback message / category tag using plugin admin panel page. (premium)
* Removed crontab support.
* Removed data storage in files. There should be no permission problems now.
* Added top menu for plugin admin panel page.
* Removed crontab section from plugin admin panel page.
* Made 'Socket open timeout' and 'Send trackback timeout' work more smoothly.

= Version 1.2 =

* Added an option to run the script secvential on page load.
* Fixed a bug in 50 last tags section.

= Version 1.1.9 =

* Fixed minor bugs.
* Detaliated FAQ/readme.txt.
* Typo fixing.

= Version 1.1.8 =

Public release

== Upgrade Notice ==

1. Deactivate older version.
2. Remove older version. (option)
3. Copy new version over older version. (see Installation)
4. Activate news version.

== Frequently Asked Questions ==

= How it works? =

1. As you publish or edit a post, the plugin gathers user defined tag.
2. When the script is runned, the plugin will send the tags to our server for processing.
3. After the data is send, the plugin will ask the server for processed results.
4. If any, the script will send trackbacks.

Please notice that there it might be a delay between sending the data and receiving results for it. It will not always take a single run of the script to get results. When the script is executed again you will get the results.

= How can i get trackbacks for posts published before the installation of the plugin? =

Just edit the post and publish it unmodified.

= If I add new tags to a published post, I will get trackbacks for them? =

Yes.

= Where can I get support? =

The support forums can be found here: http://www.autowordpress.biz/helpdesk/

= Where can I report a bug? =

Please report it on our forums at http://www.autowordpress.biz/helpdesk/

