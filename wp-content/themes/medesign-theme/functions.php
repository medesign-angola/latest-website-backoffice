<?php 

add_theme_support( 'post-thumbnails' );

require_once(TEMPLATEPATH . "/cpts/cpts.php");
require_once(TEMPLATEPATH . "/pages/option-pages.php");
require_once(TEMPLATEPATH . "/endpoints/home.php");
require_once(TEMPLATEPATH . "/taxonomies/custom.php");
// require_once(TEMPLATEPATH . "/endpoints/ads.php");

function dd($var = ''){
  echo "<pre>";
  var_dump($var);
  echo "</pre>";
  die();
}

/**
 * { Isaquias }
 * Custom function. Adicionar campos na resposta dos posts na rest api
*/

function register_custom_fields_post_rest_response(){

  register_rest_field( 'post', 'categories', [
    'get_callback' => 'humanizePostsCategories',
    'update_callback' => null,
  ]);

  register_rest_field( 'projectos', 'area_de_actuacao', [
    'get_callback' => 'humanizeProjectsAreas',
    'update_callback' => null,
  ]);

  register_rest_field( 'post', 'posted_at', [
    'get_callback' => 'human_readable_post_created_at',
    'update_callback' => null,
  ]);

  register_rest_field( 'brochuras', 'posted_at', [
    'get_callback' => 'human_readable_brochure_created_at',
    'update_callback' => null,
  ]);

  register_rest_field( 'post', 'custom_author', [
    'get_callback' => 'author',
    'update_callback' => null,
  ]);

  register_rest_field( 'post', 'images_size_custom', [
    'get_callback' => 'images_custom',
    'update_callback' => null,
  ]);

  register_rest_field( 'post', 'comments_count', [
    'get_callback' => 'get_comments_number_func',
    'update_callback' => null,
  ]);

  register_rest_field( 'post', 'views_count', [
    'get_callback' => 'get_views_number_func',
    'update_callback' => null,
  ]);
  
  register_rest_field( 'post', 'highlight', [
    'get_callback' => 'highlight',
    'update_callback' => null,
  ]);

}
add_action('rest_api_init', 'register_custom_fields_post_rest_response');

function human_readable_post_created_at($post_id)
{
  // return get_the_date('d/m/Y', (int) $post_id);
  if(gettype($post_id) == "integer"){
    $date = strtotime( get_the_date('Y-m-d H:i:s', (int) $post_id));
  }else{
    $date = strtotime( get_the_date('Y-m-d H:i:s', (int) $post_id['id']));
  }

  $now = strtotime( wp_date( 'Y-m-d H:i:s' ) );

  return human_time_diff( $date, $now );
}

function human_readable_brochure_created_at($brochure_id)
{
  return get_the_date('d/m/Y', (int) $brochure_id['id']);
}

function author($author_id)
{
  $author_id = (int) $author_id;
  $author_first_name = get_the_author_meta( 'first_name', $author_id );
  $author_last_name = get_the_author_meta( 'last_name', $author_id );
  
  return "$author_first_name $author_last_name";
}

function images_custom($post_id)
{
  if(gettype($post_id) == "integer"){
    return [
      'full_image_size' => get_the_post_thumbnail_url( (int) $post_id, 'full' ),
      'medium_image_size' => get_the_post_thumbnail_url( (int) $post_id, 'medium' ),
      'thumbnail_image_size' => get_the_post_thumbnail_url( (int) $post_id, 'post-thumbnail' ),
    ];
  }else{
    return [
      'full_image_size' => get_the_post_thumbnail_url( (int) $post_id['id'], 'full' ),
      'medium_image_size' => get_the_post_thumbnail_url( (int) $post_id['id'], 'medium' ),
      'thumbnail_image_size' => get_the_post_thumbnail_url( (int) $post_id['id'], 'thumbnail' ),
    ];
  }

}

function highlight($post_id)
{
  return get_the_excerpt( (int) $post_id['id'] );
}

function humanizePostsCategories($post_id)
{
  $categories = [];

  if(gettype($post_id) == "integer"){
    $postCategoriesArrayObject = get_the_category( (int) $post_id );
  }else{
    $postCategoriesArrayObject = get_the_category( (int) $post_id['id'] );
  }

  foreach($postCategoriesArrayObject as $postCategory){
      $categories[] = [
        'id' => $postCategory->cat_ID,
        'name' => $postCategory->name,
        'parent' => $postCategory->parent,
        'slug' => $postCategory->slug,
      ];
  }

  return $categories;
}

function humanizeProjectsAreas($project_id)
{

  $areas = [];

  if(gettype($project_id) == "integer"){
    $projectAreasArrayObject = get_the_terms( (int) $project_id, 'areas-de-actuacao' );
  }else{
    $projectAreasArrayObject = get_the_terms( (int) $project_id['id'], 'area-de-actuacao' );
  }

  // dd($projectAreasArrayObject);
  if($projectAreasArrayObject){
    foreach($projectAreasArrayObject as $projectArea){
        $areas[] = [
          'id' => $projectArea->term_id,
          'name' => $projectArea->name,
          'parent' => $projectArea->parent,
          'slug' => $projectArea->slug,
        ];
    }
  } else {
    $areas = [];
  }

  return $areas;
}

function get_comments_number_func($post_id)
{
  if(gettype($post_id) === "integer"){
    return (int) get_comments_number( (int) $post_id );
  }else{
    return (int) get_comments_number( (int) $post_id['id'] );
  }
}

function get_views_number_func($post_id)
{  
  if(gettype($post_id) === "integer"){
    return (int) pvc_get_post_views( (int) $post_id );
  }else{
    return (int) pvc_get_post_views( (int) $post_id['id'] );
  }
}

/**
 * 
 * 
 * 
 */

function filter_site_upload_size_limit( $size ) {
    // Set the upload size limit to 256 MB for all users.
    $size = 1024 * 262000;
    return $size;
  }
 
  add_filter( 'upload_size_limit', 'filter_site_upload_size_limit', 20 );

  // Cria o menu padrão para a aplicação.
  /*function register_my_menus() {
    register_nav_menus(
      array(
        'menu-principal' => __( 'Menu Principal' ),
      )
    );
  }

  add_action( 'init', 'register_my_menus' );*/

  function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *");
  }
  add_action('init','add_cors_http_header');

  /*PHP Configs - Uploads*/
  @ini_set( 'upload_max_size', '256M' );
  @ini_set( 'post_max_size', '300M');
  @ini_set( 'max_execution_time', '3000' );