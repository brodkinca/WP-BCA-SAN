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
 * Version: 1.1.0
 * Description: Assists Brodkin CyberArts in providing support and troubleshooting.
 * Author: Brodkin CyberArts
 * Author URI: http://brodkinca.com/
 */
namespace BCA\WPSAN;

require 'vendor/autoload.php';

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
new Login();
