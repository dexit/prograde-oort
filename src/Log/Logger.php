<?php

namespace ProgradeOort\Log;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private static $instance = null;
    private $loggers = [];
    private $log_dir;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->log_dir = (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : __DIR__ . '/../../wp-content') . '/uploads/prograde-oort-logs/';
        if (!file_exists($this->log_dir)) {
            mkdir($this->log_dir, 0777, true);
        }
    }

    private function get_logger($channel)
    {
        if (!isset($this->loggers[$channel])) {
            $logger = new MonologLogger($channel);

            $file_path = $this->log_dir . $channel . '.log';
            $handler = new RotatingFileHandler($file_path, 7, MonologLogger::DEBUG);

            $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
            $formatter = new LineFormatter($output, "Y-m-d H:i:s");
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);
            $this->loggers[$channel] = $logger;
        }
        return $this->loggers[$channel];
    }

    public function info($message, $context = [], $channel = 'global')
    {
        $this->get_logger($channel)->info($message, $this->enrich_context($context));
    }

    public function warning($message, $context = [], $channel = 'global')
    {
        $this->get_logger($channel)->warning($message, $this->enrich_context($context));
    }

    public function error($message, $context = [], $channel = 'global')
    {
        $this->get_logger($channel)->error($message, $this->enrich_context($context));
    }

    private function enrich_context($context)
    {
        $context['request_id'] = uniqid('oort_');
        if (function_exists('get_current_user_id')) {
            $context['user_id'] = get_current_user_id();
        }
        return $context;
    }

    public function get_logs($channel = 'global')
    {
        $file_path = $this->log_dir . $channel . '.log';
        if (file_exists($file_path)) {
            return file_get_contents($file_path);
        }
        return "No logs found for channel: {$channel}";
    }
}
