<?php

namespace TNP\API\V2;

use WP_Error;
use WP_REST_Controller;

class REST_Controller extends WP_REST_Controller {

    const ROOT_NAMESPACE = 'newsletter';

    public function __construct() {
        $this->namespace = self::ROOT_NAMESPACE . '/v2';
    }

    /**
     * Called by WP when a route is matched, if the callback has been added to the route.
     * 
     * @param \WP_REST_Request $request
     * @return WP_Error|bool
     */
    public function permissions_check($request) {
        return \NewsletterApi::$authenticated;
    }
}
