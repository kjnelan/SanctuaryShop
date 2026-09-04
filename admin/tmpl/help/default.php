<?php defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="com-sanctuaryshop-help">
    <div class="accordion" id="helpAccordion">

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#help-getting-started">
                    Getting Started
                </button>
            </h2>
            <div id="help-getting-started" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                <div class="accordion-body">
                    <ol>
                        <li><strong>Configure the shop:</strong> Click <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=settings'); ?>">Settings</a> in the submenu to set your shop name, currency, payment provider credentials, shipping rates, and download settings.</li>
                        <li><strong>Create your first product:</strong> Go to Products → New. Choose a product type (Physical, Digital, Service, or Subscription), enter a title, price, and description. For digital products, add downloadable files. Save the product.</li>
                        <li><strong>Set up a menu item:</strong> In Joomla's menu manager, create a new menu item. Select SanctuaryShop as the component. You can link to the Products list view, a single Product view, or the Cart/Checkout views.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-product-types">
                    Product Types
                </button>
            </h2>
            <div id="help-product-types" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body">
                    <dl>
                        <dt>Physical (Shipped)</dt>
                        <dd>A tangible item that gets shipped to the customer. Stock is tracked and decremented on purchase. Shipping rates apply. Shows an "Add to Cart" button and a "Buy Now" button.</dd>
                        <dt>Digital Download</dt>
                        <dd>A file the customer downloads after purchase. You attach files (relative paths from your Download Files Path). After checkout, secure download tokens are generated automatically. No shipping cost is added. Shows a "Buy Now" button that skips the cart.</dd>
                        <dt>Service / Appointment</dt>
                        <dd>A non-shipping service product. SanctuaryShop does not provide appointment scheduling. No physical delivery, no files. Stock can track available slots.</dd>
                        <dt>Subscription / Membership</dt>
                        <dd>A subscription product supported through the configured payment provider where applicable. It does not provide provider-independent membership access or automatic file delivery.</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-buy-now">
                    Setting Up a Single Buy Now Page
                </button>
            </h2>
            <div id="help-buy-now" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body">
                    <p>This is the fastest way to sell a single item (e.g., an ebook, a course, or a one-time service).</p>
                    <ol>
                        <li>Go to <strong>Products → New</strong>. Set the product type to <em>Digital</em> (or Service). Fill in the title, description, and price. Add downloadable files if it's a digital product. Set State to Published. Save.</li>
                        <li>Note the product's ID from the URL after saving (e.g., <code>&amp;id=5</code>).</li>
                        <li>In Joomla's menu manager, create a new menu item. Set the type to <strong>SanctuaryShop → Product View</strong>. Set the <em>Product ID</em> parameter to the ID you noted. Give the menu item a descriptive alias (e.g., <code>my-ebook</code>).</li>
                        <li>Publish the menu item. The resulting page shows your product with a <strong>Buy Now</strong> button that goes directly to checkout — bypassing the cart page entirely.</li>
                        <li>If the product is physical, the page shows both <strong>Add to Cart</strong> and <strong>Buy Now</strong> buttons.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-catalog">
                    Setting Up a Product Catalog
                </button>
            </h2>
            <div id="help-catalog" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body">
                    <ol>
                        <li>Create <strong>Categories</strong> in the submenu (uses Joomla's built-in category system with extension <code>com_sanctuaryshop</code>).</li>
                        <li>Create <strong>Products</strong> and assign each to a category.</li>
                        <li>In Joomla's menu manager, create a menu item with type <strong>SanctuaryShop → Products List</strong>. Optionally pre-filter by category or type.</li>
                        <li>Customers can browse, filter by category, filter by product type, and sort by price or title.</li>
                        <li>Use <strong>Product Options/Variants</strong> (Size, Color, Format, etc.) to offer variations of a product without creating separate products. Edit a product and use the Variants section.</li>
                    </ol>
                </div>
            </div>
        </div>


        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-dashboard">Dashboard &amp; Reports</button></h2>
            <div id="help-dashboard" class="accordion-collapse collapse" data-bs-parent="#helpAccordion"><div class="accordion-body">
                <p>The <strong>Dashboard</strong> is the first SanctuaryShop submenu item. It shows revenue, order counts, pending orders, active products, recent orders, top products, and low-stock alerts.</p>
                <p>Use <strong>Reports</strong> for a date-range report. You can filter by order status and review totals, orders by status, sales by payment provider, and top completed products. The Orders page also provides a detailed CSV export.</p>
            </div></div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-receipts">Receipts &amp; confirmations</button></h2>
            <div id="help-receipts" class="accordion-collapse collapse" data-bs-parent="#helpAccordion"><div class="accordion-body">
                <p>After successful payment, the customer may receive an order-confirmation email if customer confirmations are enabled. The email contains the order details and any download links.</p>
                <p>The customers order page includes <strong>Print receipt</strong>. Receipt title, footer, logo URL, and accent color can be configured in Settings. The printed receipt is a presentation of the order record; it is not a separate accounting or tax-invoice system.</p>
            </div></div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-downloads">
                    Digital Downloads
                </button>
            </h2>
            <div id="help-downloads" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body">
                    <p><strong>How files are stored:</strong> Set the <em>Download Files Path</em> in Settings to an absolute server path (e.g., <code>/var/www/html/protected-files</code>). Files stored there are never directly accessible via URL — they are served only through the secure token download system.</p>
                    <p><strong>Token system:</strong> After a successful payment, the system automatically generates a unique download token for each file in the order. Each token is a 64-character hex string that cannot be guessed.</p>
                    <p><strong>Expiry &amp; limits:</strong> Set <em>Link Expiry (days)</em> to automatically expire tokens (0 = never). Set <em>Max Downloads Per Link</em> to limit how many times each file can be downloaded (default 5).</p>
                    <p><strong>Managing tokens:</strong> Go to the <strong>Download Tokens</strong> view to see all active tokens. You can revoke tokens individually if needed.</p>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-coupons">
                    Coupons
                </button>
            </h2>
            <div id="help-coupons" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body">
                    <p>Go to <strong>Coupons</strong> in the submenu to manage discount codes.</p>
                    <ul>
                        <li><strong>Code:</strong> The coupon code customers enter at checkout (automatically uppercased).</li>
                        <li><strong>Type:</strong> Percentage (e.g., 10%) or Fixed Amount (e.g., $5.00 off).</li>
                        <li><strong>Minimum Subtotal:</strong> Require a minimum cart total before the coupon applies (0 = no minimum).</li>
                        <li><strong>Usage Limit:</strong> Maximum number of times the coupon can be used in total (0 = unlimited).</li>
                        <li><strong>Expires:</strong> Optional expiry date/time after which the coupon becomes invalid.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-orders">
                    Order Management
                </button>
            </h2>
            <div id="help-orders" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body">
                    <p><strong>Status workflow:</strong></p>
                    <ul>
                        <li><strong>Pending</strong> — Order created, payment not yet confirmed.</li>
                        <li><strong>Processing</strong> — Payment confirmed; you are preparing the order.</li>
                        <li><strong>Shipped</strong> — Physical order dispatched. Add a tracking number in the sidebar.</li>
                        <li><strong>Completed</strong> — Order fulfilled.</li>
                        <li><strong>Cancelled</strong> — Order cancelled.</li>
                        <li><strong>Refunded</strong> — Payment refunded; automatic refunds are currently available only for eligible Square orders. Manage other providers through their dashboards.</li>
                    </ul>
                    <p>Use the quick-action buttons (<em>Mark Processing, Mark Shipped, Mark Completed, Mark Cancelled</em>) in the order sidebar for fast status updates. Enter a tracking number when marking as shipped.</p>
                    <p>Add internal <strong>notes</strong> to any order — these are not visible to customers.</p>
                </div>
            </div>
        </div>


        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-stripe">Stripe Setup</button></h2>
            <div id="help-stripe" class="accordion-collapse collapse" data-bs-parent="#helpAccordion"><div class="accordion-body">
                <ol><li>Choose <strong>Stripe</strong> in Settings � Payment.</li><li>Enter the publishable key and secret key from your Stripe account. Use test keys while testing.</li><li>Stripe PaymentIntents are confirmed during checkout and Stripe can send its own receipt email when configured in Stripe.</li></ol>
                <p>The current extension completes Stripe payments through checkout. Stripe webhook processing, automatic Stripe refunds, and subscription lifecycle synchronization are not included yet.</p>
            </div></div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-square">
                    Square Setup
                </button>
            </h2>
            <div id="help-square" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body">
                    <ol>
                        <li>Log in to <a href="https://developer.squareup.com" target="_blank" rel="noopener">developer.squareup.com</a> and create an application.</li>
                        <li><strong>Sandbox (Testing):</strong> Use the Sandbox environment during development. Your sandbox Application ID starts with <code>sandbox-sq0idb-</code>. Your sandbox Access Token is found in the Sandbox section of your application.</li>
                        <li><strong>Production (Live):</strong> Switch to Production in <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=settings'); ?>">Settings → Payment</a> tab. Your Production credentials start with <code>sq0idp-</code>.</li>
                        <li><strong>Location ID:</strong> Found in your Square dashboard under <em>Locations</em>.</li>
                        <li><strong>Webhook Signature Key:</strong> Optional — used to verify webhook payloads from Square. Configure a webhook endpoint in your Square app pointing to <code>index.php?option=com_sanctuaryshop&task=webhook.square</code>.</li>
                    </ol>
                    <div class="alert alert-warning mt-3">
                        <strong>Important:</strong> Never commit production Square credentials to version control. Always use Settings to store them.
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help-module">
                    Mini-Cart Module
                </button>
            </h2>
            <div id="help-module" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body">
                    <p>SanctuaryShop includes a <strong>mod_sanctuaryshop_cart</strong> module that displays a cart icon with item count and a dropdown summary in your site's header or wherever you place it.</p>
                    <p>To install: go to <strong>System → Install → Extensions</strong> and upload the <code>mod_sanctuaryshop_cart</code> folder as a ZIP, or install it from the <code>modules/</code> directory in the component package.</p>
                    <p>After installation, assign the module to a module position in <strong>Content → Site Modules</strong>. It automatically reads the cart from the current session — no configuration needed.</p>
                </div>
            </div>
        </div>

    </div>
</div>
