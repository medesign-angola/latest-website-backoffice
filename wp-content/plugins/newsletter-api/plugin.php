<?php

require_once __DIR__ . '/autoloader.php';

use TNP\API\V2\ExtraFields\ExtraFields_Controller;
use TNP\API\V2\Lists\Lists_Controller;
use TNP\API\V2\Newsletters\Newsletters_Controller;
use TNP\API\V2\Subscribers\Subscribers_Controller;
use TNP\API\V2\Subscriptions\Subscriptions_Controller;
use TNP\API\V2\TNP_REST_Authentication_Key;

class NewsletterApi extends NewsletterAddon {

    /**
     * @var NewsletterApi
     */
    static $instance;
    var $table_name;
    static $authenticated = false;

    static function instance() {
        return self::$instance;
    }

    /**
     * 
     * @global wpdb $wpdb
     * @param string $version
     */
    function __construct($version = '') {
        global $wpdb;

        self::$instance = $this;
        $this->table_name = $wpdb->prefix . 'newsletter_api_keys';

        parent::__construct('api', $version);
        $this->setup_options();
    }

    /**
     * 
     * @global wpdb $wpdb
     * @param bool $first_install
     */
    function upgrade($first_install = false) {
        global $wpdb;
        parent::upgrade($first_install);

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
				  id int NOT NULL AUTO_INCREMENT,
				  user_id int NOT NULL default '0',
				  description VARCHAR(200) NULL default '',
				  client_key CHAR(64) NOT NULL default '',
				  client_secret CHAR(64) NOT NULL default '',
				  permissions VARCHAR(10) NOT NULL default '',			  
				  PRIMARY KEY  (id),
				  KEY client_key (client_key),
				  KEY client_secret (client_secret)
				) $charset_collate";

        dbDelta($sql);
    }

    function init() {
        add_action('rest_api_init', [$this, 'hook_rest_api_init']);

        if (is_admin()) {
            if (Newsletter::instance()->is_allowed()) {
                add_action('admin_menu', [$this, 'hook_admin_menu'], 100);
                add_filter('newsletter_menu_settings', [$this, 'hook_newsletter_menu_settings'], 1, 3);
            }
        }
    }

    /**
     * Called only for our endpoints, it checks if the client key and secret are present (as GET
     * or Basic Auth) and check if they're valid. If not, an error is returned (we could return a null
     * to let other authentication methods to do something... but it is our endpoint...).
     * If it is authenticated we store this information so endpoints can provide the requested service.
     * 
     * If the key and secret are missing, we consider this call as an anonymous call (for example to start a 
     * subscription flow).
     * 
     * We need to act here since on permission callback it's too late, WP sees the Basic Auth request and
     * tries to authenticate a user failing.
     * 
     * Not a really flexible design, actually.
     * 
     * @param type $result
     * @return \WP_Error|bool
     */
    function hook_rest_authentication_errors($result) {
        $logger = $this->get_logger();

        $logger->debug('REST Authentication');

        // Already authenticated? Since this is a call to our API, we ignore it.
//        if (!is_null($result)) {
//            $logger->debug('Pases result not null');
//            $logger->debug($result);
//            return $result;
//        }
        //$logger->debug($_GET);
        //$logger->debug($_SERVER);

        $client_key = '';
        $client_secret = '';

        if (!empty($_GET['client_key']) && !empty($_GET['client_secret'])) {
            $client_key = $_GET['client_key'];
            $client_secret = $_GET['client_secret'];
        }

        if (!$client_key && !empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            $client_key = $_SERVER['PHP_AUTH_USER'];
            $client_secret = $_SERVER['PHP_AUTH_PW'];
        }

        //$logger->debug($client_key);
        //$logger->debug($client_secret);
        // Consider this request a "public" request
        if (!$client_key || !$client_secret) {
            wp_set_current_user(0);
            return true;
        }

        // Get api key data.
        $this->key = $this->get_key_by_client_key($client_key);
        if (empty($this->key)) {
            return new WP_Error('tnp_auth_error', 'API key not found');
        }

        // Validate user secret.
        if (!hash_equals($this->key->get_client_secret(), $client_secret)) {
            return new WP_Error('tnp_auth_error', 'Invalid secret');
        }

        // We have not a user attached to this API request, but it is still a valid request
        wp_set_current_user(0);
        self::$authenticated = true;

        return true;
    }

    function is_authenticated() {
        return self::$authenticated;
    }

    function get_users($args, $format = OBJECT) {
        global $wpdb;

        $this->logger->debug($args);

        $default_args = array(
            'page' => 1,
            'per_page' => 10
        );

        $args = array_merge($default_args, $args);

        $query = 'SELECT * FROM ' . NEWSLETTER_USERS_TABLE . ' ';
        $query_args = [];

        $query .= ' LIMIT %d OFFSET %d';
        $query_args[] = (int) $args['per_page'];
        $query_args[] = ( (int) $args['page'] - 1 ) * (int) $args['per_page'];

        $records = $wpdb->get_results($wpdb->prepare($query, $query_args), $format);

        if ($wpdb->last_error) {
            $this->logger->error($wpdb->last_error);

            return null;
        }

        return $records;
    }

    function hook_newsletter_menu_settings($entries) {
        $entries[] = array(
            'label' => 'API',
            'url' => '?page=newsletter_api_index',
            'description' => ''
        );

        return $entries;
    }

    function hook_admin_menu() {
        add_submenu_page('newsletter_main_index', 'API', '<span class="tnp-side-menu">API</span>', 'manage_options', 'newsletter_api_index', function () {
            require __DIR__ . '/admin/index.php';
        });
    }

    /**
     * 
     * @param WP_REST_Server $server
     */
    function hook_rest_api_init($server) {

        // Very bad, but WP does not give other opportunities. We check if the route match our version 2 endpoints and
        // activate our legacy authentication.
        if (isset($GLOBALS['wp']->query_vars['rest_route'])) {
            $route = $GLOBALS['wp']->query_vars['rest_route'];
            if (strpos($route, '/newsletter/v2') === 0) {
                add_filter('rest_authentication_errors', [$this, 'hook_rest_authentication_errors']);
            }
        }

        require_once NEWSLETTER_INCLUDES_DIR . '/module.php';

        require_once __DIR__ . '/v1/TNP_REST_Controller.php';

        // V1 REST API registration
        new TNP_REST_Controller($this->options);

        // V2 REST API registration
        ( new Subscribers_Controller())->register_routes();
        ( new Subscriptions_Controller())->register_routes();
        ( new Lists_Controller())->register_routes();
        ( new ExtraFields_Controller())->register_routes();
        ( new Newsletters_Controller())->register_routes();
    }

    public function generate_user_api_key($user_id, $permissions, $description) {
        // Created API keys.
        $permissions = in_array($permissions, array(
                    'read',
                    'write',
                    'read_write'
                        ), true) ? sanitize_text_field($permissions) : 'read';

        $client_key = self::rand_hash();
        $client_secret = self::rand_hash();
        $description = sanitize_text_field($description);

        $key = new TNP_REST_Authentication_Key(0, $user_id, $client_key, $client_secret, $permissions, $description);
        $key_id = $this->save_key($key);
        $key->set_id($key_id);

        return $key;
    }

    public function delete_key($id) {
        global $wpdb;
        return $wpdb->delete($this->table_name, ['id' => $id]);
    }

    public function get_keys() {
        global $wpdb;

        $records = $wpdb->get_results("SELECT * FROM {$this->table_name}");

        return array_map([$this, 'convert_key'], $records);
    }

    private function convert_key($obj) {
        return new TNP_REST_Authentication_Key(
                (int) $obj->id,
                (int) $obj->user_id,
                $obj->client_key,
                $obj->client_secret,
                $obj->permissions,
                $obj->description
        );
    }

    /**
     * @param TNP_REST_Authentication_Key $key
     *
     * @return int|false
     */
    public function save_key($key) {
        global $wpdb;
        $ret = $wpdb->insert($this->table_name,
                [
                    'user_id' => (int) $key->get_user_id(),
                    'description' => sanitize_text_field($key->get_description()),
                    'client_key' => $key->get_client_key(),
                    'client_secret' => $key->get_client_secret(),
                    'permissions' => $key->get_permissions()
                ]
        );

        return $ret > 0 ? $wpdb->insert_id : false;
    }

    public function get_key_by_client_key($client_key) {
        global $wpdb;

        $query = "SELECT * FROM {$this->table_name} WHERE client_key = %s";

        $record = $wpdb->get_row($wpdb->prepare($query, $client_key));

        if (!empty($record)) {
            return $this->convert_key($record);
        }

        return null;
    }

    private static function rand_hash() {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            return sha1(wp_rand());
        }

        return bin2hex(openssl_random_pseudo_bytes(20));
    }

}
