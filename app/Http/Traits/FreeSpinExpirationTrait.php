<?php


namespace App\Http\Traits;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait FreeSpinExpirationTrait
{
    public function get_freespin_issue($now){
        return DB::table('freespin_issue')
            ->select('freespin_issue.id')
            ->where([['freespin_issue.active_until', '<', $now], ['freespin_issue.status', '=', 1]])
            ->get();
    }

    public function update_freespin_issue($id, $status, $now){
        DB::table('freespin_issue')->where('id', $id)->update(['status' => $status, 'expiry_at' => $now]);
    }
}
