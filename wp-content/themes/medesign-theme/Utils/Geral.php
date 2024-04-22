<?php

class Geral{

    public static function getQueryMeta($key, $value, $compare)
    {
        return [
            [
                'key' => $key,
                'value' => $value,
                'compare' => $compare
            ]
        ];
    }

    public static function getQuery( $postType, $queryMeta = [], $postsPerPage = -1, $offset = 0 )
    {
            return [
                'post_type' => $postType,
                'meta_query' => $queryMeta,
                'posts_per_page' => $postsPerPage,
                'offset' => $offset,
                'orderby' => 'date',
                'order' => 'DESC'
            ];
    }

    public static function registerRoute( $method, $function, $context, $namespace, $endpoint )
    {
        $arg = [
            [
                'methods' => $method,
                'callback' => [ $context, $function ]
            ]
        ];

        register_rest_route($namespace, $endpoint, $arg);
    }

    public static function getPostByFilter( $postType, $key = '', $value, $operator = '', $postPerPage = '' )
    {
        $tmpPostType = $postType;

    	if($key === ''){

    		$query = self::getQuery($postType, [], $postPerPage);
            

    	} else {

	        $queryMeta = self::getQueryMeta($key, $value, $operator);  

	        $query = self::getQuery($postType, $queryMeta, $postPerPage, 0);

    	}


        $data = [];

        $list = new WP_Query( $query );

        if( empty( $list->posts ) ) {

            return self::resposta_404( );

        }

        // return $list->posts;

  		// $res['data']['status'] = 200;
        // $res['data']['res'] = Util::$tmpPostType ( $list );

        $res = Util::$tmpPostType ( $list );

        return $res;
    }

    public static function resposta_404 (){

    	return new WP_REST_Response(
			    array(
			        'status' => 404,
			        'MSG' => 'Sem resultado',
			        'res' => array()
			    )
		);

    }

    public static function resposta_500 (){

    	return new WP_REST_Response(
			    array(
			        'status' => 500,
			        'MSG' => 'Erro ao tentar executar esta acção.',
			        'res' => array()
			    )
		);

    }

    public static function resposta_200 (){

    	return new WP_REST_Response(
			    array(
			        'status' => 200,
			        'MSG' => 'Acção executada com êxito!',
			        // 'res' => array()
			    )
		);

    }

}