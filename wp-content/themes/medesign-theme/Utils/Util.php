<?php

class Util {

    public static function services(){
        $visibleServices = [];
        foreach (get_field('servicos', 'options') as $service) {
            if($service['visivel_no_site']){
                $visibleServices[] = $service;
            }
        }
        return $visibleServices;
    }

    public static function clients(){
        $visibleClients = [];
        foreach (get_field('clientes', 'options') as $service) {
            if($service['visivel_no_site']){
                $visibleClients[] = $service;
            }
        }
        return $visibleClients;
    }
    
    // public static function get_by_acf_fields($field_type, $limit_posts = 6){
    //     $posts = Geral::getPostByFilter('post', $field_type, true, '=', $limit_posts);

    //     if($field_type === "incluir_no_banner"){
    //         foreach($posts as $key => $post){
    //             if($key === 0){
    //                 $posts[$key]['active'] = true;
    //             }else{
    //                 $posts[$key]['active'] = false;
    //             }
    //         }
    //     }
        
    //     return $posts;
    // }
    
    public static function post_response_format($id_post, $title, $slug, $category, $description = '', $highlight, $author = '', $views, $comments, $time_read, $created_at, $img)
    {
        return [
             'id' => $id_post,
             'slug' => $slug,
             'title' => [ 'rendered' => $title ],
             'content' => [ 'rendered' => $description ],
             'categories' => $category ?? null,
             'posted_at' => $created_at,
             'custom_author' => $author,
             'images_size_custom' => $img ?? null,
             'views' => $views,
             'comments' => $comments,
             'time_read' => $time_read,
             'highlight' => $highlight,
         ];
    }

    // public static function post($posts){
        
    //     while($posts->have_posts()):
    //         $posts->the_post();

    //         $ID = get_the_ID();
    //         $post = get_post( $ID );

    //         $post_categories = humanizePostsCategories( $ID );

    //         $author = author($post->post_author);

    //         $post_highlight = get_the_excerpt();
    //         $post_time_to_read = get_field('tempo_de_leitura', $ID, 'options');

    //         $post_banner_image = images_custom( $ID );

    //         $created_at = human_readable_post_created_at( $ID );
            
    //         $res[] = self::post_response_format(
    //             $ID,
    //             get_the_title(),
    //             $post->post_name,
    //             $post_categories,
    //             // get_the_content( '', false, $ID ),
    //             '',
    //             $post_highlight,
    //             $author,
    //             get_views_number_func($ID),
    //             get_comments_number_func($ID),
    //             $post_time_to_read,
    //             $created_at,
    //             $post_banner_image
    //         );

    //         $dados = $res;

    //     endwhile;

    //     return $dados;

    // }
    
}