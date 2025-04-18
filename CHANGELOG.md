# Changelog

### 3.1.6

- Improve module security by escaping all of the basic newsletter form display fields.

### 3.1.5

- Fix an issue where administrator scripts are not loaded when DISALLOW_FILE_EDIT configuration option is enabled.

### 3.1.4

- Fix a security issue where users who didn't have correct privileges to edit the plugin settings were allowed to do so through console in administrators interface. https://www.cve.org/CVERecord?id=CVE-2024-54286

### 3.1.3

- Fix an issue where blocks section is not rendering due to autoresponders JSON response bad formatting.

### 3.1.2

- Resolve plugin notices on plugin activation and page editing [[#139](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/139)]

### 3.1.1

- WordPress 6.2 compatibility

### 3.1.0

- Adds basic support for Gutenberg blocks [[#129](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/129)]

### 3.0.7

- Fix automation workflows are fetched on every operation in WordPress [[#121](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/121)]

### 3.0.6

- Update user manual links - [[#118](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/118)]

### 3.0.5

- Test compatibility with WordPress 5.8 - [[#115](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/115)]

### 3.0.4

- Fix advanced form when using shortcode - [[#108](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/108)]

### 3.0.3

- Test compatibility with WordPress 5.7 - [[#95](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/issues/95)]

### 3.0.2

- Remove unnecessary static content limiting [[#102](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/102)]

### 3.0.1

- Hardcoded development plugin name breaks production CSS and JS loading [[#99](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/99)]

### 3.0.0

- Autoresponders can be now be configured in widget settings and per widget - [[#84](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/84)]
- Fix spelling of plugin's admin page title - [[#91](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/91)]
- Plugin migrations are now automatically executed when plugin is updated - [[#88](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/pull/88)]

### 2.3.0

- Restructured plugin localizations - [[#72](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/issues/72)]
- Added support for WordPress 5.6 - [[#73](https://github.com/sendsmaily/sendsmaily-wordpress-plugin/issues/73)]

### 2.2.1

- Fix opt-in form not using selected automation ID [#64]
- Fix shortcode not having attribute to provide automation ID [#65]

### 2.2.0

- Also use Polylang and WordPress's locale when determining language field.
- Add link to API user creation guide for admin form.
- Rename username & password to API username & API password.
- Removed form class name 'container', which resulted in unexpected behavior.
- Enable HTML5 validation for basic form's email field.
- Replace label with placeholder for name field.
- Fixed a bug, which deleted autoresponders when refreshing them.

### 2.1.0

- Add shortcode for adding newsletter form to WordPress pages.

### 2.0.2

- Include version info in requests made against Smaily API.

### 2.0.1

- Maintenance release to update the branding on Wordpress.org.

### 2.0.0

- First public release.
