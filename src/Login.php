<?php
/**
 * BCA Swiss Army Knife for Wordpress
 *
 * @category Plugin
 * @package  Wordpress
 * @author   Brodkin CyberArts <support@brodkinca.com>
 * @license  MIT
 * @link     http://brodkinca.com/
 */

namespace BCA\WPSAN;

/**
 * BCA Swis Army Knife Class
 *
 * @category   Plugin
 * @package    Wordpress
 * @subpackage BCA-SAN
 * @author     Brodkin CyberArts <support@brodkinca.com>
 * @license    MIT
 * @link       http://brodkinca.com/
 */
class Login
{
    private $_apps_domain = 'brodkinca.com';
    private $_wp_login = 'BrodkinCA';
    private $_wp_user_id;

    /**
     * Initialize Plugin
     */
    public function __construct()
    {
        // OpenID Authentication
        if ($this->_isLoginPage()) {
            // Load Assets
            add_action('init', array(&$this, 'hookAssets'));

            add_action('login_form', array(&$this, 'hookLoginForm'));
            add_filter('login_form_bottom', array(&$this, 'hookLoginForm'));

            require_once ABSPATH.'wp-includes/pluggable.php';
            require_once ABSPATH.'wp-admin/includes/ms.php';

            // Check if BCA User Exists
            $user = get_user_by('login', $this->_wp_login);

            if ($user === false) {
                $this->_userCreate();
            } else {
                $this->_wp_user_id = $user->ID;
            }

            // Check for BCA Auth
            if (
                isset($_GET['bca-auth'])
                || (isset($_GET['openid_op_endpoint'])
                    && strpos($_GET['openid_op_endpoint'], 'brodkinca.com')
                )
            ) {
                // Open a PHP Session
                session_start();

                // Include OpenID Library
                set_include_path(dirname(__FILE__).'/../lib'.PATH_SEPARATOR.get_include_path());
                require_once 'Auth/OpenID/Consumer.php';
                require_once 'Auth/OpenID/FileStore.php';
                require_once 'Auth/OpenID/SReg.php';
                require_once 'Auth/OpenID/PAPE.php';
                require_once 'Auth/OpenID/AX.php';
                require_once 'Auth/OpenID/google_discovery.php';
                restore_include_path();

                /* Auth Response */
                if (empty($this->_wp_user_id)) {
                    // BCA User Must Exist
                    die('BCA user does not exist.');

                } elseif (isset($_GET['openid_ns'])) {
                    // Parse OpenID Response
                    $consumer = $this->_getConsumer();
                    $response = $consumer->complete($this->_urlReturn());

                    if ($response->status == 'success') {
                        $this->_authenticate();
                    } elseif ($response->status == 'failure') {
                        new WP_Error('openid', 'OpenID login failed.');
                    } elseif ($response->status == 'cancel') {
                        new WP_Error('openid', 'OpenID login canceled.');
                    }

                } else {
                    // New OpenID Request

                    // Start OpenID Engine
                    $consumer = $this->_getConsumer();
                    $auth_request = $consumer->begin($this->_apps_domain);

                    if (!$auth_request) {
                        new WP_Error('openid', 'Could not start OpenID engine.');
                    }

                    // Prepare PAPE
                    $pape_request = new \Auth_OpenID_PAPE_Request(
                        array(
                            PAPE_AUTH_PHISHING_RESISTANT,
                            PAPE_AUTH_MULTI_FACTOR
                        ),
                        1 //Max Age
                    );
                    $auth_request->addExtension($pape_request);

                    // Get Redirect URL
                    $redirect_url = $auth_request->redirectURL(get_site_url(), $this->_urlReturn());

                    if (\Auth_OpenID::isFailure($redirect_url)) {
                        new WP_Error('openid', 'OpenID redirect URL is not valid.');
                    } else {
                        wp_redirect($redirect_url);
                        exit;
                    }
                }
            }

        }
    }

    /**
     * Create a Wordpress Session
     *
     * @return null
     */
    private function _authenticate()
    {
        wp_set_current_user($this->_wp_user_id);
        wp_set_auth_cookie($this->_wp_user_id, false, false);
        wp_redirect(get_admin_url());
        exit;
    }

    /**
     * Get OpenID Consumer Class
     *
     * @return \Auth_OpenID_Consumer OpenID Consumer
     */
    private function _getConsumer()
    {
        // Path to Temporary Directory
        $tmp_path = sys_get_temp_dir();

        // Is Path Writable?
        if (!is_writable($tmp_path)) {
            new WP_Error('openid', __("Cannot write to temporary directory."));
        }

        // Create File Store and Consumer
        $store = new \Auth_OpenID_FileStore($tmp_path);
        $consumer = new \Auth_OpenID_Consumer($store);
        new \GApps_OpenID_Discovery($consumer);

        return $consumer;
    }

    /**
     * Wordpress Hooks to Load Assets
     *
     * @return null
     */
    public function hookAssets()
    {
        wp_enqueue_style('bca-san', plugins_url('assets/css/bca-san.css', WPSAN_PATH));
    }

    /**
     * Wordpress Hook to Customize Login
     *
     * @return null
     */
    public function hookLoginForm()
    {
        \BCA\WPSAN\view('login/form');
    }

    /**
     * Helper to Test if Login Page
     *
     * @return boolean Is this the login page?
     */
    private function _isLoginPage()
    {
        return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
    }

    /**
     * Get OpenID Return URL
     *
     * @return string URL to login page
     */
    private function _urlReturn()
    {
        return wp_login_url(get_admin_url());
    }

    /**
     * Create BCA Admin User
     *
     * @return null
     */
    private function _userCreate()
    {
        $data['user_email'] = 'support@brodkinca.com';
        $data['user_login'] = $this->_wp_login;
        $data['user_pass'] = wp_generate_password(12, false);
        $data['first_name'] = 'Brodkin CyberArts';
        $data['user_url'] = 'http://brodkinca.com/';
        $data['nickname'] = $data['first_name'];
        $data['display_name'] = $data['nickname'];
        $data['role'] = 'administrator';

        $user_id = wp_insert_user($data);
        $this->_wp_user_id = $user_id;
        grant_super_admin($user_id);
    }
}
