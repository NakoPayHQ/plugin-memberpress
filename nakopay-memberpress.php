<?php
/**
 * Plugin Name: NakoPay for MemberPress
 * Plugin URI: https://nakopay.com/integrations
 * Description: Recurring crypto subscriptions for MemberPress.
 * Version: 0.1.0
 * Author: NakoPay
 * Author URI: https://nakopay.com
 * License: MIT
 * Text Domain: nakopay-memberpress
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH') && !defined('NAKOPAY_CLI')) {
    exit;
}

// Skeleton: real implementation lives in includes/.
// This file is the host-system entry point only - keep it thin.
require_once __DIR__ . '/includes/bootstrap.php';
