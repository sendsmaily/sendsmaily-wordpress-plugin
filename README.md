Smaily email marketing and automation plugin for Wordpress.
Automatically generate a simple sign-up form and add new subscribers to Smaily subscribers list.


## Smaily for WordPress — Deprecation notice

Smaily for WordPress is officially deprecated. It is no longer maintained, and no further updates or security patches will be provided.
We have released Smaily Connect, a new plugin that combines support for WordPress, WooCommerce, Contact Form 7, and Elementor in a single package.

Please migrate now!

In your WordPress admin go to Plugins → Installed Plugins.
Deactivate and Delete Smaily for WordPress.
Go to Plugins → Add New, search for “Smaily Connect”, then Install and Activate.
Open Smaily Connect and re-connect your Smaily account (subdomain, API user, API password).

For assistance, contact support@smaily.com.

[Smaily Connect](https://wordpress.org/plugins/smaily-connect/)

## Features

### Wordpress Newsletter Subscription

- Add new subscribers to Smaily subscribers list.
- Select autoresponder to send automated emails.
- Shortcode for adding newsletter form.

### Subscription Widget

- Option for custom advanced HTML form.
- Functional with NoScript users.
- Send custom form values to Smaily.

## Requirements

- PHP 5.6+
- Wordpress 4.0+

## Documentation & Support

Online documentation with help is available at the [Knowledgebase](https://smaily.com/help/user-manuals/).

## Contribution

All development for Smaily for Wordpress is handled via [GitHub](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/).

Opening new issues and submitting pull requests are welcome.

## Installation

- Install as regular plugin.
- Open smaily plugin settings from admin side panel menu "Smaily".
- Validate your credentials.
- Go to Appearance -> Widgets.
- Place "Smaily newsletter subscription" widget where you wish.
- Using advanced tab in smaily settings will render advanced form.

## Shortcode

- Navigate to a Pages section and select a page to edit for adding newsletter form with shortcode.
- Add a `[smaily_for_wp_newsletter_form]` shortcode to a preferred location.
- You can choose a page to return in case of successful subscription by providing `success_url` attribute to the shortcode. `[smaily_for_wp_newsletter_form success_url="http://www.example.com/?page_id=2"]`
- You can also choose a page to return in case of error by providing `failure_url` attribute to the shortcode. `[smaily_for_wp_newsletter_form failure_url="http://www.example.com/?page_id=2"]`
- Default return path for both is the site's home URL.
- You can add the name field to the form by providing `show_name` attribute to the shortcode. `[smaily_for_wp_newsletter_form show_name="true"]`
- Targeting of a specific automation can be done by adding `autoresponder_id` attribute to the shortcode. `[smaily_for_wp_newsletter_form autoresponder_id="123"]`
