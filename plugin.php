<?php
/**
 * BCA Swiss Army Knife for Wordpress
 *
 * @category Plugin
 * @package  Wordpress
 * @author   Brodkin CyberArts <support@brodkinca.com>
 * @license  MIT
 * @link     http://brodkinca.com/
 *
 * Plugin Name: BCA Swiss Army Knife
 * Version: 1.1.1
 * Description: Assists Brodkin CyberArts in providing support and troubleshooting.
 * Author: Brodkin CyberArts
 * Author URI: http://brodkinca.com/
 */
namespace BCA\WPSAN;

require_once __DIR__.'/src/Dashboard.php';
require_once __DIR__.'/src/Login.php';

define('WPSAN_PATH', __FILE__);

/**
 * Load View
 *
 * @param string $path Relative path within views directory without extension.
 * @param array  $vars Variables to be used within view file.
 *
 * @return void
 */
function view ($path, array $vars = array())
{
    extract($vars);
    require __DIR__.'/views/'.$path.'.php';
}

// Instantiate the plugin
new Dashboard();
new Login();
