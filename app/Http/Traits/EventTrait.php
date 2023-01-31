<?php


namespace App\Http\Traits;


use App\Models\EventTypes;
use App\Models\GroupPlayers;

trait EventTrait
{
    use ConditionTrait;

    public function game_category_modified()
    {
        $event = EventTypes::find(1);
    }

    public function admin_modified()
    {
        $event = EventTypes::find(2);
    }

    public function admin_signed_out()
    {
        $event = EventTypes::find(3);
    }

    public function admin_failed_to_sign_in()
    {
        $event = EventTypes::find(4);
    }

    public function admin_signed_in()
    {
        $event = EventTypes::find(5);
    }

    public function admin_created()
    {
        $event = EventTypes::find(6);
    }

    public function user_signed_in($user)
    {
        $event = EventTypes::find(12);
        foreach ($event->groups as $group) {
            $inGroup = false;
            foreach ($group->filter->filter_conditions as $condition){
                $method = $condition->condition::CONDITIONS[$condition->condition->id];
                if($this->$method($condition, $user)){
                    $inGroup = true;
                    continue;
                };
                $inGroup = false;
                break;
            }
           $this->check_group($inGroup, $user, $group);
        }
    }

    public function deposit_made($user)
    {
        $event = EventTypes::find(20);
        foreach ($event->groups as $group) {
            $inGroup = false;
            foreach ($group->filter->filter_conditions as $condition){
                $method = $condition->condition::CONDITIONS[$condition->condition->id];
                if($this->$method($condition, $user, $group)){
                    $inGroup = true;
                    continue;
                };
                $inGroup = false;
                break;
            }
            $this->check_group($inGroup, $user, $group);
        }
    }

    private function check_group($inGroup, $user, $group){
        if($inGroup){
            GroupPlayers::firstOrCreate(['user_id' => $user->id, 'group_id' => $group->id]);
        }else{
            GroupPlayers::where(['user_id' => $user->id, 'group_id' => $group->id])->delete();
        }
    }

}
