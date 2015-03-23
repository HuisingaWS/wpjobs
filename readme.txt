=== WordPress Job Openings & Application Management ===
Contributors: Aaron Huisinga
Tags: employment, jobs, applications, human resources
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 0.5.5

A WordPress plugin allowing administrators and editors to post available job openings, accept resumes & applications, and send batch emails to applicants.

== Description ==

Integrates a simple system to list job openings, display them on a sleek and organized page, and accept applications & resumes via pre-formatted email messages.

Uses the short code `[WPEM]` to render the job listings with the proper tags defined in the settings panel.
Uses the short code `[EMAPPLY]` to render the job application form for the selected job opening.

Note: When creating job listings, be sure to fill out the job meta data (contact email, wage, resume, etc)! Without some of them, you may experience errors.

== Installation ==

1. Upload the `wp-openings.zip` file to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Adjust settings under `Settings -> Job Openings`
4. Create some job listings under `Employment` custom post type
	1. Be sure to give the post a tag of the company that this listing is for (valid tags are defined in Settings).
	2. Also be sure to check the Meta information under the post body. These are settings unique to the job listing post type.
5. Create a new WP page, and use the short code `[WPEM]` in post content to pull in active listings and format them automatically.
6. Create another new WP page, and use the short code `[EMAPPLY]` to let viewers apply for positions that are listed.