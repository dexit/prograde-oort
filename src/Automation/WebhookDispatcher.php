<?php

namespace ProgradeOort\Automation;

class WebhookDispatcher
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
     * Send an outgoing webhook.
     *
     * @param string $url     Target URL.
     * @param array  $payload Data to send.
     * @param array  $headers Optional custom headers.
     * @return array          Response status and body.
     */
    public function dispatch($url, $payload, $headers = [])
    {
        \ProgradeOort\Log\Logger::instance()->info("Dispatching outgoing webhook to: $url", $payload, 'webhooks');

        $args = [
            'body'        => json_encode($payload),
            'headers'     => array_merge(['Content-Type' => 'application/json'], $headers),
            'timeout'     => 30,
            'data_format' => 'body',
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            \ProgradeOort\Log\Logger::instance()->error("Outgoing webhook failed: $error_message", ['url' => $url], 'webhooks');
            return ['status' => 'error', 'message' => $error_message];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        \ProgradeOort\Log\Logger::instance()->info("Outgoing webhook response ($status_code)", ['body' => $body], 'webhooks');

        return [
            'status'      => 'success',
            'status_code' => $status_code,
            'body'        => json_decode($body, true) ?: $body,
        ];
    }
}
