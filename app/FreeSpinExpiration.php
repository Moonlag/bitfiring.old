<?php


namespace App;

use App\Http\Traits\FreeSpinExpirationTrait;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use phpbb\console\command\config\get;

class FreeSpinExpiration
{
    use FreeSpinExpirationTrait;

    public function __invoke()
    {
        $expire_bonus = $this->get_freespin_issue();

        foreach ($expire_bonus as $bonus) {
            $this->update_freespin_issue($bonus->id, 2);
        }

    }


}
