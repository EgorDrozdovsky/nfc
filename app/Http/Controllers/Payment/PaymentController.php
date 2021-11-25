<?php

namespace App\Http\Controllers\Payment;

use App\Gateway;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Redirect;

class PaymentController extends Controller
{
    public function preparePaymentGateway(Request $request, $planId)
    {
 
        $payment_mode = Gateway::where('payment_gateway_id', $request->payment_gateway_id)->first();

        if ($payment_mode == null) {
            alert()->error('Please choose valid payment method!');
            return redirect()->back();
        } else {
            if ($payment_mode->payment_gateway_name == "Paypal") {
                return redirect()->route('paywithpaypal', $planId);
            } else if ($payment_mode->payment_gateway_name == "Razorpay") {
                return redirect()->route('paywithrazorpay', $planId);
            } else if ($payment_mode->payment_gateway_name == "Stripe") {
                return redirect()->route('paywithstripe', $planId);
            } else if ($payment_mode->payment_gateway_name == "Bank Transfer") {
                return redirect()->route('paywithoffline', $planId);
            } else {
                alert()->error('Something went wrong!');
                return redirect()->back();
            }
        }
    }
}
