<?php

namespace App\Http\Controllers\Api;

use App\Model\BuyCoinHistory;
use App\Model\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use App\Http\Controllers\Controller;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');


        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handleSuccessfulPayment($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handleFailedPayment($event->data->object);
                break;
        }

        return response()->json(['success' => true]);
    }

    private function handleSuccessfulPayment($paymentIntent)
    {
        try {
            // Find the transaction by stripe token
            $transaction = BuyCoinHistory::where('stripe_token', $paymentIntent->id)
                ->where('status', '!=', STATUS_ACCEPTED)
                ->first();

            if ($transaction) {
                // Add coins to user's wallet
                $wallet = get_primary_wallet($transaction->user_id, DEFAULT_COIN_TYPE);
                $wallet->increment('balance', $transaction->coin);
                // Update transaction status
                $transaction->status = STATUS_ACCEPTED;
                $transaction->save();
                sendBuyCoinEmail('transactions.coin-order-accept',$transaction);

            }
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
        }
    }

    private function handleFailedPayment($paymentIntent)
    {
        try {
            $transaction = BuyCoinHistory::where('stripe_token', $paymentIntent->id)
                ->where('status', '!=', STATUS_REJECTED)
                ->first();

            if ($transaction) {
                $transaction->status = STATUS_REJECTED;
                $transaction->save();
                sendBuyCoinEmail('transactions.coin-order-reject',$transaction);
            }
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
        }
    }
}