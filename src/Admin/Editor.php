<?php

namespace ProgradeOort\Admin;

/**
 * Admin Editor with Monaco Code Editor integration
 * Provides PHP code editing with WordPress autocomplete
 */
class Editor
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
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('add_meta_boxes', [$this, 'add_code_editor_metabox']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_head', [$this, 'add_help_tabs']);
    }

    public function register_settings(): void
    {
        register_setting('prograde_oort_settings', 'prograde_oort_allow_eval');
    }

    public function register_menu()
    {
        add_menu_page(
            __('Prograde Oort', 'prograde-oort'),
            __('Oort', 'prograde-oort'),
            'manage_options',
            'prograde-oort',
            [$this, 'render_dashboard'],
            'dashicons-admin-generic'
        );
    }

    public function enqueue_assets($hook)
    {
        // Define common asset paths
        $dist_url = PROGRADE_OORT_URL . 'assets/dist/';
        $dist_path = plugin_dir_path(dirname(__DIR__)) . 'assets/dist/'; // Assumes src/Admin/../.. -> root

        // 1. Dashboard Page
        if (strpos($hook, 'page_prograde-oort') !== false) {
            $asset_file = $dist_path . 'oort-dashboard.asset.php';
            $deps = file_exists($asset_file) ? require($asset_file) : ['dependencies' => ['wp-element', 'wp-components', 'wp-i18n', 'wp-api-fetch'], 'version' => '1.0.0'];
            
            wp_enqueue_script(
                'oort-dashboard',
                $dist_url . 'oort-dashboard.js',
                $deps['dependencies'],
                $deps['version'],
                true
            );

            // Enqueue standard WP styles for components
            wp_enqueue_style('wp-components');

            wp_localize_script('oort-dashboard', 'oortConfig', [
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
                'adminUrl' => admin_url(),
            ]);
        }

        // 2. Editor Page (Custom Logic)
        if (get_post_type() === 'oort_endpoint') {
            $asset_file = $dist_path . 'oort-editor.asset.php';
            $deps = file_exists($asset_file) ? require($asset_file) : ['dependencies' => ['wp-element', 'react'], 'version' => '1.0.0'];

            // Ensure monaco-editor deps are handled or bundled. 
            // Since we use wp-scripts, dependencies in package.json like @monaco-editor/react are bundled.
            // External dependencies like 'react' are excluded by default if using wp-scripts.

            wp_enqueue_script(
                'oort-monaco-editor',
                $dist_url . 'oort-editor.js',
                $deps['dependencies'],
                $deps['version'],
                true
            );

            wp_enqueue_style(
                'oort-editor-styles',
                PROGRADE_OORT_URL . 'assets/css/editor.css',
                ['wp-components'], // Add wp-components style if we start using them there
                '1.2.0'
            );

            wp_localize_script('oort-monaco-editor', 'oortEditorConfig', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('oort_editor'),
                'features' => [
                    'actionScheduler' => function_exists('as_enqueue_async_action'),
                    'guzzle' => class_exists('GuzzleHttp\\Client'),
                    'monolog' => class_exists('Monolog\\Logger')
                ],
                // Autocomplete data... (omitted for brevity, keep existing if possible or move to API)
            ]);
        }
    }

    public function add_code_editor_metabox()
    {
        add_meta_box(
            'oort_code_editor',
            __('Custom Logic Editor', 'prograde-oort'),
            [$this, 'render_code_editor'],
            'oort_endpoint',
            'normal',
            'high'
        );
    }

    public function render_code_editor($post)
    {
        $code = get_post_meta($post->ID, '_oort_logic', true);
        if (empty($code)) {
            $code = "<?php\n// Available: \$params (webhook data), \$data (contextual data)\n// Action Scheduler: as_enqueue_async_action('hook', \$args)\n// HTTP Client: use GuzzleHttp\\Client;\n// Logger: \\ProgradeOort\\Log\\Logger::instance()->info(\$message)\n\nreturn ['status' => 'success'];\n";
        }
?>
        <div class="oort-code-editor-wrapper">
            <!-- React mounts here -->
            <div id="oort-react-editor-root"></div>

            <!-- Hidden field for WordPress form submission -->
            <textarea id="oort_logic_code" name="_oort_logic" style="display:none;"><?php echo esc_textarea($code); ?></textarea>

            <div class="oort-editor-footer">
                <p class="description">
                    <strong><?php _e('💡 Quick Hints:', 'prograde-oort'); ?></strong><br />
                    <?php _e('• Access webhook data via', 'prograde-oort'); ?> <code>$params</code><br />
                    <?php _e('• Schedule background tasks:', 'prograde-oort'); ?> <code>as_enqueue_async_action('my_hook', $args)</code><br />
                    <?php _e('• Make HTTP requests:', 'prograde-oort'); ?> <code>$client = new GuzzleHttp\Client()</code><br />
                    <?php _e('• Log events:', 'prograde-oort'); ?> <code>\ProgradeOort\Log\Logger::instance()->info($message)</code>
                </p>
            </div>
        </div>
    <?php
    }

    public function render_dashboard()
    {
    ?>
        <div class="wrap" id="oort-dashboard-root">
             <!-- React app loads here -->
             <p><?php _e('Loading Prograde Oort Dashboard...', 'prograde-oort'); ?></p>
        </div>
    <?php
    }

    /**
     * Add contextual help tabs to Oort-related screens.
     */
    public function add_help_tabs(): void
    {
        $screen = get_current_screen();
        if (!$screen) return;

        // Only add to our specific pages
        if (strpos((string)$screen->id, 'prograde-oort') === false && $screen->post_type !== 'oort_endpoint') {
            return;
        }

        $screen->add_help_tab([
            'id'      => 'oort_overview',
            'title'   => __('Overview', 'prograde-oort'),
            'content' => '<p>' . __('Welcome to Prograde Oort. This plugin allows you to create high-performance webhook endpoints and automation flows using a safe Expression Language or legacy PHP.', 'prograde-oort') . '</p>',
        ]);

        $screen->add_help_tab([
            'id'      => 'oort_syntax',
            'title'   => __('Syntax Guide', 'prograde-oort'),
            'content' => '<h4>' . __('Available Variables', 'prograde-oort') . '</h4>' .
                '<ul>' .
                '<li><code>$params</code>: ' . __('The raw payload from the incoming request.', 'prograde-oort') . '</li>' .
                '<li><code>$data</code>: ' . __('Contextual data gathered during processing.', 'prograde-oort') . '</li>' .
                '</ul>' .
                '<h4>' . __('Functions', 'prograde-oort') . '</h4>' .
                '<ul>' .
                '<li><code>pluck(array, key)</code>: ' . __('Extract values for a key.', 'prograde-oort') . '</li>' .
                '<li><code>get_meta(id, key)</code>: ' . __('Get WP metadata.', 'prograde-oort') . '</li>' .
                '<li><code>log(message)</code>: ' . __('Log to Oort system logs.', 'prograde-oort') . '</li>' .
                '</ul>',
        ]);

        $screen->add_help_tab([
            'id'      => 'oort_advanced',
            'title'   => __('Advanced Logic', 'prograde-oort'),
            'content' => '<p>' . __('For complex logic, you can enable "Legacy PHP execution" in System Settings. Note that this is less secure than the default Expression Language.', 'prograde-oort') . '</p>' .
                '<p>' . __('Action Scheduler integration is supported for background batch processing of large datasets.', 'prograde-oort') . '</p>',
        ]);

        $screen->set_help_sidebar(
            '<p><strong>' . __('More Information', 'prograde-oort') . '</strong></p>' .
                '<p><a href="https://google.com" target="_blank">' . __('Documentation', 'prograde-oort') . '</a></p>' .
                '<p><a href="https://google.com" target="_blank">' . __('Support Forum', 'prograde-oort') . '</a></p>'
        );
    }
}
