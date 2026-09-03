<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use SanctuaryShop\Component\Sanctuaryshop\Site\Model\CheckoutModel;

class WebhookController extends BaseController
{
    public function square(): void
    {
        $app = Factory::getApplication();
        $raw = file_get_contents('php://input') ?: '';
        $params = ComponentHelper::getParams('com_sanctuaryshop');
        $key = (string) $params->get('square_webhook_signature_key', '');
        $url = (string) $params->get('square_webhook_url', '');
        if ($url === '') {
            $url = $app->get('live_site') . '/index.php?option=com_sanctuaryshop&task=webhook.square';
        }
        $signature = $_SERVER['HTTP_X_SQUARE_HMACSHA256_SIGNATURE'] ?? '';
        $expected = $key ? base64_encode(hash_hmac('sha256', $url . $raw, $key, true)) : '';
        if (!$key || !$signature || !hash_equals($expected, $signature)) {
            http_response_code(401); echo json_encode(['error' => 'Invalid signature']); $app->close();
        }

        $event = json_decode($raw, true);
        $eventId = (string) ($event['event_id'] ?? '');
        if ($eventId === '') {
            http_response_code(400); echo json_encode(['error' => 'Missing event_id']); $app->close();
        }
        $checkout = new CheckoutModel;
        $db = $checkout->getDatabase();
        $db->setQuery('INSERT IGNORE INTO ' . $db->quoteName('#__sanctuaryshop_webhook_events') . ' (event_id, event_type, payload, received) VALUES (' . $db->quote($eventId) . ', ' . $db->quote((string) ($event['type'] ?? '')) . ', ' . $db->quote($raw) . ', ' . $db->quote(Factory::getDate()->toSql()) . ')')->execute();
        if ($db->getAffectedRows() === 0) {
            echo json_encode(['received' => true, 'duplicate' => true]); $app->close();
        }

        $object = $event['data']['object'] ?? [];
        $subscription = $object['subscription'] ?? null;
        if (is_array($subscription) && !empty($subscription['id'])) {
            $db->setQuery(
                $db->getQuery(true)->update($db->quoteName('#__sanctuaryshop_orders'))
                    ->set('square_subscription_status = ' . $db->quote(strtoupper((string) ($subscription['status'] ?? ''))))
                    ->where('square_subscription_id = ' . $db->quote((string) $subscription['id']))
            )->execute();
        }
        $payment = $object['payment'] ?? $object;
        $paymentId = (string) ($payment['id'] ?? '');
        $status = strtoupper((string) ($payment['status'] ?? ''));
        if ($paymentId && in_array($status, ['COMPLETED', 'APPROVED'], true)) {
            $referenceId = (string) ($payment['reference_id'] ?? '');
            $squareOrderId = (string) ($payment['order_id'] ?? '');
            $conditions = [];
            if ($paymentId !== '') {
                $conditions[] = 'payment_id = ' . $db->quote($paymentId);
            }
            if (ctype_digit($referenceId)) {
                $conditions[] = 'id = ' . (int) $referenceId;
            }
            if ($squareOrderId !== '') {
                $conditions[] = 'square_order_id = ' . $db->quote($squareOrderId);
            }
            $order = $conditions
                ? $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__sanctuaryshop_orders'))->where(implode(' OR ', $conditions)))->loadObject()
                : null;
            if ($order && $order->status === 'pending') {
                $checkout->finalizePaidOrder((int) $order->id, $paymentId, $squareOrderId ?: null, $order);
            }
        }
        echo json_encode(['received' => true]);
        $app->close();
    }
}
