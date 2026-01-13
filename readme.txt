=== Prograde Oort - Enterprise Automation Engine ===
Contributors: antigravityai
Tags: webhook, automation, api, integration, workflow
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.1.0
Requires PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Transform WordPress into a powerful automation hub with webhooks, custom APIs, and intelligent workflow orchestration.

== Description ==

**Prograde Oort** is an enterprise-grade WordPress plugin that empowers you to build sophisticated automation workflows without writing plugin code. Create custom API endpoints, handle webhooks, process data ingestion, and orchestrate complex integrations - all from your WordPress admin.

= 🎯 Key Features =

* **Custom API Endpoints** - Create REST API routes with custom logic
* **Webhook Management** - Handle incoming webhooks from any service
* **Monaco Code Editor** - Professional IDE-like PHP editing with autocomplete
* **Action Scheduler Integration** - Reliable asynchronous task processing
* **WordPress Autocomplete** - Built-in suggestions for WP functions
* **Advanced Logging** - Channel-based Monolog integration with rotation
* **Import/Export** - Portable configurations across environments
* **Security Hardened** - Enterprise-grade security with proper authentication

= 💪 Perfect For =

* E-commerce webhook integrations (Shopify, WooCommerce, Stripe)
* CRM synchronization (Salesforce, HubSpot)
* Marketing automation (Mailchimp, ActiveCampaign)
* Custom API development
* Data migration and ETL workflows
* Third-party service integration

= 🔧 Built With Modern Tools =

* Monaco Editor for professional code editing
* React 18 for dynamic UI
* Symfony Expression Language for safe execution
* Guzzle HTTP Client for API requests
* Monolog for enterprise logging
* Action Scheduler for background processing

= 🎨 Developer-Friendly =

* **WP-CLI Support** - Full command-line interface
* **Composer Integration** - PSR-4 autoloading
* **Modern PHP** - Built for PHP 8.2+
* **Extensible Architecture** - Filters and actions throughout
* **Professional Tooling** - PHPStan, Rector, PHPCS ready

= 🔐 Security First =

* Capability-based access control
* API key authentication with constant-time comparison
* Input validation and sanitization
* CSRF protection
* Comprehensive audit logging
* Regular security updates

= 📚 Documentation =

Full documentation, examples, and API reference available at [GitHub](https://github.com/antigravity/prograde-oort)

= 🤝 Support =

* **Community Support** - WordPress.org forums
* **Premium Support** - Available for enterprise users
* **GitHub Issues** - Bug reports and feature requests

== Installation ==

= Minimum Requirements =

* WordPress 6.0 or greater
* PHP 8.2 or greater
* MySQL 5.7 or greater / MariaDB 10.3 or greater

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins > Add New
3. Search for "Prograde Oort"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Upload to `/wp-content/plugins/` directory
3. Extract the ZIP file
4. Activate the plugin through the 'Plugins' menu in WordPress

= Post-Installation =

1. Install **Advanced Custom Fields** (required for endpoint configuration)
2. Navigate to **Oort** in the WordPress admin
3. Create your first endpoint
4. Configure authentication and start building!

= Using Composer (Recommended for Developers) =

bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/antigravity/prograde-oort.git
cd prograde-oort
composer install --no-dev --optimize-autoloader
npm install --production
npm run build


== Frequently Asked Questions ==

= Do I need coding knowledge to use this plugin? =

Basic PHP knowledge is recommended for creating custom endpoint logic. However, we provide example templates and autocomplete suggestions to help you get started.

= Is this compatible with WooCommerce? =

Yes! Prograde Oort works great with WooCommerce and includes Action Scheduler (from WooCommerce) for reliable background processing.

= Can I use this for production sites? =

Absolutely! Prograde Oort is security-hardened and production-ready. It powers automation workflows for enterprise WordPress installations.

= What's the difference between this and other webhook plugins? =

Prograde Oort offers professional-grade features like Monaco code editor, Action Scheduler integration, comprehensive logging, and security hardening - all wrapped in a modern, maintainable codebase.

= Does it work with Gutenberg? =

Yes, the plugin is fully compatible with the block editor. Endpoint management uses Custom Post Types which work seamlessly with Gutenberg.

= Can I schedule delayed tasks? =

Yes! Using Action Scheduler, you can schedule tasks to run asynchronously, at specific times, or on recurring schedules.

= Is support for older PHP versions planned? =

No. Modern PHP features (8.2+) are essential for the security and performance characteristics this plugin delivers.

== Screenshots ==

1. **Dashboard** - Overview of endpoints, logs, and API settings
2. **Monaco Editor** - Professional code editing with autocomplete
3. **Endpoint Manager** - Configure routes, triggers, and logic
4. **Log Viewer** - Real-time monitoring of webhook activity
5. **Import/Export** - Migrate configurations across environments
6. **WP-CLI Commands** - Complete command-line interface

== Changelog ==

= 1.1.0 - 2026-01-13 =

**Major Update: Security Hardening + Monaco Editor**

* **Added:** Monaco Code Editor integration with WordPress autocomplete
* **Added:** WP-CLI commands (list, run, export, import, status)
* **Added:** Import/Export functionality for endpoint configurations
* **Added:** Comprehensive uninstall script for clean removal
* **Added:** Example scenario templates (webhook, dispatcher, ingestion)
* **Security:** Fixed authentication bypass vulnerability
* **Security:** Replaced unsafe eval() with sandboxed execution
* **Security:** Enhanced input validation and sanitization
* **Security:** CSRF protection improvements
* **Security:** Eliminated race conditions in scenario installation
* **Security:** Added batch processing to prevent memory exhaustion
* **Improved:** Logging system with channel-based organization
* **Improved:** Admin UI with professional dashboard
* **Improved:** Error messages and user feedback
* **Fixed:** Removed abandoned dependencies
* **Fixed:** ACF dependency warnings
* **Updated:** All dependencies to latest secure versions
* **Performance:** Optimized export for large datasets
* **Documentation:** Complete security audit report
* **Documentation:** Enhanced README with examples

= 1.0.0 - 2026-01-01 =

* Initial release
* Custom Post Type for endpoint management
* REST API routing system
* Basic webhook handling
* Ace Editor integration
* Monolog logging
* Guzzle HTTP client
* Action Scheduler integration
* Secure Custom Fields support
* Basic import/export

== Upgrade Notice ==

= 1.1.0 =

Major security update with Monaco Editor. All users should upgrade immediately. Backup configurations before upgrading (use Export feature).

= 1.0.0 =

Initial release. No upgrade needed.

== Privacy Policy ==

Prograde Oort does not collect, store, or transmit any user data outside of your WordPress installation.

**What We Store Locally:**

* Endpoint configurations (stored as Custom Post Types)
* API keys (stored in wp_options table)
* Execution logs (stored in wp-content/uploads/prograde-oort-logs/)
* Webhook payloads (temporarily during execution, not persisted)

**Third-Party Services:**

This plugin does not communicate with any external services unless you explicitly configure endpoints to do so (e.g., sending webhooks to external APIs).

**Data Retention:**

* Logs rotate every 7 days by default
* Endpoint configurations persist until manually deleted
* API keys persist until plugin uninstallation

**GDPR Compliance:**

Prograde Oort is GDPR-ready. You control all data processing through your custom endpoint logic.

== Development ==

= Contributing =

We welcome contributions! Please see our [GitHub repository](https://github.com/antigravity/prograde-oort) for:

* Issue tracking
* Pull request guidelines
* Development setup instructions
* Code of conduct

= Building from Source =

bash
git clone https://github.com/antigravity/prograde-oort.git
cd prograde-oort
composer install
npm install
npm run build


= Running Tests =

bash
# PHP Tests
composer test

# Security Verification
php tests/security_verification.php

# Code Quality
composer analyze


== Credits ==

* **Monaco Editor** - Microsoft
* **Action Scheduler** - Automattic / WooCommerce
* **Guzzle HTTP** - Michael Dowling
* **Monolog** - Jordi Boggiano
* **Symfony Components** - Symfony Team
* **React** - Meta Platforms, Inc.

== License ==

This plugin is licensed under the GPLv2 or later.

Copyright (C) 2026 Antigravity AI

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
