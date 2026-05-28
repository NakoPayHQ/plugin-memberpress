<?php
/**
 * Plugin Name: NakoPay for MemberPress
 * Plugin URI:  https://nakopay.com/integrations/memberpress
 * Description: Accept Bitcoin and crypto for MemberPress memberships and subscriptions.
 * Version: 0.3.1
 * Author:      NakoPay
 * Author URI:  https://nakopay.com
 * License:     MIT
 * Text Domain: nakopay-memberpress
 * Requires PHP: 8.0
 * Requires at least: 6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('NAKOPAY_MEPR_VERSION', '0.1.0');
define('NAKOPAY_MEPR_DIR', plugin_dir_path(__FILE__));
define('NAKOPAY_MEPR_URL', plugin_dir_url(__FILE__));
define('NAKOPAY_MEPR_FILE', __FILE__);

require_once NAKOPAY_MEPR_DIR . 'includes/bootstrap.php';
