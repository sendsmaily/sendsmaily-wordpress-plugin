=== Smaily for WP ===
Contributors: sendsmaily, kaarel, tomabel
License: GPLv2 or later
Requires PHP: 5.6
Requires at least: 4.0
Stable tag: 2.1.0
Tags: widget, plugin, sidebar, api, mail, email, marketing, smaily
Tested up to: 5.4.1

Smaily newsletter subscription plugin for WordPress

== Description ==

Smaily email marketing and automation extension plugin for Wordpress.
Automatically generate a simple sign-up form and add new subscribers to Smaily subscribers list.

= Features =

**Wordpress Newsletter Subscribers**

- Add new subscribers to Smaily subscribers list.
- Select autoresponder to send automated emails.
- Shortcode for adding newsletter form.

**Subscription Widget**

- Option for custom advanced HTML form.
- Functional even with NoScript users.
- Supports sending custom form values to Smaily.

= Documentation & Support =

Online documentation with help is available at the [Knowledgebase](http://help.smaily.com/en/support/home).

= Contribute =

All development for Smaily for Wordpress is handled via [GitHub](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/).
Opening new issues and submitting pull requests are welcome.

== Installation ==

1. Upload or extract the `sendsmaily-wordpress-plugin` folder to your site's `/wp-content/plugins/` directory. You can also use the **Add new** option found in the **Plugins** menu in WordPress.
2. Activate the plugin from the **Plugins** menu in WordPress.

== Frequently Asked Questions ==

= How to set up widget for signup form? =

1. Validate your Smaily credentials in Smaily settings menu.
2. Move to Appearance -> Widget menu from admin page sidepanel.
3. Add Smaily Newsletter widget to your preferred location on page.
4. Select Title for your subscribe newsletter form.

If you have added Form Submitted automation trigger from Smaily site under Automation tab you can see all availabe autoresponders in your widget settings.

There is no need to select autoresponder for widget form, but if you want to customize different approach from opt-in automation trigger you can do that.
When no autoresponder selected regular opt-in workflow will run. You can add delay, filter by field and send email after subscription. For that edit settings in Smaily automation page.

= How to add shortcode for signup form? =

1. Navigate to a Pages section and select a page to edit for adding newsletter form with shortcode.
2. Add a [smaily_newsletter_form] shortcode to a preferred location.
3. You can choose a page to return in case of successful subscription by providing success_url attribute to the shortcode. [smaily_newsletter_form success_url="http://www.example.com/?page_id=2"]
4. You can also choose a page to return in case of error by providing failure_url attribute to the shortcode. [smaily_newsletter_form failure_url="http://www.example.com/?page_id=2"]
5. Default return path for both is the site`s home url.
6. You can add the name field to the form by providing show_name attribute to the shortcode. [smaily_newsletter_form show_name="true"]

== Screenshots ==

1. Smaily plugin in admin view.
2. Smaily plugin in admin view with validated credentials.
3. Smaily plugin in widgets view.
4. Smaily basic newsletter form.
5. Smaily plugin shortcode from.

== Changelog ==

= 2.1.0 =
- Add shortcode for adding newsletter form to WordPress pages.

= 2.0.2 =
- Include version info in requests made against Smaily API.

= 2.0.1 =
- Maintenance release to update the branding on Wordpress.org.

= 2.0.0 =
* First public release.
