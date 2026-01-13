<?php

namespace ProgradeOort\Admin;

class LogViewer
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
    }

    public function register_menu()
    {
        add_submenu_page(
            'prograde-oort',
            'Log Viewer',
            'Log Viewer',
            'manage_options',
            'prograde-oort-logs',
            [$this, 'render_page']
        );
    }

    public function render_page()
    {
        $channel = $_GET['channel'] ?? 'webhooks';
        $logs = \ProgradeOort\Log\Logger::instance()->get_logs($channel);
?>
        <div class="wrap">
            <h1>Prograde Oort Log Viewer</h1>
            <p>
                <a href="?page=prograde-oort-logs&channel=webhooks" class="button <?php echo $channel === 'webhooks' ? 'button-primary' : ''; ?>">Webhooks</a>
                <a href="?page=prograde-oort-logs&channel=execution" class="button <?php echo $channel === 'execution' ? 'button-primary' : ''; ?>">Execution</a>
                <a href="?page=prograde-oort-logs&channel=ingestion" class="button <?php echo $channel === 'ingestion' ? 'button-primary' : ''; ?>">Ingestion</a>
                <a href="?page=prograde-oort-logs&channel=security" class="button <?php echo $channel === 'security' ? 'button-primary' : ''; ?>">Security</a>
            </p>
            <textarea readonly style="width: 100%; height: 600px; font-family: monospace; background: #272822; color: #f8f8f2; padding: 10px;"><?php echo esc_textarea($logs); ?></textarea>
        </div>
<?php
    }
}
