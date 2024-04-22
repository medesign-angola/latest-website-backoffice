<?php

namespace TNP\API\V2;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST API authentication class.
 * 
 * @deprecated
 */
class TNP_REST_Authentication {

    private static $instance = null;

    /**
     * Authentication error.
     *
     * @var WP_Error
     */
    protected $error = null;

    /**
     * Logged in Key Model.
     *
     * @var TNP_REST_Authentication_Key
     */
    protected $key = null;
    public static $is_authenticated_with_key = false;

    /**
     * Current auth method.
     *
     * @var string
     */
    protected $auth_method = '';

    const PERMISSIONS = ['read' => 'Read', 'write' => 'Write', 'read_write' => 'Read & Write'];

    public static function getInstance() {

        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize authentication actions.
     */
    private function __construct() {
        
    }

    public function init_authentication_method() {
        return;
        
        global $wp_filter;
        $logger = \NewsletterApi::$instance->get_logger();
//        if (has_filter('determine_current_user')) {
//            $logger->debug('Authenticate: current state of "determine_current_user" filter:' . PHP_EOL . var_export($wp_filter['determine_current_user'], true));
//        }
        $logger->debug('Authenticate: add "determine_current_user" filter');

        add_filter('determine_current_user', array($this, 'authenticate'), 110);
        add_filter('rest_authentication_errors', array($this, 'check_authentication_error'), 100);
        add_filter('rest_pre_dispatch', array($this, 'check_user_permissions'), 10, 3);
        add_filter('rest_post_dispatch', array($this, 'send_unauthorized_headers'), 50);
        add_action('shutdown', array($this, 'log_shutdown_filters'), 999);
    }

    public function log_shutdown_filters() {
        global $wp_filter;
        $logger = \NewsletterApi::$instance->get_logger();
        $logger->debug('Authenticate: before shutdown');
        if (has_filter('determine_current_user')) {
            $logger->debug('Authenticate: current state of "determine_current_user" filter:' . PHP_EOL . var_export($wp_filter['determine_current_user'], true));
        }
    }

    /**
     * Authenticate user.
     *
     * @param int|false $user_id User ID if one has been determined, false otherwise.
     *
     * @return int|false
     */
    public function authenticate($user_id) {
        //if (!defined('REST_REQUEST')) return $user_id;

        $logger = \NewsletterApi::$instance->get_logger();

        $logger->debug('Authenticate: try to authenticate with TNP authentication system');

        // Not for us, return the value without changing it
        if (!$this->is_request_to_newsletter_rest_api()) {
            $logger->debug('Authenticate: not a request for our APIs');
            return $user_id;
        }

        if (is_ssl() || ( defined('NEWSLETTER_REST_ALLOW_NON_HTTPS_REQUEST') && NEWSLETTER_REST_ALLOW_NON_HTTPS_REQUEST )) {
            return $this->perform_basic_authentication();
        } else {
            $logger->debug('Authenticate: not SSL - stop');
        }
        return false;
    }

    /**
     * Check for authentication error.
     *
     * @param WP_Error|null|bool $error Error data.
     *
     * @return WP_Error|null|bool
     */
    public function check_authentication_error($error) {

        if (self::$is_authenticated_with_key) {
            return null;
        }

        if ($this->error) {
            return $this->error;
        }

        return $error;
    }

    /**
     * Set authentication error.
     *
     * @param WP_Error $error Authentication error data.
     */
    protected function set_error($error) {
        // Reset key.
        $this->key = null;

        $this->error = $error;
    }

    /**
     * Get authentication error.
     *
     * @return WP_Error|null.
     */
    protected function get_error() {
        return $this->error;
    }

    /**
     * Check for user permissions
     *
     * @param mixed $result Response to replace the requested version with.
     * @param WP_REST_Server $server Server instance.
     * @param WP_REST_Request $request Request used to generate the response.
     *
     * @return mixed
     */
    public function check_user_permissions($result, $server, $request) {

        if ($this->key instanceof TNP_REST_Authentication_Key) {
            // Check API Key permissions.
            $allowed = $this->check_permissions($request->get_method());
            if (is_wp_error($allowed)) {
                return $allowed;
            }
        }

        return $result;
    }

    /**
     * Check that the API keys provided have the proper key-specific permissions to either read or write API resources.
     *
     * @param string $method Request method.
     *
     * @return bool|WP_Error
     */
    private function check_permissions($method) {
        $permissions = $this->key->get_permissions();

        switch ($method) {
            case 'HEAD':
            case 'GET':
                if ('read' !== $permissions && 'read_write' !== $permissions) {
                    return new WP_Error('newsletter_rest_authentication_error', __('The API key provided does not have read permissions.', 'newsletter'), array('status' => 401));
                }
                break;
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                if ('write' !== $permissions && 'read_write' !== $permissions) {
                    return new WP_Error('newsletter_rest_authentication_error', __('The API key provided does not have write permissions.', 'newsletter'), array('status' => 401));
                }
                break;
            case 'OPTIONS':
                return true;
            default:
                return new WP_Error('newsletter_rest_authentication_error', __('Unknown request method.', 'newsletter'), array('status' => 401));
        }

        return true;
    }

    /**
     * If the consumer_key and consumer_secret $_GET parameters are NOT provided
     * and the Basic auth headers are either not present or the consumer secret does not match the consumer
     * key provided, then return the correct Basic headers and an error message.
     *
     * @param WP_REST_Response $response Current response being served.
     *
     * @return WP_REST_Response
     */
    public function send_unauthorized_headers($response) {
        if (is_wp_error($this->get_error()) && 'basic_auth' === $this->auth_method) {
            $auth_message = __('Newsletter API. Use a client key in the username field and a client secret in the password field.', 'newsletter');
            $response->header('WWW-Authenticate', 'Basic realm="' . $auth_message . '"', true);
        }

        return $response;
    }


    private static function rand_hash() {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            return sha1(wp_rand());
        }

        return bin2hex(openssl_random_pseudo_bytes(20));
    }

}
