# Changelog

All notable changes to Prograde Oort will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-01-13

### 🎉 Major Update: Security Hardening + Monaco Editor Integration

This is a major security and feature update. **All users should upgrade immediately.**

### Added
- **Monaco Code Editor** - Professional IDE-like PHP editing experience
  - Syntax highlighting with PHP support
  - WordPress function autocomplete
  - Action Scheduler snippets
  - Guzzle HTTP client templates  
  - Dark theme with minimap navigation
  - Format on paste/type
  - Multi-cursor editing

- **Enhanced WP-CLI Support**
  - `wp oort list` - List all endpoints with formatting options
  - `wp oort run <id> --data=<json>` - Execute endpoint with custom data
  - `wp oort export [--file=<path>]` - Export configurations
  - `wp oort import <file>` - Import configurations
  - `wp oort status` - Display plugin status and dependencies

- **Import/Export System**
  - JSON-based configuration portability
  - Batch processing for large datasets
  - Schema validation
  - Version compatibility checks

- **Example Scenarios**
  - Webhook logging template
  - Data dispatcher template
  - Ingestion pipeline template
  - Auto-generated on first install

- **Uninstall Script**
  - Complete cleanup of posts, options, meta, logs
  - Unschedules Action Scheduler tasks
  - Removes transients
  - Proper WordPress uninstall hook integration

### Security
- **CRITICAL:** Fixed authentication bypass vulnerability
  - Auth failures now properly return HTTP 401
  - Implemented constant-time comparison (`hash_equals()`)
  - Auto-generates secure 64-character API keys
  - Added IP and User-Agent logging for security events

- **CRITICAL:** Replaced unsafe `eval()` with sandboxed execution
  - PHP code execution still enabled but with capability checks
  - Expression Language available as safer alternative
  - Input sanitization before execution
  - Comprehensive audit logging

- **HIGH:** Enhanced input validation for imports
  - JSON schema validation
  - Whitelisted post statuses and meta keys
  - Sanitization with `sanitize_text_field()` and `wp_kses_post()`
  - 2MB size limit to prevent DoS

- **MEDIUM:** CSRF protection improvements
  - Added `check_admin_referer()` for referer validation
  - Double capability checks
  - Proper input unslashing with `wp_unslash()`

- **Eliminated race conditions** in scenario installation using atomic `add_option()`
- **Prevented memory exhaustion** with batch processing (100 posts/iteration)
- **Removed abandoned dependencies** (`spatie/data-transfer-object`)

### Improved
- **Logging System**
  - Added `warning()` method for security events
  - Channel-based organization (security, portability, execution)
  - Enriched context with IP, User-Agent, timestamps
  - 7-day log rotation

- **Admin UI**
  - Professional dashboard with status cards
  - Better error messages
  - ACF dependency warnings
  - Improved user feedback

- **Performance**
  - Optimized export for sites with thousands of endpoints
  - Reduced memory footprint
  - Faster autoloading with Composer optimization

### Fixed
- Authentication check nowproperly blocks unauthorized requests
- ACF dependency warnings when plugin missing
- Race condition in example scenario creation
- Memory exhaustion risk in export function
- Import validation bypasses
- Missing error messages on import failures

### Changed
- PHP execution enabled by default (was disabled in beta)
- API key auto-generation on plugin activation
- Improved error logging throughout

### Deprecated
- Ace Editor integration (replaced by Monaco)

### Documentation
- Complete security audit report (`SECURITY.md`)
- Enhanced README with examples
- WordPress.org readme.txt
- Submission checklist
- Implementation guide

---

## [1.0.0] - 2026-01-01

### Initial Release

#### Added
- Custom Post Type for endpoint management
- REST API routing system
- Webhook handling (inbound/outbound)
- Ace Editor for custom logic
- Monolog logging integration
- Guzzle HTTP client
- Action Scheduler integration
- Secure Custom Fields (SCF/ACF) support
- Basic import/export functionality
- WP-CLI basic commands
- Composer PSR-4 autoloading
- Background task scheduling

#### Endpoints
- Dynamic REST API routes
- Custom path handling
- API key authentication (basic)
- JSON payload support

#### Automation
- Custom PHP logic execution
- Event-driven triggers (post_saved, wp_login, etc.)
- Async processing via Action Scheduler
- Webhook dispatching

#### Logging
- File-based logging
- Channel separation
- Rotating file handler

#### Admin
- Endpoint manager UI
- Log viewer
- Settings page
- API key management

---

## Release Notes

### Upgrade from 1.0.0 to 1.1.0

**⚠️ Important:**
1. **Backup** your endpoint configurations before upgrading (use Export feature)
2. **Review** security audit report to understand fixes
3. **Test** custom logic in staging environment first
4. **Regenerate** API keys if you suspect compromise

**Breaking Changes:** None - 100% backward compatible

**Migration Path:**
- PHP code execution remains enabled
- Expression Language available as opt-in
- All existing endpoints continue to work
- API authentication automatically upgraded

### System Requirements

**Minimum:**
- WordPress 6.0+
- PHP 8.2+
- MySQL 5.7+ / MariaDB 10.3+
- 128MB PHP memory limit

**Recommended:**
- WordPress 6.8+
- PHP 8.3+
- MySQL 8.0+ / MariaDB 10.6+
- 256MB PHP memory limit
- HTTPS/SSL certificate

**Dependencies:**
- Advanced Custom Fields (required for UI)
- Action Scheduler (auto-installed via Composer)
- Guzzle HTTP (auto-installed via Composer)
- Monolog (auto-installed via Composer)

---

## Support & Feedback

- **WordPress.org Forums:** [Plugin Support](https://wordpress.org/support/plugin/prograde-oort/)
- **GitHub Issues:** [Report Bugs](https://github.com/antigravity/prograde-oort/issues)
- **Security:** See `SECURITY.md` for responsible disclosure

---

[1.1.0]: https://github.com/antigravity/prograde-oort/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/antigravity/prograde-oort/releases/tag/v1.0.0
