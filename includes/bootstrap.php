<?php
/**
 * Bootstrap - register the NakoPay gateway with MemberPress.
 */

if (!defined('ABSPATH')) {
    exit;
}

function nakopay_mepr_init(): void
{
    if (!defined('MEPR_VERSION')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('NakoPay for MemberPress requires MemberPress to be installed and active.', 'nakopay-memberpress');
            echo '</p></div>';
        });
        return;
    }

    require_once NAKOPAY_MEPR_DIR . 'includes/class-nakopay-client.php';
    require_once NAKOPAY_MEPR_DIR . 'includes/class-mepr-nakopay-gateway.php';
    require_once NAKOPAY_MEPR_DIR . 'includes/class-nakopay-webhook.php';

    // Register gateway with MemberPress
    add_filter('mepr-gateway-paths', function (array $paths) {
        $paths[] = NAKOPAY_MEPR_DIR . 'includes';
        return $paths;
    });
}
add_action('plugins_loaded', 'nakopay_mepr_init', 20);
