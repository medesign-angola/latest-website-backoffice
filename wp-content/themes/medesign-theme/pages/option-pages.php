<?php

if( function_exists('acf_add_options_page') ){
    acf_add_options_page(array(
        'page_title' => 'Clientes',
        'menu_title' => 'Clientes',
        'menu_slug' => 'clientes',
        'capability' => 'edit_posts',
        'redirect' => false,
        'icon_url' => 'dashicons-groups',
        'position' => 6,
    ));
    acf_add_options_page(array(
        'page_title' => 'Serviços',
        'menu_title' => 'Serviços',
        'menu_slug' => 'servicos',
        'capability' => 'edit_posts',
        'redirect' => false,
        'icon_url' => 'dashicons-admin-page',
        'position' => 6,
    ));
}