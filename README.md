# NakoPay for MemberPress

Accept Bitcoin and crypto payments for MemberPress memberships, courses, and
subscriptions. Non-custodial, wallet-to-wallet - NakoPay never holds your funds.

[![Status](https://img.shields.io/badge/status-stable-blue)](https://nakopay.com/integrations/memberpress)
[![License](https://img.shields.io/badge/license-MIT-green)](../LICENSE)

## Requirements

- WordPress 6.0+
- MemberPress 1.9+
- PHP 8.0+
- A NakoPay account ([sign up free](https://nakopay.com))

## Install

### Option A: Upload zip (recommended)

1. Download `nakopay-memberpress.zip` from [the latest release](https://github.com/NakoPayHQ/plugin-memberpress/releases/latest)
2. In wp-admin: **Plugins > Add New > Upload Plugin > Choose File** > pick the zip > **Install Now > Activate**

### Option B: Clone from GitHub

```bash
cd wp-content/plugins
git clone https://github.com/NakoPayHQ/plugin-memberpress.git nakopay-memberpress
```

Activate in wp-admin: **Plugins > Installed Plugins > NakoPay for MemberPress > Activate**

## Configure

1. Get your API key at [nakopay.com/dashboard/api-keys](https://nakopay.com/dashboard/api-keys)
2. In wp-admin: **MemberPress > Settings > Payments tab**
3. Click **Add Payment Method** (or the + button)
4. Select **NakoPay (Bitcoin)** from the Gateway dropdown
5. Paste your **Secret key** (`sk_test_*` or `sk_live_*`) in the API Key field
6. Paste your **Webhook Signing Secret** (`whsec_*`) in the Webhook Secret field
7. Click **Update Options**

### Webhook URL

Set this URL in your NakoPay dashboard at [nakopay.com/dashboard/webhooks](https://nakopay.com/dashboard/webhooks):

```
https://your-site.com/?mepr-listener=nakopay
```

Subscribe to events: `invoice.paid`, `invoice.expired`

## Test mode

Use `sk_test_*` keys to test the full checkout flow without real funds. Flip
to `sk_live_*` when you're ready for production.

## How it works

1. Member selects a membership and chooses "NakoPay (Bitcoin)" as payment method
2. Plugin creates a NakoPay invoice via the API
3. Member is redirected to NakoPay's hosted checkout page (QR code + pay address)
4. On payment confirmation, NakoPay sends a webhook to your site
5. Plugin verifies the HMAC-SHA256 signature and activates the membership
6. Member gets immediate access to protected content

## Supported features

- [x] One-time membership payments
- [x] Hosted checkout (redirect)
- [x] HMAC-SHA256 webhook verification
- [x] Test/live mode toggle
- [x] Transaction ID storage
- [x] Automatic membership activation
- [x] Admin settings panel
- [ ] Recurring subscription renewals (planned)

## Troubleshooting

**NakoPay doesn't appear in the gateway dropdown**
- Make sure MemberPress is installed and active before activating NakoPay for MemberPress
- Deactivate and reactivate the NakoPay plugin

**Membership doesn't activate after payment**
1. Check the webhook URL in nakopay.com/dashboard/webhooks matches your domain
2. Re-paste the `whsec_*` secret (don't retype)
3. Resend the webhook from the dashboard

**"NakoPay error" on checkout**
- Verify your API key is valid at nakopay.com/dashboard/api-keys
- Check that the API key starts with `sk_test_` or `sk_live_`

## Support

- [Open a GitHub issue](https://github.com/NakoPayHQ/plugin-memberpress/issues)
- [NakoPay documentation](https://nakopay.com/docs)
- [Contact support](https://nakopay.com/contact)

## About MemberPress

[MemberPress](https://memberpress.com/) - WordPress membership plugin for subscriptions, courses, and paywalls. Visit their website to learn more about the platform and its features.

## License

MIT


## Subscription Webhooks

NakoPay supports recurring crypto subscriptions. When enabled, your webhook endpoint receives these additional events:

| Event | Description |
|-------|-------------|
| `subscription.created` | New subscription activated |
| `subscription.renewed` | Renewal invoice generated, billing period advanced |
| `subscription.past_due` | Payment overdue (3-day grace period expired) |
| `subscription.updated` | Subscription details changed |
| `subscription.canceled` | Subscription ended (manual cancel or non-payment after 7 days past due) |

Each event payload includes the subscription `id`, `amount`, `coin`, `interval`, `status`, and `metadata` (plan name, plan ID). Renewal and past-due events also include the `invoice_id`.

To enable subscription webhooks, add `subscription.*` to your webhook endpoint's enabled events in your NakoPay dashboard at `nakopay.com/dashboard/settings`.
