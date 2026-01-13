<?php

/**
 * Plugin Name: Prograde Oort
 * Description: Unified Webhook & Automation Engine combining Datamachine, Feed Consumer, Path Dispatch, and Custom API logic.
 * Version:           1.1.0
 * Author:            Antigravity
 * Author URI:        https://google.com
 * License:           GPL-2.0-or-later
 * Text Domain:       prograde-oort
 * Requires PHP:      8.2
 */

// Basic Security Check
if (!defined('WPINC')) {
    die;
}

// PHP 8.2+ Check
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>Prograde Oort requires PHP 8.2.0 or higher. Current version: ' . PHP_VERSION . '</p></div>';
    });
    return;
}

if (! defined('ABSPATH')) {
    exit;
}

// Define plugin constants
if (!defined('PROGRADE_OORT_PATH')) {
    define('PROGRADE_OORT_PATH', plugin_dir_path(__FILE__));
}
if (!defined('PROGRADE_OORT_URL')) {
    define('PROGRADE_OORT_URL', plugin_dir_url(__FILE__));
}

// Use Composer Autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Initialize the plugin with a safety guard
add_action('plugins_loaded', function () {
    if (class_exists('\ProgradeOort\Core\Bootstrap')) {
        \ProgradeOort\Core\Bootstrap::instance();
    }
});
