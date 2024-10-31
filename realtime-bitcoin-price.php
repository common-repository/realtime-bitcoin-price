<?php
/*
Plugin Name: Bitstamp Realtime Bitcoin Price
Version: 1.0
Plugin URI: https://wordpress.org/plugins/realtime-bitcoin-price/
Author: CONDACORE
Author URI: https://condacore.com/
Description: Adds the current Bitstamp Bitcoin Price to your WordPress Website. The Price updates automatically. It's not required to reload the whole site. Shortcode: <code>[bitstamp_price]</code>
Text Domain: realtime-bitcoin-price
Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('BITSTAMP_PRICE_TICKER')) {

    class BITSTAMP_PRICE_TICKER {

        function __construct() {
            $this->bpt_plugin_includes();
        }

        function bpt_plugin_includes() {
            add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
            add_action('wp_enqueue_scripts', 'bpt_header_script');
            add_shortcode('bitstamp_price', 'bpt_init');
            //allows shortcode execution in the widget, excerpt and content
            add_filter('widget_text', 'do_shortcode');
            add_filter('the_excerpt', 'do_shortcode', 11);
            add_filter('the_content', 'do_shortcode', 11);
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function plugins_loaded_handler()
        {
            load_plugin_textdomain('clappr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
        }
    }
    $GLOBALS['bitstamp_bitcoin_price'] = new BITSTAMP_PRICE_TICKER();
}

function bpt_header_script() {
    if (!is_admin()) {
        $plugin_url = plugins_url('', __FILE__);
        wp_register_script('pusher-js', $plugin_url . '/js/pusher.min.js', array(), '4.2.1', false);
        wp_enqueue_script('pusher-js');
    }
}

function bpt_footer_script()
{
    echo "<!-- Bitstamp Live Ticker by CONDACORE - Start -->\n";
    echo "<script type=\"text/javascript\">\n";
    echo "document.getElementById('bitstamp_price').innerHTML = 'loading...';\n";
    echo "var pusher = new Pusher('de504dc5763aeef9ff52');\n";
    echo "var trades_channel = pusher.subscribe('live_trades');\n";
    echo "trades_channel.bind('trade', function(data) {\n";
    echo "	document.getElementById('bitstamp_price').innerHTML = '$'+data.price;\n";
    echo "});\n";
    echo "</script>\n";
    echo "<!-- Bitstamp Live Ticker by CONDACORE - End -->\n";
}

function bpt_init($atts) {
	add_action('wp_footer', 'bpt_footer_script');
    extract(shortcode_atts(array(
        'size' => '',
        'color' => ''
    ), $atts));
     if ($size || $color) {
        $styles = "style=\"font-size: $size; color: $color;\"";
    }
    $output = "<span id=\"bitstamp_price\" $styles></span>";

    return $output;
}
