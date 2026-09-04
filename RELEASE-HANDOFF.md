# SanctuaryShop release handoff

Last reviewed: 2026-09-03

## Current position

SanctuaryShop `1.9.5` is a release candidate. The implementation is ready for final production acceptance testing; no additional feature work is required before that pass.

Completed:

- 31 automated regression tests pass.
- The `pkg_sanctuaryshop_v1.9.5.zip` archive passes integrity validation.
- Git changes are committed and pushed to `origin/main`.
- Clean installation was tested on `pub.sacwan.org`.
- Upgrade from archived version 1.7 to the current version was tested successfully.
- Square and Stripe payment flows have been tested.
- Provider-specific payment guards and signed webhook handling are implemented.
- Inventory decrement and order completion are transactionally protected.
- Dashboard, reports, receipts, receipt settings, Help & Documentation, coupons, and downloads are implemented.
- SanctumDonation's working payment patterns were compared with SanctuaryShop.

## Final staging checklist

Use the exact `pkg_sanctuaryshop_v1.9.5.zip` package on `pub.sacwan.org` and record the result of each test.

- [ ] Clean installation from the package.
- [ ] Upgrade from an older installed version without uninstalling first.
- [ ] Guest checkout.
- [ ] Logged-in customer checkout.
- [ ] Square sandbox payment.
- [ ] Square refund and webhook behavior.
- [ ] Stripe test payment.
- [ ] Stripe webhook: configure the SanctuaryShop Stripe webhook signing secret and verify `payment_intent.succeeded` completes the order.
- [ ] Authorize.Net availability check. It is included for distribution, but it is explicitly untested because no sandbox account is available.
- [ ] Coupon percentage and fixed discounts, including limits and expired/invalid coupons.
- [ ] Inventory success, insufficient stock, and a simulated stock-decrement failure.
- [ ] Digital download using one real downloadable product fixture, including access control, token validation, expiry/limits, and traversal protection.
- [ ] Receipt appearance and configured wording.
- [ ] Reports page, filters, totals, and Top Products labels.
- [ ] Help & Documentation links and instructions.

## Important testing notes

- Do not uninstall before an upgrade test. Install an older version first, then install `1.9.5` over it.
- Reinstalling the same extension version may produce a Joomla “Copy file failed” or package installation error; that is not the normal upgrade path.
- The Stripe webhook endpoint is:
  `index.php?option=com_sanctuaryshop&task=webhook.stripe`
- The Stripe webhook secret belongs in SanctuaryShop's own Settings field; it is separate from SanctumDonation's configuration.
- Authorize.Net should be documented as supported-for-distribution but not personally sandbox-validated.

## Release materials still needed

- [ ] Final product name, price, and license terms.
- [ ] Public installation and upgrade instructions.
- [ ] Changelog/release notes for 1.9.5.
- [ ] Support contact and support policy.
- [ ] Customer refund policy.
- [ ] Download page or delivery process for the package.
- [ ] Backup and rollback procedure for customer sites.

## Release decision

After the checked staging items above are completed, SanctuaryShop `1.9.5` can be released for production sales. Any provider or feature that remains untested should be clearly labeled in the customer documentation rather than represented as personally verified.

