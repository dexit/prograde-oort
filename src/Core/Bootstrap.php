<?php

namespace ProgradeOort\Core;

class Bootstrap
{
    private static $instance = null;
    private $components = [];

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init_components();
    }

    private function init_components()
    {
        $this->components['log'] = \ProgradeOort\Log\Logger::instance();
        $this->components['router'] = \ProgradeOort\Api\Router::instance();
        $this->components['engine'] = \ProgradeOort\Automation\Engine::instance();
        $this->components['events'] = \ProgradeOort\Automation\Events::instance();
        $this->components['dispatcher'] = \ProgradeOort\Automation\WebhookDispatcher::instance();
        $this->components['runner'] = \ProgradeOort\Consumption\Runner::instance();
        $this->components['admin'] = \ProgradeOort\Admin\Editor::instance();
        $this->components['log_viewer'] = \ProgradeOort\Admin\LogViewer::instance();
        $this->components['portability'] = \ProgradeOort\Admin\PortabilityPage::instance();
        $this->components['post_types'] = \ProgradeOort\Core\PostTypes::instance();
        $this->components['metaboxes'] = \ProgradeOort\Integration\ScfMetaboxes::instance();

        // Register Examples on initialization
        add_action('init', function () {
            \ProgradeOort\Integration\Scenarios::register_examples();
        });
    }

    public function get_component($name)
    {
        return isset($this->components[$name]) ? $this->components[$name] : null;
    }
}
