<?php

namespace App\Http\Controllers\User;

use App\Plan;
use App\User;
use App\Setting;
use App\Currency;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    public function indexTransactions()
    {
        $active_plan = Plan::where('plan_id', Auth::user()->plan_id)->first();
        $plan = User::where('user_id', Auth::user()->user_id)->first();

        if($active_plan != null) {
            $transactions = Transaction::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->get();
            $settings = Setting::where('status', 1)->first();
            $currencies = Currency::get();

            return view('user.transactions.index', compact('transactions', 'settings', 'currencies'));
        } else {
            if($plan->billing_name != null) {
                return redirect()->route('user.plans');
            } else {
                return redirect()->route('user.billing');
            }
        }
    }

    public function viewInvoice($id) {
        $transaction = Transaction::where('gobiz_transaction_id', $id)->first();
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();
        $currencies = Currency::get();
        $transaction['billing_details'] = json_decode($transaction['invoice_details'], true);
        return view('user.transactions.view-invoice', compact('transaction', 'settings', 'config', 'currencies'));
    }

    public function billing() {
        $user = User::where('user_id', Auth::user()->user_id)->first();
        $settings = Setting::first();

        return view('user.billing.index', compact('user', 'settings'));
    }

    public function updateBilling(Request $request) {
        $validator = Validator::make($request->all(), [
            'billing_name' => 'required',
            'billing_email' => 'required',
            'billing_phone' => 'required',
            'billing_address' => 'required',
            'billing_city' => 'required',
            'billing_state' => 'required',
            'billing_zipcode' => 'required',
            'billing_country' => 'required',
            'type' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->with('errors', $validator->messages()->all()[0])->withInput();
        }

        User::where('user_id', Auth::user()->user_id)->update([
            'billing_name' => $request->billing_name,
            'billing_email' => $request->billing_email,
            'billing_phone' => $request->billing_phone,
            'billing_address' => $request->billing_address,
            'billing_city' => $request->billing_city,
            'billing_state' => $request->billing_state,
            'billing_zipcode' => $request->billing_zipcode,
            'billing_country' => $request->billing_country,
            'type' => $request->type,
            'vat_number' => $request->vat_number
        ]);

        return redirect()->route('user.plans')->with('success', 'Billing Details Updated Successfully!');
    }
}
