<?php

// Reencaminhar para uma página no website

$domain_name = 'https://medesign-angola.com';
$post_slug_uri = $_SERVER['REQUEST_URI'];

$post_slug_uri_exploded = explode('/', $post_slug_uri);

// dd($post_slug_uri_exploded);

$domain = ($post_slug_uri_exploded[1] === "") ? $domain_name : $domain_name.'/new/'.$post_slug_uri_exploded[1];

// dd($domain);

header("Location: $domain");
?>