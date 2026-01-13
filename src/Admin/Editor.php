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
    }

    public function register_menu()
    {
        add_menu_page(
            'Prograde Oort',
            'Oort',
            'manage_options',
            'prograde-oort',
            [$this, 'render_dashboard'],
            'dashicons-admin-generic'
        );
    }

    public function enqueue_assets($hook)
    {
        // Only load on Oort pages
        if (strpos($hook, 'prograde-oort') === false && get_post_type() !== 'oort_endpoint') {
            return;
        }

        // Enqueue Monaco Editor bundle
        wp_enqueue_script(
            'oort-monaco-editor',
            PROGRADE_OORT_URL . 'assets/dist/oort-editor.js',
            ['react', 'react-dom'],
            '1.1.0',
            true
        );

        // Enqueue React (WordPress includes it by default in Gutenberg)
        wp_enqueue_script('react');
        wp_enqueue_script('react-dom');

        // Editor styles
        wp_enqueue_style(
            'oort-editor-styles',
            PROGRADE_OORT_URL . 'assets/css/editor.css',
            [],
            '1.1.0'
        );

        // Pass configuration to JavaScript
        wp_localize_script('oort-monaco-editor', 'oortEditorConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('oort_editor'),
            'features' => [
                'actionScheduler' => function_exists('as_enqueue_async_action'),
                'guzzle' => class_exists('GuzzleHttp\\Client'),
                'monolog' => class_exists('Monolog\\Logger')
            ]
        ]);
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
        $code = get_post_meta($post->ID, 'oort_logic', true);
        if (empty($code)) {
            $code = "<?php\n// Available: \$params (webhook data), \$data (contextual data)\n// Action Scheduler: as_enqueue_async_action('hook', \$args)\n// HTTP Client: use GuzzleHttp\\Client;\n// Logger: \\ProgradeOort\\Log\\Logger::instance()->info(\$message)\n\nreturn ['status' => 'success'];\n";
        }
?>
        <div class="oort-code-editor-wrapper">
            <!-- React mounts here -->
            <div id="oort-react-editor-root"></div>

            <!-- Hidden field for WordPress form submission -->
            <textarea id="oort_logic_code" name="oort_logic" style="display:none;"><?php echo esc_textarea($code); ?></textarea>

            <div class="oort-editor-footer">
                <p class="description">
                    <strong>💡 Quick Hints:</strong><br />
                    • Access webhook data via <code>$params</code><br />
                    • Schedule background tasks: <code>as_enqueue_async_action('my_hook', $args)</code><br />
                    • Make HTTP requests: <code>$client = new GuzzleHttp\Client()</code><br />
                    • Log events: <code>\ProgradeOort\Log\Logger::instance()->info($message)</code>
                </p>
            </div>
        </div>
    <?php
    }

    public function render_dashboard()
    {
    ?>
        <div class="wrap">
            <h1><?php _e('Prograde Oort Dashboard', 'prograde-oort'); ?></h1>
            <div class="oort-dashboard-grid">
                <div class="oort-card">
                    <h2>📋 Endpoints</h2>
                    <p>Manage your API routes and automation workflows.</p>
                    <a href="<?php echo admin_url('edit.php?post_type=oort_endpoint'); ?>" class="button button-primary">
                        View Endpoints
                    </a>
                </div>
                <div class="oort-card">
                    <h2>📊 Logs</h2>
                    <p>Monitor webhook activity and execution logs.</p>
                    <a href="<?php echo admin_url('admin.php?page=oort-logs'); ?>" class="button">
                        View Logs
                    </a>
                </div>
                <div class="oort-card">
                    <h2>🔄 Import/Export</h2>
                    <p>Migrate configurations across environments.</p>
                    <a href="<?php echo admin_url('admin.php?page=oort-portability'); ?>" class="button">
                        Manage
                    </a>
                </div>
                <div class="oort-card">
                    <h2>🔐 API Settings</h2>
                    <p>Your API Key: <code><?php echo substr(get_option('prograde_oort_api_key', 'Not set'), 0, 16); ?>...</code></p>
                    <p class="description">Use this key in the <code>X-Prograde-Key</code> header.</p>
                </div>
            </div>
        </div>
        <style>
            .oort-dashboard-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }

            .oort-card {
                background: white;
                padding: 20px;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            }

            .oort-card h2 {
                margin-top: 0;
            }

            .oort-card code {
                background: #f0f0f1;
                padding: 2px 6px;
                border-radius: 3px;
            }
        </style>
<?php
    }
}
