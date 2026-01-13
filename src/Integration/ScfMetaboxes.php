<?php

namespace ProgradeOort\Integration;

class ScfMetaboxes
{
    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('acf/init', [$this, 'register_fields']);

        // Check if ACF is installed
        if (!$this->is_acf_available()) {
            add_action('admin_notices', [$this, 'acf_missing_notice']);
        }
    }

    /**
     * Check if ACF or compatible plugin is available
     */
    private function is_acf_available()
    {
        return function_exists('acf_add_local_field_group') ||
            function_exists('register_field_group'); // SCF compatibility
    }

    /**
     * Display admin notice if ACF is missing
     */
    public function acf_missing_notice()
    {
?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong><?php _e('Prograde Oort:', 'prograde-oort'); ?></strong>
                <?php _e('Advanced Custom Fields (ACF) or Secure Custom Fields is required for endpoint configuration. Please install and activate it.', 'prograde-oort'); ?>
            </p>
            <p>
                <a href="<?php echo admin_url('plugin-install.php?s=advanced+custom+fields&tab=search&type=term'); ?>" class="button button-primary">
                    <?php _e('Install ACF Now', 'prograde-oort'); ?>
                </a>
            </p>
        </div>
<?php
    }

    public function register_fields()
    {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key'    => 'group_oort_endpoint_settings',
            'title'  => 'Endpoint Settings',
            'fields' => [
                [
                    'key'     => 'field_oort_route_type',
                    'label'   => 'Route Type',
                    'name'    => 'oort_route_type',
                    'type'    => 'select',
                    'choices' => [
                        'rest' => 'REST API Endpoint',
                        'path' => 'Custom Path Dispatcher',
                    ],
                    'default_value' => 'rest',
                ],
                [
                    'key'   => 'field_oort_route_path',
                    'label' => 'Route Path',
                    'name'  => 'oort_route_path',
                    'type'  => 'text',
                    'instructions' => 'e.g., /webhook/my-flow or my-path',
                    'required' => 1,
                ],
                [
                    'key'     => 'field_oort_trigger',
                    'label'   => 'Trigger Event',
                    'name'    => 'oort_trigger',
                    'type'    => 'select',
                    'choices' => [
                        'webhook' => 'Incoming Webhook',
                        'ingestion' => 'Feed Ingestion Runner',
                        'event' => 'Internal Event (Post Save/Login)',
                        'manual' => 'Manual / API Call only',
                    ],
                    'default_value' => 'webhook',
                ],
                [
                    'key'   => 'field_oort_logic',
                    'label' => 'Automation Logic (PHP)',
                    'name'  => 'oort_logic',
                    'type'  => 'textarea',
                    'rows'  => 20,
                    'instructions' => 'Enter PHP code without <?php tag. Variable $data contains payload.',
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'oort_endpoint',
                    ],
                ],
            ],
        ]);
    }
}
