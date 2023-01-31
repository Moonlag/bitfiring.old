<?php


namespace App\Http\Traits;


use Illuminate\Support\Facades\Http;

trait CryptoPaymentApi
{

    public function csrf_auth()
    {
        Http::cryptopayment()->get('sanctum/csrf-cookie');
    }

    public function get_token($email, $password, $device_name)
    {
        return Http::cryptopayment()
            ->post('api/sanctum/token', [
                'email' => $email,
                'password' => $password,
                'device_name' => $device_name
            ])->json();
    }

    public function transfer_usdtTRC20($token, $amount, $to, $payment_id)
    {
        return Http::cryptopayment()
            ->withToken($token)
            ->post('api/usdt-trc20/transfer', ['amount' => $amount, 'to' => $to, 'payment_id' => $payment_id])
            ->json();
    }

    public function transfer_usdtERC20($token, $amount, $to, $payment_id)
    {
        return Http::cryptopayment()
            ->withToken($token)
            ->post('api/usdt-erc20/transfer', ['amount' => $amount, 'to' => $to, 'payment_id' => $payment_id])
            ->json();
    }

    public function transfer_ETH($token, $amount, $to, $payment_id)
    {
        return Http::cryptopayment()
            ->withToken($token)
            ->post('api/eth/transfer', ['amount' => $amount, 'to' => $to, 'payment_id' => $payment_id])
            ->json();
    }

    public function transfer_BTC($token, $amount, $to, $payment_id)
    {
        return Http::cryptopayment()
            ->withToken($token)
            ->post('api/btc/transfer', ['amount' => $amount, 'to' => $to, 'payment_id' => $payment_id])
            ->json();
    }

    public function transfer_DOGE($token, $amount, $to, $payment_id)
    {
        return Http::cryptopayment()
            ->withToken($token)
            ->post('api/doge/transfer', ['amount' => $amount, 'to' => $to, 'payment_id' => $payment_id])
            ->json();
    }

    public function balance($token)
    {
        return Http::cryptopayment()
            ->withToken($token)
            ->post('api/balance')
            ->json();
    }

    public function status($token)
    {
        return Http::cryptopayment()
            ->withToken($token)
            ->post('api/status')
            ->json();
    }
}
