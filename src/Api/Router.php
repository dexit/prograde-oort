<?php

namespace ProgradeOort\Api;

class Router
{
    private static $instance = null;
    private $paths = [];

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('parse_request', [$this, 'dispatch_custom_paths']);
    }

    public function register_rest_routes()
    {
        $endpoints = get_posts([
            'post_type'      => 'oort_endpoint',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'   => 'oort_route_type',
                    'value' => 'rest',
                ],
            ],
        ]);

        foreach ($endpoints as $endpoint) {
            $path = get_field('oort_route_path', $endpoint->ID);
            if (! $path) continue;

            register_rest_route('prograde-oort/v1', $path, [
                'methods'             => 'POST',
                'callback'            => function ($request) use ($endpoint) {
                    return $this->handle_dynamic_rest($request, $endpoint);
                },
                'permission_callback' => '__return_true',
            ]);
        }
    }

    public function handle_dynamic_rest($request, $endpoint)
    {
        // Check authentication and return error if it fails
        $auth_result = $this->check_auth($request);
        if (is_wp_error($auth_result)) {
            return $auth_result;
        }

        $params = $request->get_json_params();
        $logic = get_field('oort_logic', $endpoint->ID);

        \ProgradeOort\Log\Logger::instance()->info("Dynamic REST call: " . $endpoint->post_title, $params, 'webhooks');

        return \ProgradeOort\Automation\Engine::instance()->run_flow("endpoint_{$endpoint->ID}", $params, $logic);
    }

    public function register_path($path, $callback)
    {
        $this->paths[trim($path, '/')] = $callback;
    }

    public function dispatch_custom_paths($wp)
    {
        $request_path = trim($wp->request, '/');
        if (isset($this->paths[$request_path])) {
            call_user_func($this->paths[$request_path]);
            exit;
        }
    }

    private function check_auth($request)
    {
        $api_key = $request->get_header('X-Prograde-Key');
        $stored_key = get_option('prograde_oort_api_key');

        // Generate secure key on first access
        if (empty($stored_key)) {
            $stored_key = $this->generate_secure_key();
            update_option('prograde_oort_api_key', $stored_key);

            // Log key generation for admin awareness
            \ProgradeOort\Log\Logger::instance()->warning(
                'New API key generated. Please update your webhook clients.',
                ['key_preview' => substr($stored_key, 0, 8) . '...'],
                'security'
            );
        }

        // Constant-time comparison to prevent timing attacks
        if (!hash_equals($stored_key, $api_key ?? '')) {
            \ProgradeOort\Log\Logger::instance()->error(
                'Unauthorized API access attempt blocked',
                [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown'
                ],
                'security'
            );

            // Return proper REST error response
            return new \WP_Error(
                'rest_forbidden',
                __('Invalid API key', 'prograde-oort'),
                ['status' => 401]
            );
        }

        return true;
    }

    /**
     * Generate cryptographically secure API key
     */
    private function generate_secure_key()
    {
        if (function_exists('wp_generate_password')) {
            return wp_generate_password(64, true, true);
        }

        // Fallback for non-WP environments
        return bin2hex(random_bytes(32));
    }
}
