<?php

namespace ProgradeOort\Integration;

/**
 * Pre-defined example configurations to showcase Guzzle, Action Scheduler, etc.
 */
class Scenarios
{
    public static function register_examples()
    {
        // Use atomic add_option to prevent race conditions
        // Returns false if option already exists
        if (!add_option('prograde_oort_examples_installed', time(), '', 'no')) {
            // Examples already installed
            return;
        }

        // 1. Example: Inbound Webhook Logging (Simple)
        self::create_endpoint(
            'Example: Generic Webhook',
            'webhook',
            'api/v1/log-it',
            '<?php
\ProgradeOort\Log\Logger::instance()->info("Received generic webhook", $params, "webhooks");
return ["status" => "logged"];'
        );

        // 2. Example: Dynamic Dispatch (CPT -> 3rd Party via Guzzle)
        // Uses Guzzle 7.x
        self::create_endpoint(
            'Example: Dynamic Dispatcher',
            'webhook',
            'api/v1/dispatch',
            '<?php
use GuzzleHttp\Client;

$client = new Client();
$response = $client->post("https://httpbin.org/post", [
    "json" => [
        "source" => "Prograde Oort",
        "payload" => $params
    ]
]);

$body = json_decode($response->getBody(), true);
\ProgradeOort\Log\Logger::instance()->info("Dispatched data to 3rd party", ["response" => $body], "execution");
return $body;'
        );

        // 3. Example: Ingestion -> Logging (Action Scheduler)
        self::create_endpoint(
            'Example: Ingestion Trigger',
            'event',
            'wp_login',
            '<?php
// Schedule an async task using Action Scheduler
if (function_exists("as_enqueue_async_action")) {
    as_enqueue_async_action("oort_ingestion_task", ["user_id" => $data["user_id"]]);
    \ProgradeOort\Log\Logger::instance()->info("Scheduled ingestion task for user login", $data, "ingestion");
}
return ["status" => "scheduled"];'
        );

        update_option('prograde_oort_examples_installed', 1);
    }

    private static function create_endpoint($title, $type, $path, $code)
    {
        $post_id = wp_insert_post([
            'post_title' => $title,
            'post_type'  => 'oort_endpoint',
            'post_status' => 'publish'
        ]);

        if ($post_id) {
            update_post_meta($post_id, 'route_type', $type);
            update_post_meta($post_id, 'route_path', $path);
            update_post_meta($post_id, 'logic_code', $code);
        }
    }
}
