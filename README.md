# NakoPay for MemberPress

Accept Bitcoin and other crypto in MemberPress with a one-flat-fee, non-custodial
checkout. Wallet-to-wallet - NakoPay never holds your funds.

[![Status](https://img.shields.io/badge/status-beta-blue)](https://nakopay.com/integrations)
[![License](https://img.shields.io/badge/license-MIT-green)](../LICENSE)

## Install

```
wp plugin install nakopay-memberpress --activate
```

## Configure

1. Get an API key from <https://nakopay.com/dashboard/api-keys>.
2. In MemberPress admin: MemberPress → Settings → Payments → Add Method → NakoPay
3. Set the webhook URL shown in the plugin settings inside your NakoPay
   dashboard (Settings → Webhooks).

## Test mode

Use `sk_test_*` keys to run the full checkout against the NakoPay sandbox.
No real funds move. Flip to `sk_live_*` when you're ready for production.

## Supported features

- [x] Recurring invoices
- [x] One-time invoices
- [x] Auto-reconciliation
- [x] Multi-currency
- [x] Test mode

## Local development

See [`../CONTRIBUTING.md`](../CONTRIBUTING.md) for the full setup. Quick
start for PHP plugins:

- PHP stack: see CONTRIBUTING § "Local development per host".
- Run `bash ../scripts/check-no-internal-urls.sh .` before opening a PR.

## Release

Tag-driven from the monorepo:

```
plugins/scripts/release.sh memberpress 0.1.0
```

The matching workflow at `.github/workflows/release-memberpress.yml` handles the
upload to the marketplace. Full runbook in [`../PUBLISHING.md`](../PUBLISHING.md).

## Issues

File on <https://github.com/NakoPayHQ/plugin-memberpress/issues>.

## License

MIT - see [`../LICENSE`](../LICENSE).
