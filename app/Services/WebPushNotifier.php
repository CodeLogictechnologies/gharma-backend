<?php

namespace App\Services;

use App\Models\BackPanel\Inventory;
use App\Models\BackPanel\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Exception;

class WebPushNotifier
{
    public static function notifyLowStock(string $orgid): void
    {
        try {
            $alerts = Inventory::lowStockAlerts($orgid);

            if ($alerts->isEmpty()) {
                return;
            }

            $subscriptions = PushSubscription::where('orgid', $orgid)->get();

            if ($subscriptions->isEmpty()) {
                return;
            }

            $count = $alerts->count();
            $first = $alerts->first();
            $body  = $count === 1
                ? "{$first->title} ({$first->attribute}: {$first->variation_value}) is low on stock."
                : "{$first->title} and " . ($count - 1) . " other item(s) are low on stock.";

            $payload = json_encode([
                'title' => 'Low Stock Alert',
                'body'  => $body,
                'count' => $count,
                'url'   => route('inventory'),
            ]);

            $webPush = new WebPush([
                'VAPID' => [
                    'subject'    => config('services.webpush.subject'),
                    'publicKey'  => config('services.webpush.public_key'),
                    'privateKey' => config('services.webpush.private_key'),
                ],
            ]);

            foreach ($subscriptions as $sub) {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $sub->endpoint,
                        'keys'     => [
                            'p256dh' => $sub->public_key,
                            'auth'   => $sub->auth_token,
                        ],
                    ]),
                    $payload
                );
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                    \Log::warning('[webpush] subscription expired, removed', ['endpoint' => $report->getEndpoint()]);
                } elseif (!$report->isSuccess()) {
                    \Log::warning('[webpush] send failed', [
                        'endpoint' => $report->getEndpoint(),
                        'reason'   => $report->getReason(),
                    ]);
                }
            }
        } catch (Exception $e) {
            report($e);
        }
    }
}
