<?php

namespace ProgradeOort\Automation;

class Events
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
        add_action('save_post', [$this, 'handle_post_save'], 10, 3);
        add_action('wp_login', [$this, 'handle_user_login'], 10, 2);
    }

    public function handle_post_save($post_id, $post, $update)
    {
        if (wp_is_post_revision($post_id) || $post->post_type === 'oort_endpoint') {
            return;
        }

        $this->trigger_event('post_saved', [
            'post_id'   => $post_id,
            'post_type' => $post->post_type,
            'update'    => $update,
        ]);
    }

    public function handle_user_login($user_login, $user)
    {
        $this->trigger_event('user_logged_in', [
            'user_id'    => $user->ID,
            'user_login' => $user_login,
        ]);
    }

    private function trigger_event($event_name, $context)
    {
        // Find endpoints triggered by events
        $endpoints = get_posts([
            'post_type'      => 'oort_endpoint',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'   => 'oort_trigger',
                    'value' => 'event', // We'll need to add 'event' to the select choices in SCF
                ],
            ],
        ]);

        foreach ($endpoints as $endpoint) {
            // In a more advanced version, we'd check if the endpoint is listening for THIS specific event.
            $logic = get_field('oort_logic', $endpoint->ID);
            Engine::instance()->run_flow("event_{$event_name}_{$endpoint->ID}", $context, $logic);
        }
    }
}
