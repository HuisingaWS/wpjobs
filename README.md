wpjobs
======

Contributors: aaronhuisinga
Tags: employment, jobs, management
Requires at least: 3.4
Tested up to: 3.5.1
Stable tag: trunk

Description
-----------
Integrates a simple system to list job openings, display them on a sleek and organized page, and accept applications via pre-formatted email messages.

Uses the short code `[WPEM]` to render the job listings with the proper tags defined in the settings panel.
Uses the short code `[EMAPPLY]` to render the job application form for the selected job opening.

Dependencies
------------
1. Active [Wordpress](http://wordpress.org/) installation (tested on 3.5+).
2. Uses [PHPMailer](https://github.com/PHPMailer/PHPMailer) to format and mail the received applications.
3. Current table, list, and button styles depend on [Bootstrap](http://getbootstrap.com). This is temporary, and will be changed in favor of custom styles later on.

Installation
------------
1. Upload the `wp-openings.zip` file to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Adjust settings under `Settings -> Job Openings`
4. Create some job listings under `Employment` custom post type
	1. Be sure to give the post a tag of the company that this listing is for (valid tags are defined in Settings).
	2. Also be sure to check the Meta information under the post body. These are settings unique to the job listing post type.
5. Create a new WP page, and use the short code `[WPEM]` in post content to pull in active listings and format them automatically.
6. Create another new WP page, and use the short code `[EMAPPLY]` to let viewers apply for positions that are listed.