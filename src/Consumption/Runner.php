<?php

namespace ProgradeOort\Consumption;

class Runner
{
    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Run an ingestion process for a specific feed.
     *
     * @param string $feed_url The source URL.
     * @param array  $config   Ingestion configuration (mapping, types).
     * @return array           Stats of the ingestion.
     */
    public function run($feed_url, $config = [])
    {
        \ProgradeOort\Log\Logger::instance()->info("Starting ingestion from: $feed_url", $config, 'ingestion');

        $response = wp_remote_get($feed_url);
        if (is_wp_error($response)) {
            return ['status' => 'error', 'message' => $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (! $data) {
            return ['status' => 'error', 'message' => 'Invalid data format received.'];
        }

        $pipeline = new Pipeline($config);
        $stats = $pipeline->process($data);

        \ProgradeOort\Log\Logger::instance()->info("Ingestion completed", $stats, 'ingestion');

        return ['status' => 'success', 'stats' => $stats];
    }
}
