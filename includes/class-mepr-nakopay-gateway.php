<?php
/**
 * MemberPress gateway implementation for NakoPay.
 *
 * Extends MeprBaseRealGateway to integrate with MemberPress's
 * payment gateway system. Uses hosted checkout (redirect).
 */

if (!defined('ABSPATH')) {
    exit;
}

class MeprNakoPayGateway extends MeprBaseRealGateway
{
    public static $has_spc_form = false;

    /**
     * Load gateway settings from MemberPress options.
     */
    public function load($settings): void
    {
        $this->settings = (object) array_merge([
            'api_key'        => '',
            'webhook_secret' => '',
            'test_mode'      => false,
        ], (array) ($settings ?? []));
    }

    /**
     * Set default settings.
     */
    public function set_defaults(): void
    {
        if (!isset($this->settings)) {
            $this->settings = (object) [];
        }
        $this->settings->api_key        = $this->settings->api_key ?? '';
        $this->settings->webhook_secret = $this->settings->webhook_secret ?? '';
        $this->settings->test_mode      = $this->settings->test_mode ?? false;
    }

    /**
     * Gateway capabilities.
     */
    public function capabilities(): array
    {
        return ['process-payments', 'process-refunds'];
    }

    /**
     * Process a payment - redirect to NakoPay hosted checkout.
     */
    public function process_payment($txn): void
    {
        if (isset($txn) && $txn instanceof MeprTransaction) {
            $usr     = $txn->user();
            $product = $txn->product();

            $client = new NakoPay_MePr_Client([
                'api_key'        => $this->settings->api_key,
                'webhook_secret' => $this->settings->webhook_secret,
            ]);

            $result = $client->createInvoice([
                'amount'         => $txn->total,
                'currency'       => $txn->get_currency() ?: 'USD',
                'description'    => sprintf('MemberPress - %s', $product->post_title),
                'customer_email' => $usr->user_email,
                'mepr_txn_id'    => $txn->id,
            ]);

            if (!($result['_ok'] ?? false) || empty($result['id'])) {
                $txn->status = MeprTransaction::$failed_str;
                $txn->store();
                $msg = $result['_error'] ?? $result['message'] ?? 'Unknown error';
                wp_die(
                    esc_html(sprintf(__('NakoPay error: %s', 'nakopay-memberpress'), $msg)),
                    __('Payment Error', 'nakopay-memberpress'),
                    ['response' => 500, 'back_link' => true]
                );
                return;
            }

            // Store invoice ID
            update_post_meta($txn->id, '_nakopay_invoice_id', sanitize_text_field($result['id']));

            $checkout_url = $result['checkout_url'] ?? '';
            if ($checkout_url !== '') {
                MeprUtils::wp_redirect($checkout_url);
            }
        }
    }

    /**
     * Record a payment (called after webhook confirmation).
     */
    public function record_payment(): void
    {
        // Handled via webhook - see NakoPay_MePr_Webhook
    }

    /**
     * Process a refund.
     */
    public function process_refund(MeprTransaction $txn): void
    {
        // NakoPay refunds are initiated from the NakoPay dashboard
        $txn->status = MeprTransaction::$refunded_str;
        $txn->store();
    }

    /**
     * Record a refund.
     */
    public function record_refund(): void
    {
        // Handled via webhook
    }

    /**
     * Display gateway options in MemberPress settings.
     */
    public function display_options_form(): void
    {
        $api_key        = $this->settings->api_key ?? '';
        $webhook_secret = $this->settings->webhook_secret ?? '';
        $test_mode      = $this->settings->test_mode ?? false;
        $webhook_url    = home_url('/?mepr-listener=nakopay');

        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="nakopay_api_key"><?php esc_html_e('API Key', 'nakopay-memberpress'); ?></label></th>
                <td>
                    <input type="text" id="nakopay_api_key" name="<?php echo esc_attr($this->get_field_name('api_key')); ?>" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('Your NakoPay Secret key (sk_test_* or sk_live_*). Get it at nakopay.com/dashboard/api-keys.', 'nakopay-memberpress'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="nakopay_webhook_secret"><?php esc_html_e('Webhook Secret', 'nakopay-memberpress'); ?></label></th>
                <td>
                    <input type="text" id="nakopay_webhook_secret" name="<?php echo esc_attr($this->get_field_name('webhook_secret')); ?>" value="<?php echo esc_attr($webhook_secret); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('Starts with whsec_. Get it at nakopay.com/dashboard/webhooks.', 'nakopay-memberpress'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Webhook URL', 'nakopay-memberpress'); ?></th>
                <td>
                    <code><?php echo esc_html($webhook_url); ?></code>
                    <p class="description"><?php esc_html_e('Set this URL in your NakoPay dashboard. Subscribe to: invoice.paid, invoice.expired', 'nakopay-memberpress'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Validate gateway options.
     */
    public function validate_options_form($errors): array
    {
        return $errors;
    }

    /**
     * Display the payment form (minimal - we redirect to hosted checkout).
     */
    public function display_payment_form($amount, $user, $product_id, $txn_id): void
    {
        echo '<p>' . esc_html__('You will be redirected to NakoPay to complete your Bitcoin payment.', 'nakopay-memberpress') . '</p>';
    }

    /**
     * Enqueue gateway scripts/styles (none needed for hosted checkout).
     */
    public function enqueue_payment_form_scripts(): void
    {
        // No scripts needed - we use hosted checkout
    }

    /**
     * Return the gateway name.
     */
    public function name(): string
    {
        return 'NakoPay (Bitcoin)';
    }

    /**
     * Return the gateway icon URL.
     */
    public function icon(): string
    {
        return NAKOPAY_MEPR_URL . 'assets/img/logo.png';
    }

    /**
     * Return the gateway description.
     */
    public function desc(): string
    {
        return __('Pay with Bitcoin via NakoPay. Non-custodial, wallet-to-wallet.', 'nakopay-memberpress');
    }
}
