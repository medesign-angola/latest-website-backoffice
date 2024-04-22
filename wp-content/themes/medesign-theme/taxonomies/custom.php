<?php

// Registrando taxonomia para Design Gráfico
function register_works_custom_taxonomy() {
    $args = array(
        'hierarchical' => true,
        'label' => 'Áreas de Actuação',
        'rewrite' => array('slug' => 'areas-de-actuacao'),
        'query_var' => true,
        'publicly_queriable' => true,
        'show_in_rest' => true,
        'rest_base' => 'areas-de-actuacao',
    );
    register_taxonomy('area-de-actuacao', 'projectos', $args);
}
add_action('init', 'register_works_custom_taxonomy');