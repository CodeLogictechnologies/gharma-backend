<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    public function vapidPublicKey()
    {
        return response()->json([
            'publicKey' => config('services.webpush.public_key'),
        ]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint'         => 'required|string',
            'keys.p256dh'      => 'required|string',
            'keys.auth'        => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $request->input('endpoint')],
            [
                'user_id'    => Auth::id(),
                'orgid'      => session('orgid'),
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
            ]
        );

        return response()->json(['type' => 'success', 'message' => 'Subscribed.']);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['endpoint' => 'required|string']);

        PushSubscription::where('endpoint', $request->input('endpoint'))->delete();

        return response()->json(['type' => 'success', 'message' => 'Unsubscribed.']);
    }
}
