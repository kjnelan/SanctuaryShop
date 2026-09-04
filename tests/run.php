<?php
/**
 * SanctuaryShop dependency-free regression checks.
 *
 * These checks intentionally run without Joomla so they can be used in CI on
 * the source/package tree. Full payment-provider sandbox tests still require
 * a Joomla staging site and provider credentials.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$passed = 0;
$failed = 0;

function check(bool $condition, string $name): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "PASS: {$name}\n";
        return;
    }
    $failed++;
    echo "FAIL: {$name}\n";
}

function source(string $relative): string
{
    global $root;
    $path = $root . '/' . $relative;
    if (!is_file($path)) {
        throw new RuntimeException("Missing test subject: {$relative}");
    }
    return file_get_contents($path);
}

$checkout = source('site/src/Model/CheckoutModel.php');
$controller = source('site/src/Controller/CheckoutController.php');
$webhook = source('site/src/Controller/WebhookController.php');
$cart = source('site/src/Model/CartModel.php');
$download = source('site/src/Controller/DownloadController.php');
$installer = source('script.php');
$installSql = source('admin/sql/install.mysql.utf8.sql');
$manifest = source('sanctuaryshop.xml');

// Checkout and payment callback guards.
check(str_contains($checkout, "!== 'stripe'") && str_contains($checkout, "!== 'authorize_net'"), 'provider-specific order checks are present');
check(str_contains($controller, 'Session::checkToken()') && substr_count($controller, 'Session::checkToken(\'get\')') >= 3, 'checkout endpoints require CSRF tokens');
check(str_contains($webhook, 'hash_equals') && str_contains($webhook, 'INSERT IGNORE') && str_contains($webhook, 'finalizePaidOrder'), 'Square webhook verifies, deduplicates, and finalizes');
check(str_contains($webhook, 'payment_intent.succeeded') && str_contains($webhook, 'verifyStripeSignature') && str_contains($webhook, 'amount_received'), 'Stripe webhook verifies and matches the order');

// Inventory atomicity regression checks.
$finalizeStart = strpos($checkout, 'public function finalizePaidOrder');
$finalizeEnd = strpos($checkout, 'private function generateDownloadTokens', $finalizeStart);
$finalize = substr($checkout, $finalizeStart, $finalizeEnd - $finalizeStart);
check(strpos($finalize, 'transactionStart') < strpos($finalize, 'decrementStock'), 'inventory transaction starts before stock decrement');
check(strpos($finalize, 'decrementStock') < strpos($finalize, "set('status = '"), 'stock decrement occurs before completion status update');
check(str_contains($finalize, 'transactionRollback') && str_contains($finalize, 'transactionCommit'), 'inventory and order completion have rollback/commit handling');
check(substr_count($finalize, 'decrementStock') === 1, 'stock is decremented only inside finalization transaction');
$inventoryStart = strpos($checkout, 'private function decrementStock');
$inventory = substr($checkout, $inventoryStart);
check(str_contains($inventory, 'throw $e;'), 'inventory failures propagate to the transaction');

// Coupon rules.
check(str_contains($cart, 'usage_limit') && str_contains($cart, 'used_count') && str_contains($cart, 'COM_SANCTUARYSHOP_COUPON_EXHAUSTED'), 'coupon usage limits are enforced');
check(str_contains($cart, 'return min((float) $coupon->value, $subtotal);') && str_contains($cart, 'min(100, max(0, (float) $coupon->value))'), 'fixed and percentage coupon discounts are bounded');

// Digital-download safety.
check(str_contains($download, 'strlen($token) !== 64') && str_contains($download, 'ctype_xdigit'), 'download tokens are format-validated');
check(str_contains($download, 'realpath($basePath') && str_contains($download, '$baseRealPath . DIRECTORY_SEPARATOR'), 'download paths are traversal-protected');
check(str_contains($download, 'download_count') && str_contains($download, 'download_count') && str_contains($download, 'getAffectedRows() !== 1'), 'download limits are atomically enforced');

check(strpos($installSql, "0000-00-00") === false, "fresh-install SQL has no zero-date defaults");
check(str_contains(source("modules/mod_sanctuaryshop_cart/mod_sanctuaryshop_cart.xml"), "<folder>src</folder>"), "module manifest includes dispatcher source");
// Install and upgrade integrity.
check(substr_count($installSql, '`shipping_method`') === 1, 'fresh-install schema has one shipping_method column');
check(substr_count($installer, '`shipping_method`') >= 1, 'installer retains idempotent shipping_method migration');
check(str_contains($manifest, '<schemapath type="mysql">sql/updates/mysql</schemapath>'), 'manifest includes Joomla SQL update path');
foreach (['1.0.0', '1.1.0', '1.2.0', '1.3.0', '1.4.0', '1.5.0', '1.6.0', '1.8.0', '1.9.0', '1.9.2', '1.9.3'] as $version) {
    check(is_file($root . '/admin/sql/updates/mysql/' . $version . '.sql'), "upgrade file {$version} exists");
}
check(!str_contains($manifest, 'example.com'), 'manifest has no placeholder release URLs');

printf("\n%d passed, %d failed\n", $passed, $failed);
exit($failed === 0 ? 0 : 1);
