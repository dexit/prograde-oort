<?php

namespace ProgradeOort\Core;

class PostTypes
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
        add_action('init', [$this, 'register_endpoint_cpt']);
    }

    public function register_endpoint_cpt()
    {
        $labels = [
            'name'               => _x('Oort Endpoints', 'Post Type General Name', 'prograde-oort'),
            'singular_name'      => _x('Oort Endpoint', 'Post Type Singular Name', 'prograde-oort'),
            'menu_name'          => __('Oort Endpoints', 'prograde-oort'),
            'name_admin_bar'     => __('Oort Endpoint', 'prograde-oort'),
            'add_new'            => __('Add New', 'prograde-oort'),
            'add_new_item'       => __('Add New Endpoint', 'prograde-oort'),
            'new_item'           => __('New Endpoint', 'prograde-oort'),
            'edit_item'          => __('Edit Endpoint', 'prograde-oort'),
            'view_item'          => __('View Endpoint', 'prograde-oort'),
            'all_items'          => __('All Endpoints', 'prograde-oort'),
            'search_items'       => __('Search Endpoints', 'prograde-oort'),
            'not_found'          => __('No endpoints found.', 'prograde-oort'),
            'not_found_in_trash' => __('No endpoints found in Trash.', 'prograde-oort'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'prograde-oort',
            'query_var'          => true,
            'rewrite'            => ['slug' => 'oort_endpoint'],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title', 'revisions', 'custom-fields'],
            'show_in_rest'       => false, // We handle our own REST registration
        ];

        register_post_type('oort_endpoint', $args);
    }
}
