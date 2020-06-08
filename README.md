# Smaily Wordpress plugin

Smaily email marketing and automation extension plugin for Wordpress.
Automatically generate a simple sign-up form and add new subscribers to Smaily subscribers list.

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

Online documentation with help is available at the [Knowledgebase](http://help.smaily.com/en/support/home).

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
- Default return path for both is the site`s home url.
- You can add the name field to the form by providing `show_name` attribute to the shortcode. `[smaily_for_wp_newsletter_form show_name="true"]`
