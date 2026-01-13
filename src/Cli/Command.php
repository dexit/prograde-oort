<?php

namespace ProgradeOort\Cli;

if (! class_exists('WP_CLI_Command')) {
    return;
}

/**
 * Manage Prograde Oort endpoints and automation workflows
 */
class Command extends \WP_CLI_Command
{
    /**
     * Run a specific Oort endpoint.
     *
     * ## OPTIONS
     *
     * <id>
     * : The ID of the oort_endpoint post.
     *
     * [--data=<json>]
     * : Optional JSON data to pass to the endpoint
     *
     * ## EXAMPLES
     *
     *     wp oort run 123
     *     wp oort run 123 --data='{"foo":"bar"}'
     *
     * @when after_wp_load
     */
    public function run($args, $assoc_args)
    {
        $post_id = $args[0];
        $post = get_post($post_id);

        if (! $post || $post->post_type !== 'oort_endpoint') {
            \WP_CLI::error("Invalid Oort Endpoint ID: $post_id");
        }

        \WP_CLI::log("Executing Endpoint: " . $post->post_title);

        $logic = get_field('oort_logic', $post_id);

        // Parse data if provided
        $data = ['cli' => true];
        if (isset($assoc_args['data'])) {
            $parsed = json_decode($assoc_args['data'], true);
            if ($parsed) {
                $data = array_merge($data, $parsed);
            }
        }

        $result = \ProgradeOort\Automation\Engine::instance()->run_flow("endpoint_{$post_id}", $data, $logic);

        if ($result['status'] === 'success') {
            \WP_CLI::success("Execution finished.");
            if (isset($result['result'])) {
                \WP_CLI::log("\nResult:");
                print_r($result['result']);
            }
        } else {
            \WP_CLI::error("Execution failed: " . ($result['message'] ?? 'Unknown error'));
        }
    }

    /**
     * List all Oort endpoints.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format (table, json, csv, yaml, ids)
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - csv
     *   - yaml
     *   - ids
     * ---
     *
     * ## EXAMPLES
     *
     *     wp oort list
     *     wp oort list --format=json
     *
     * @when after_wp_load
     */
    public function list($args, $assoc_args)
    {
        $endpoints = get_posts([
            'post_type' => 'oort_endpoint',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ]);

        $items = [];
        foreach ($endpoints as $post) {
            $items[] = [
                'ID' => $post->ID,
                'Title' => $post->post_title,
                'Status' => $post->post_status,
                'Type' => get_post_meta($post->ID, 'oort_route_type', true) ?: 'N/A',
                'Path' => get_post_meta($post->ID, 'oort_route_path', true) ?: 'N/A',
                'Trigger' => get_post_meta($post->ID, 'oort_trigger', true) ?: 'N/A'
            ];
        }

        if (empty($items)) {
            \WP_CLI::warning("No endpoints found.");
            return;
        }

        \WP_CLI\Utils\format_items(
            $assoc_args['format'] ?? 'table',
            $items,
            ['ID', 'Title', 'Status', 'Type', 'Path', 'Trigger']
        );
    }

    /**
     * Export endpoint configurations.
     *
     * ## OPTIONS
     *
     * [--file=<path>]
     * : Save to file instead of stdout
     *
     * ## EXAMPLES
     *
     *     wp oort export
     *     wp oort export --file=oort-config.json
     *
     * @when after_wp_load
     */
    public function export($args, $assoc_args)
    {
        $json = \ProgradeOort\Integration\Portability::export_all();

        if (isset($assoc_args['file'])) {
            file_put_contents($assoc_args['file'], $json);
            \WP_CLI::success("Exported to: " . $assoc_args['file']);
        } else {
            echo $json;
        }
    }

    /**
     * Import endpoint configurations.
     *
     * ## OPTIONS
     *
     * <file>
     * : JSON file to import
     *
     * ## EXAMPLES
     *
     *     wp oort import oort-config.json
     *
     * @when after_wp_load
     */
    public function import($args, $assoc_args)
    {
        $file = $args[0];

        if (!file_exists($file)) {
            \WP_CLI::error("File not found: $file");
        }

        $json = file_get_contents($file);

        if (\ProgradeOort\Integration\Portability::import_data($json)) {
            \WP_CLI::success("Import completed successfully.");
        } else {
            \WP_CLI::error("Import failed. Check logs for details.");
        }
    }

    /**
     * Display plugin status and configuration.
     *
     * ## EXAMPLES
     *
     *     wp oort status
     *
     * @when after_wp_load
     */
    public function status($args, $assoc_args)
    {
        \WP_CLI::log("=== Prograde Oort Status ===\n");

        // Version
        \WP_CLI::log("Version: 1.1.0");

        // API Key
        $api_key = get_option('prograde_oort_api_key');
        \WP_CLI::log("API Key: " . ($api_key ? substr($api_key, 0, 16) . '...' : 'Not set'));

        // Endpoint count
        $count = wp_count_posts('oort_endpoint');
        \WP_CLI::log("Endpoints: " . ($count->publish + $count->draft));

        // Dependencies
        \WP_CLI::log("\nDependencies:");
        \WP_CLI::log("  ACF: " . (function_exists('acf_add_local_field_group') ? '✓' : '✗'));
        \WP_CLI::log("  Action Scheduler: " . (function_exists('as_enqueue_async_action') ? '✓' : '✗'));
        \WP_CLI::log("  Guzzle: " . (class_exists('GuzzleHttp\Client') ? '✓' : '✗'));
        \WP_CLI::log("  Monolog: " . (class_exists('Monolog\Logger') ? '✓' : '✗'));

        // Log directory
        $log_dir = WP_CONTENT_DIR . '/uploads/prograde-oort-logs/';
        \WP_CLI::log("\nLog Directory: " . $log_dir);
        \WP_CLI::log("  Exists: " . (is_dir($log_dir) ? '✓' : '✗'));
        if (is_dir($log_dir)) {
            $log_files = glob($log_dir . '*.log');
            \WP_CLI::log("  Log Files: " . count($log_files));
        }
    }
}

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('oort', __NAMESPACE__ . '\\Command');
}
