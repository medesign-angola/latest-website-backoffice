<?php

add_action('init', 'create_custom_posts');

function create_custom_posts(){

    register_post_type('projectos', [
        'label' => 'Projectos',
        'description' => 'Gestão de Projectos',
        'public' => true,
        'show_ui' => true,
        'capabiliy_type' => 'post',
        'rewrite' => [ 'slug' => 'projectos', 'with_front' => true ],
        'query_var' => true,
        'publicly_queriable' => true,
        'show_in_rest' => true,
        'rest_base' => 'projectos',
        'menu_icon' => 'dashicons-portfolio',
        'supports' => [ 'custom_fields', 'title' ],
        'position' => 0
    ]);

}