<?php

namespace TNP\API\V2\Subscriptions;

use NewsletterSubscription;
use TNP_Subscription;
use TNP_Subscription_Data;
use TNP\API\V2\Subscribers\Subscribers_Controller;
use WP_REST_Response;
use WP_REST_Server;

class Subscriptions_Controller extends Subscribers_Controller {

    public function __construct() {
        parent::__construct();
        $this->rest_base = 'subscriptions';
    }

    /**
     * Registers the subscriptions routes
     */
    public function register_routes() {

        register_rest_route($this->namespace, '/test', array(
            'methods' => 'GET',
            'callback' => function () {
                return new \stdClass();
            },
            'permission_callback' => array($this, 'permissions_check')
        ));

        register_rest_route($this->namespace, "/$this->rest_base",
                array(
                    // POST /subscriptions
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        //'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
                        'callback' => [$this, 'create_item'],
                        'permission_callback' => '__return_true',
                    )
                )
        );
    }

    public function get_item_schema() {

        $schema = parent::get_item_schema();

        unset($schema['properties']['status']);

        return $schema;
    }

    public function create_item($request) {

        $subscription_module = NewsletterSubscription::instance();
        $language = $request->get_param('language');
        $subscription = $subscription_module->get_default_subscription($language);

        $subscription->if_exists = TNP_Subscription::EXISTING_ERROR;
        $subscription->optin = $request->get_param('optin');

        $r = $this->fill_subscription_data($request, $subscription->data);

        if (is_wp_error($r)) {
            return $r;
        }

        $user = $subscription_module->subscribe2($subscription);

        if (is_wp_error($user)) {
            return $user;
        }

        $response = new WP_REST_Response(new \stdClass());
        $response->set_status(201);

        return $response;
    }

    /**
     * Build a TNP_Subscription_Data from the request, filtering sent data, lists, profiles, ready
     * to be used to start the subscription process.
     * 
     * @param \WP_REST_Request $request
     * @param \TNP_Subscription_Data $data 
     */
    protected function fill_subscription_data($request, $data) {
        $newsletter = \Newsletter::instance();

        $data->email = $newsletter->normalize_email($request->get_param('email'));
        $data->name = $newsletter->normalize_name($request->get_param('first_name'));
        $data->surname = $request->get_param('last_name');
        $data->sex = array_search($request->get_param('gender'), self::SUBSCRIBER_SEX_ENUM);

        $data->country = $request->get_param('country');
        $data->region = $request->get_param('region');
        $data->city = $request->get_param('city');

        // Process all lists passed over as an array (of objects or IDs)
        $lists = $request->get_param('lists');

        $authenticated = \NewsletterApi::instance()->is_authenticated();

        if ($lists) {
            foreach ($lists as $list) {
                if (is_object($list)) {
                    $id = (int) $list['id'];
                    $value = $value ? 1 : 0;
                } else {
                    $id = (int) $list;
                    // If negative we consider it a list opt-out
                    $value = $id > 0 ? 1 : 0;
                    $id = abs($id);
                }

                $nl = $newsletter->get_list($id);

                if (!$nl) {
                    //return new \WP_Error('1', 'Not a public list');
                }

                // Authenticated requests can set the private lists
                if ($authenticated || !$nl->is_private()) {
                    $data->lists[$id] = $value;
                }
            }
        }

        // Manage list_N parameters
        $nls = $newsletter->get_lists();
        foreach ($nls as $nl) {
            if ($request->has_param('list_' . $nl->id)) {
                if ($authenticated || !$nl->is_private()) {
                    $data->lists[$nl->id] = $request->get_param('list_' . $nl->id) ? 1 : 0;
                }
            }
        }

        $extra_fields = $request->get_param('extra_fields');
        if ($extra_fields) {
            foreach ($extra_fields as $field) {
                $np = $newsletter->get_profile($field['id']);
                if ($np && $np->is_private()) {
                    continue;
                }
                $data->profiles[$np->id] = sanitize_text_field($field['value']);
            }
        }
    }

}
