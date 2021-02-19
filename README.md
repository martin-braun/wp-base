# WP-BASE Blueprint

This is a localwp ([Local by Flywheel](https://localwp.com/)) project to use as blueprint for developers. It comes with some pre-installed plugins and changed settings to provide a better starter for WordPress.

It uses Elementor as default page builder, but can be replaced. In fact, since this is a starter, it should be modified in any case.

## Features

- [Hello Elementor](https://wordpress.org/themes/hello-elementor/) base theme that can be replaced, easily
- Custom pre-selected and implemented child theme with SCSS support (using [SCSS-Library](https://wordpress.org/support/plugin/scss-library/)) that awaits your custom edits
- Security plugins like [Wordfence](https://wordpress.org/plugins/wordfence/) and [Wordfence Login Security](https://wordpress.org/plugins/wordfence-login-security/)
- Quality of life plugins like [WP Admin Cache](https://wordpress.org/plugins/wp-admin-cache/) and [Admin Menu Search](https://wordpress.org/plugins/admin-menu-search/)

## Requirements

- [Local](https://localwp.com/)

## Installation

### Local

- Create a new site in Local.
- Quit Local
- Replace Local Site directory with this directory
- Restart Local
- Clone the site to start a project from this template
- Remove the hidden `.git` folder from the cloned site
- Trust the certificate of the cloned site
- Start the cloned site
- Login using `system` (Password: `$wpadmin1234`)

## Test

- Start Page in Local

## Build

- Set `WP_DEBUG` to `false` in [_app/public/wp-config.php_](./app/public/wp-config.php)
- Now deploy the [_app/public_](./app/public) folder and import the database that is stored at [_app/sql/local.sql_](./app/sql/local.sql) by Local

## Next steps

After cloning this blueprint, there are some recommend steps to do in any case. Things that are not listed here are individual.

### Preparation

- Change the E-Mail and Password of the `system` account

### Wordfence

- Change the E-Mail for security notifications in Wordfence
- Enable Firewall, starting with Learning Mode
- Add reCAPTCHA to Login Security

### Yoast SEO

- Configure everything

### General

- Enable Page to be visible by search engines
- Update [_app/public/wp-config.php_](./app/public/wp-config.php)

## Important notices

### Avoid SCSS Library's development mode in production use

The child theme uses SCSS files that compile to a bundled CSS file using the _SCSS Library_ plugin. The output CSS files should not be included in the caching system to avoid the requirement to clear the cache to see changes when being in development mode of _SCSS Library_.

## Replicate

In this section you can read how this project was formed. Get inspired and create your own.

### WordPress

- Create the WordPress page using [Local by Flywheel](https://localwp.com)
- Apply new constants in [_wp-config.php_](./app/public/wp-config.php)
