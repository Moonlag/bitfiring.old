<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payments;
use Illuminate\Http\Request;

class CryptoApiCallback extends Controller
{
    public function payment_withdrawal(Request $request)
    {
        if($request->ip() === '95.179.149.172'){
            $input = $request->all();
            $payment = Payments::find($input['payment_id']);
            $payment->status = 1;
            $payment->source_transaction = $input['hash'];
            $payment->save();
        }
    }

    public function payment_withdrawal_reject(Request $request)
    {
        if($request->ip() === '95.179.149.172'){
            $input = $request->all();
            $payment = Payments::find($input['payment_id']);
            $payment->status = 2;
            $payment->save();
        }
    }
}
