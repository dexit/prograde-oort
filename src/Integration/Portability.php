<?php

namespace ProgradeOort\Integration;

/**
 * Utility for Importing and Exporting Endpoint Configurations
 */
class Portability
{
    public static function export_all()
    {
        $batch_size = 100;
        $paged = 1;
        $export_data = [];
        $total_exported = 0;

        do {
            $endpoints = get_posts([
                'post_type' => 'oort_endpoint',
                'posts_per_page' => $batch_size,
                'paged' => $paged++,
                'post_status' => 'any',
                'fields' => 'all' // Get full post objects
            ]);

            foreach ($endpoints as $post) {
                $meta = get_post_custom($post->ID);
                // Clean up meta (standard WP meta often contains arrays)
                $cleaned_meta = [];
                foreach ($meta as $key => $value) {
                    if (strpos($key, '_') === 0 && !in_array($key, ['_logic_code'])) continue; // Skip internal WP meta except our logic
                    $cleaned_meta[$key] = maybe_unserialize($value[0]);
                }

                $export_data[] = [
                    'post_title'   => $post->post_title,
                    'post_content' => $post->post_content,
                    'post_status'  => $post->post_status,
                    'post_type'    => $post->post_type,
                    'meta'         => $cleaned_meta
                ];
                $total_exported++;
            }
        } while (count($endpoints) === $batch_size);

        \ProgradeOort\Log\Logger::instance()->info(
            "Export completed",
            ['total_endpoints' => $total_exported],
            'portability'
        );

        return json_encode([
            'version'   => '1.1',
            'timestamp' => time(),
            'count'     => $total_exported,
            'data'      => $export_data
        ], JSON_PRETTY_PRINT);
    }

    public static function import_data($json_data)
    {
        $decoded = json_decode($json_data, true);

        // Validate schema structure
        if (!is_array($decoded) || !isset($decoded['version'], $decoded['data'])) {
            \ProgradeOort\Log\Logger::instance()->error(
                'Import failed: Invalid JSON structure',
                [],
                'portability'
            );
            return false;
        }

        // Validate data is an array
        if (!is_array($decoded['data'])) {
            return false;
        }

        // Version compatibility check
        if (version_compare($decoded['version'], '1.0', '<')) {
            \ProgradeOort\Log\Logger::instance()->error(
                'Import failed: Unsupported version',
                ['version' => $decoded['version']],
                'portability'
            );
            return false;
        }

        // Whitelist of allowed post statuses and meta keys
        $allowed_statuses = ['publish', 'draft', 'pending', 'private'];
        $allowed_meta_keys = ['route_type', 'route_path', 'logic_code', 'oort_trigger', 'oort_route_type', 'oort_route_path', 'oort_logic'];

        $imported_count = 0;
        $skipped_count = 0;

        foreach ($decoded['data'] as $item) {
            // Validate required fields
            if (!isset($item['post_title']) || !isset($item['post_type'])) {
                $skipped_count++;
                continue;
            }

            // Ensure post type is correct
            if ($item['post_type'] !== 'oort_endpoint') {
                $skipped_count++;
                continue;
            }

            // Sanitize and validate post status
            $post_status = isset($item['post_status']) && in_array($item['post_status'], $allowed_statuses, true)
                ? $item['post_status']
                : 'draft';

            // Insert post with sanitized data
            $post_id = wp_insert_post([
                'post_title'   => sanitize_text_field($item['post_title']),
                'post_content' => wp_kses_post($item['post_content'] ?? ''),
                'post_status'  => $post_status,
                'post_type'    => 'oort_endpoint'
            ], true);

            // Check for insertion errors
            if (is_wp_error($post_id)) {
                \ProgradeOort\Log\Logger::instance()->error(
                    'Failed to import endpoint',
                    ['title' => $item['post_title'], 'error' => $post_id->get_error_message()],
                    'portability'
                );
                $skipped_count++;
                continue;
            }

            // Import meta data with validation
            if (isset($item['meta']) && is_array($item['meta'])) {
                foreach ($item['meta'] as $key => $value) {
                    // Only allow whitelisted meta keys
                    if (!in_array($key, $allowed_meta_keys, true)) {
                        continue;
                    }

                    // Sanitize meta value based on key
                    $sanitized_value = is_string($value)
                        ? sanitize_text_field($value)
                        : $value;

                    update_post_meta($post_id, sanitize_key($key), $sanitized_value);
                }
            }

            $imported_count++;
        }

        \ProgradeOort\Log\Logger::instance()->info(
            "Import completed",
            ['imported' => $imported_count, 'skipped' => $skipped_count],
            'portability'
        );

        return $imported_count > 0;
    }
}
