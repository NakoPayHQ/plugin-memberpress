<?php
/**
 * NakoPay webhook handler for MemberPress.
 *
 * Listens on: ?mepr-listener=nakopay
 * Verifies HMAC-SHA256 signature, updates transaction status.
 */

if (!defined('ABSPATH')) {
    exit;
}

class NakoPay_MePr_Webhook
{
    private array $gateway_settings;

    public function __construct(array $gateway_settings)
    {
        $this->gateway_settings = $gateway_settings;
    }

    public function handle(): void
    {
        $rawBody   = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_X_NAKOPAY_SIGNATURE'] ?? '';

        $client = new NakoPay_MePr_Client($this->gateway_settings);

        if (!$client->verifyWebhook($rawBody, $sigHeader)) {
            status_header(401);
            echo wp_json_encode(['error' => 'Invalid signature']);
            exit;
        }

        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            status_header(400);
            echo wp_json_encode(['error' => 'Invalid JSON']);
            exit;
        }

        $event_type = $payload['type'] ?? '';
        $invoice    = $payload['data'] ?? [];
        $invoice_id = $invoice['id'] ?? '';

        if ($invoice_id === '') {
            status_header(400);
            echo wp_json_encode(['error' => 'Missing invoice ID']);
            exit;
        }

        // Find MemberPress transaction by invoice ID
        $txn_id = $this->findTransactionByInvoice($invoice_id);
        if (!$txn_id) {
            status_header(404);
            echo wp_json_encode(['error' => 'Transaction not found']);
            exit;
        }

        $txn = new MeprTransaction($txn_id);

        switch ($event_type) {
            case 'invoice.paid':
                $this->handlePaid($txn, $invoice);
                break;

            case 'invoice.expired':
                $this->handleExpired($txn);
                break;

            case 'invoice.canceled':
                $this->handleCanceled($txn);
                break;
        }

        status_header(200);
        echo wp_json_encode(['ok' => true]);
        exit;
    }

    private function findTransactionByInvoice(string $invoice_id): ?int
    {
        global $wpdb;

        $txn_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_nakopay_invoice_id' AND meta_value = %s LIMIT 1",
            $invoice_id
        ));

        return $txn_id ? (int) $txn_id : null;
    }

    private function handlePaid(MeprTransaction $txn, array $invoice): void
    {
        if ($txn->status === MeprTransaction::$complete_str) {
            return; // Idempotent
        }

        if (!empty($invoice['txid'])) {
            update_post_meta($txn->id, '_nakopay_txid', sanitize_text_field($invoice['txid']));
        }

        $txn->trans_num = $invoice['txid'] ?? $invoice['id'] ?? '';
        $txn->status    = MeprTransaction::$complete_str;
        $txn->store();

        // Activate the subscription/membership
        MeprUtils::send_signup_notices($txn);
        MeprEvent::record('transaction-completed', $txn);
    }

    private function handleExpired(MeprTransaction $txn): void
    {
        if ($txn->status === MeprTransaction::$complete_str) {
            return;
        }
        $txn->status = MeprTransaction::$failed_str;
        $txn->store();
    }

    private function handleCanceled(MeprTransaction $txn): void
    {
        if ($txn->status === MeprTransaction::$complete_str) {
            return;
        }
        $txn->status = MeprTransaction::$failed_str;
        $txn->store();
    }
}

/**
 * Register the webhook listener.
 */
add_action('init', function () {
    if (!isset($_GET['mepr-listener']) || $_GET['mepr-listener'] !== 'nakopay') {
        return;
    }

    if (!defined('MEPR_VERSION')) {
        status_header(500);
        echo wp_json_encode(['error' => 'MemberPress not active']);
        exit;
    }

    // Find the NakoPay gateway settings from MemberPress
    $mepr_options = MeprOptions::fetch();
    $gateway_settings = [];
    foreach ($mepr_options->integrations as $integration) {
        if (($integration['gateway'] ?? '') === 'MeprNakoPayGateway') {
            $gateway_settings = $integration;
            break;
        }
    }

    $webhook = new NakoPay_MePr_Webhook($gateway_settings);
    $webhook->handle();
});
