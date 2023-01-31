<?php


namespace App\Http\Traits;


use App\Models\Payments;
use App\Models\Sessions;
use App\Models\Suspicions;
use Carbon\Carbon;

trait ConditionTrait
{
    public function user_duplicate($condition, $user)
    {
        if(request()->ip() === '31.129.87.21' || request()->ip() === '178.178.80.186'){
            return false;
        }
        $value = (boolean)$condition->value1;
        $sessions = $user->sessions;
        if ((boolean)$user->duplicated === $value) {
            return true;
        }

        $now = Carbon::now();
        $start = $now->copy()->subHour();
        $end = $now->copy();
        foreach ($sessions as $session) {
            $suspicions = Sessions::query()
                ->where([['user_id', '!=', $user->id], ['ip', '=', $session->ip]])
                ->orWhere([['user_id', '!=', $user->id], ['user_agent', '=', $session->user_agent], ['platform', '=', $session->platform], ['viewport', '=', $session->viewport]])
                ->whereBetween('created_at', [$start, $end])
                ->get();
            if ($value === !!$suspicions->count()) {
                $user->duplicated = $value;
                $user->suspicious = $value;
                $user->save();
                if ($value) {
                    Suspicions::firstOrCreate(['user_id' => $user->id, 'reason_id' => 12, 'active' => 1]);
                    foreach ($suspicions as $suspicion){
                        Suspicions::firstOrCreate(['user_id' => $suspicion->user_id, 'reason_id' => 12, 'active' => 1]);
                    }
                }
                return true;
            };
        }

        return false;
    }

    public function deposit_payment_systems($condition, $user, $groups)
    {
        $payments = Payments::query()
            ->where('user_id', '=', $user->id)
            ->where('status', '=', 1)
            ->get();

        if($groups->id === 19 && !$payments->count()){
            return true;
        }

        if($groups->id === 20 && $payments->count()){
            return true;
        }

        return false;
    }
}
