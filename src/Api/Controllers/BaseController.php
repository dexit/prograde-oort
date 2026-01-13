<?php

namespace ProgradeOort\Api\Controllers;

abstract class BaseController
{
    /**
     * Send a successful JSON response.
     *
     * @param mixed $data Data to return.
     * @param int   $status HTTP status code.
     * @return \WP_REST_Response
     */
    protected function success($data = null, $status = 200)
    {
        return new \WP_REST_Response([
            'status'  => 'success',
            'data'    => $data,
            'message' => 'Operation completed successfully.',
        ], $status);
    }

    /**
     * Send an error JSON response.
     *
     * @param string $message Error message.
     * @param int    $status  HTTP status code.
     * @param array  $data    Optional extra error data.
     * @return \WP_REST_Response
     */
    protected function error($message, $status = 400, $data = [])
    {
        return new \WP_REST_Response([
            'status'  => 'error',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Log an action through the central logger.
     *
     * @param string $message
     * @param array  $context
     * @param string $channel
     */
    protected function log($message, $context = [], $channel = 'global')
    {
        \ProgradeOort\Log\Logger::instance()->info($message, $context, $channel);
    }
}
