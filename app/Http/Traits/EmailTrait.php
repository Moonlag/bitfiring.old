<?php

namespace App\Http\Traits;

use App\Models\MailTextTranslations;
use App\Models\MailStatistics;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

trait EmailTrait
{
    public $admin = ['cashout_request_admin'];

    protected function get_template($mail, $data)
    {
        $mailTemplate = \App\Models\MailTemplateTranslations::find($mail->template_id);

        $body = ['content' => $this->template_body($mail->body, $data)];

        return [$this->template_body($mailTemplate->description, $body), $mail->subject];
    }


    protected function template_body($template, $data): string
    {
        $placeholders = array_map(function ($placeholder) {
            return "@$placeholder@";
        }, array_keys($data));

        return strtr($template, array_combine($placeholders, $data));
    }

    public function deposit_received_email($email, $payment_id = false)
    {
        $payment = \App\Models\Payments::find($payment_id);
        if (isset($payment->id)) {
            $body = $this->get_template('deposit_received', ['email' => $email, 'amount' => $payment->amount . ' ' . $payment->payment_system->name]);
            if (!$body) {
                return;
            }
            Mail::to($email)->send(new \App\Mail\Main(...$body));
        }
    }

    public function cashout_requested_email($email, $payment_id = false)
    {
        $payment = \App\Models\Payments::find($payment_id);
        if (isset($payment->id)) {
            $body = $this->get_template('cashout_requested', ['email' => $email, 'player' => $email, 'amount' => abs($payment->amount) . ' ' . $payment->payment_system->name]);
            if (!$body) {
                return;
            }
            Mail::to($email)->send(new \App\Mail\Main(...$body));
        }
    }

    public function cashout_requested_admin_email($email, $payment_id = false, $player = '')
    {
        $payment = \App\Models\Payments::find($payment_id);
        if (isset($payment->id)) {
            $body = $this->get_template('cashout_request_admin', ['email' => $email, 'player' => $player, 'amount' => abs($payment->amount) . ' ' . $payment->payment_system->name]);
            Mail::to($email)->send(new \App\Mail\Main(...$body));
        }
    }

    public function verify_player_email($email, $token)
    {
        $body = $this->get_template('verify_player', ['email' => $email, 'token' => $token]);
        if (!$body) {
            return;
        }
        return Mail::to($email)->send(new \App\Mail\Main(...$body));
    }

    public function bonus_received_email($email, $bonus_id = false)
    {
        $bonus = \App\Models\BonusIssue::find($bonus_id);
        if (isset($bonus->id)) {
            $body = $this->get_template('bonus_received', ['email' => $email]);
            if (!$body) {
                return;
            }
            Mail::to($email)->send(new \App\Mail\Main(...$body));
        }
    }

    public function deposit_issue_email($email, $payment_id = false)
    {
        $payment = \App\Models\Payments::find($payment_id);
        if (isset($payment->id)) {
            $body = $this->get_template('deposit_issue', ['email' => $email]);
            if (!$body) {
                return;
            }
            Mail::to($email)->send(new \App\Mail\Main(...$body));
        }
    }

    public function freespins_promo_email($email, $count = 0)
    {
        $body = $this->get_template('freespins_promo', ['email' => $email, 'count' => $count]);
        if (!$body) {
            return;
        }
        Mail::to($email)->send(new \App\Mail\Main(...$body));
    }

    public function reset_password_email($email, $link)
    {
        return $this->get_template('reset_password', ['email' => $email, 'link' => $link]);
    }

    public function cashout_success_email($email)
    {
        $body = $this->get_template('cashout_success', ['email' => $email]);
        if (!$body) {
            return;
        }
        Mail::to($email)->send(new \App\Mail\Main(...$body));
    }

    public function cashout_canceled_email($email)
    {
        $body = $this->get_template('cashout_canceled', ['email' => $email]);
        if (!$body) {
            return;
        }
        Mail::to($email)->send(new \App\Mail\Main(...$body));
    }

    public function warn_attempt_email($email, $now, $device)
    {
        $body = $this->get_template('warn_attempt', ['email' => $email, 'now' => $now, 'device' => $device]);
        if (!$body) {
            return;
        }
        Mail::to($email)->send(new \App\Mail\Main(...$body));
    }

    public function password_changed_email($email)
    {
        $body = $this->get_template('password_changed', ['email' => $email]);
        if (!$body) {
            return;
        }
        Mail::to($email)->send(new \App\Mail\Main(...$body));
    }

    public function deposit_more_email($email, $id, $amount)
    {
        $body = $this->get_template('deposit_more', ['email' => $email, 'id' => $id, 'amount' => $amount]);
        if (!$body) {
            return;
        }
        Mail::to('magistriam@gmail.com')->send(new \App\Mail\Main(...$body));
    }

    public function test_send($email, $template_name)
    {
        Mail::to($email)->send(new \App\Mail\Main(...$this->get_template($template_name, ['email' => $email])));
    }

    public function send_email($code, $email, $args = [], $tester = false)
    {

        $mail = MailTextTranslations::filters()
            ->select('mail_text_translations.description as body', 'mail_text_translations.title as subject', 'mail_text_translations.template_id', 'mail_text_translations.id')
            ->where('code', '=', $code)
            ->first();


        if (!isset($mail->id)) {
            return;
        }

        $player = \App\Models\Players::query()->where('email', '=', $email)->first();

        try {
            if (isset($player->id) && $player->mail_real || $tester) {
                Mail::to($email)->send(new \App\Mail\Main(...$this->get_template($mail, $args)));
            }


            if (!$tester) {
                $mail_statistic = new MailStatistics();
                $mail_statistic->email = $email;
                $mail_statistic->mail_text_id = $mail->id;
                $mail_statistic->status = $player->mail_real;
                $mail_statistic->save();
            }


        } catch (\Exception $exception) {

            if (!$tester) {
                $mail_statistic = new MailStatistics();
                $mail_statistic->email = $email;
                $mail_statistic->mail_text_id = $mail->id;
                $mail_statistic->status = 3;
                $mail_statistic->save();
            }
        }
    }
}
