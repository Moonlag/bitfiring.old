<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\EmailTrait;
use Auth;
use Mail;

class EmailController extends Controller
{

    use EmailTrait;
    public function index()
    {
        $user = Auth::guard('clients')->user();

        $this->deposit_received_email('miboxmyhome@gmail.com', 851);;
        return view('emails.main', ['body' => $this->get_template('cashout_success', ['email' => $user->email])]);
    }

    public function template_body($template, $data): string
    {
        $placeholders = array_map(function ($placeholder) {
            return "@$placeholder@";
        }, array_keys($data));

        return strtr($template, array_combine($placeholders, $data));
    }
}
