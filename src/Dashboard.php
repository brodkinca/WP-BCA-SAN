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
 * BCA WPSAN Dashboard Class
 *
 * @category   Plugin
 * @package    Wordpress
 * @subpackage BCA-SAN
 * @author     Brodkin CyberArts <support@brodkinca.com>
 * @license    MIT
 * @link       http://brodkinca.com/
 */
class Dashboard
{
    /**
     * Initialize Plugin
     */
    public function __construct()
    {
        // WP-Admin Enhancements
        if (is_admin()) {
            add_action('wp_dashboard_setup', array(&$this, 'hookWidgets'));
        }
    }

    /**
     * Wordpress Hook to Add Dashboard Widget
     *
     * @return null
     */
    public function hookWidgets()
    {
        require_once ABSPATH.'wp-admin/includes/template.php';
        require_once ABSPATH.'wp-admin/includes/screen.php';
        require_once ABSPATH.'wp-admin/includes/dashboard.php';

        wp_add_dashboard_widget('bca-rss', 'News from Brodkin CyberArts', array(&$this, 'widgetRSS'));
    }

    /**
     * Wordpress Dashboard RSS Widget
     *
     * @return null
     */
    public function widgetRSS()
    {
        require_once ABSPATH.WPINC.'/feed.php';

        // Get a SimplePie feed object from the specified feed source.
        $rss = fetch_feed('http://feeds.feedburner.com/BCA-WP-SAN');
        if (!is_wp_error($rss)) {
            $maxitems = $rss->get_item_quantity(1);
            $rss_items = $rss->get_items(0, $maxitems);

            if ($maxitems == 0) echo '<li>News and updates coming soon!</li>';

            echo '<div class="rss-widget"><ul>';

            foreach ($rss_items as $item) {
                echo '<li>';
                echo '<a class="rsswidget" href="'.esc_url($item->get_permalink()).'"';
                echo 'title="Posted">';
                echo esc_html($item->get_title());
                echo '</a>';
                echo '<span class="rss-date">'.$item->get_date('F j, Y').'</span>';
                echo '<p>'.$item->get_description().'</p>';
                echo '</li>';
            }

            echo '</ul></div>';
        }
    }
}
