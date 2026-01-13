<?php

namespace ProgradeOort\Integration;

class ScfHelper
{
    /**
     * Safe wrapper for SCF's get_field or standard WP get_post_meta.
     *
     * @param string $key     The field key.
     * @param int    $post_id The post ID.
     * @return mixed          False on failure/empty, or the value.
     */
    public static function get_field($key, $post_id)
    {
        // Check if Secure Custom Fields (or ACF) function exists
        if (function_exists('get_field')) {
            return get_field($key, $post_id);
        }

        // Fallback to standard core metadata
        return get_post_meta($post_id, $key, true);
    }

    /**
     * Checks if SCF/ACF is active.
     *
     * @return bool
     */
    public static function is_active()
    {
        return function_exists('get_field');
    }
}
