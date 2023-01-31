<?php


namespace App\Traits;
use App\Models\Changes;

trait ChangesTrait
{
    public $user_id;

    public function prepare($user_id){
        $this->user_id = $user_id;
    }

    public function insert_changes($data){
        Changes::query()->insert($data);
    }
}
