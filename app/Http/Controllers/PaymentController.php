<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index()
    {
        return view('pricing');
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'credits' => 'required|integer|min:1',
        ]);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $orderData = [
            'receipt'         => 'rcpt_' . uniqid(),
            'amount'          => $request->amount * 100, // Amount in paise
            'currency'        => 'INR',
            'payment_capture' => 1
        ];

        try {
            $razorpayOrder = $api->order->create($orderData);

            return response()->json([
                'order_id' => $razorpayOrder['id'],
                'amount' => $request->amount * 100,
                'key' => config('services.razorpay.key'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function verify(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
            'amount' => 'required',
            'credits' => 'required'
        ]);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        try {
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            $api->utility->verifyPaymentSignature($attributes);

            $user = auth()->user();

            // Check for duplicate transaction ID if necessary, here we assume unique flow

            $user->increment('credits', $request->credits);

            Transaction::create([
                'user_id' => $user->id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'amount' => $request->amount,
                'credits_purchased' => $request->credits,
                'status' => 'success'
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Razorpay Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment verification failed'], 400);
        }
    }
}
