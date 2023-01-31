<?php

namespace App\Orchid\Screens\Players;

use App\Orchid\Filters\IssuedBonusesFilter;
use App\Orchid\Layouts\ViewPlayerBonusInfoTable;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Alert;
use DB;

class Bonuses extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Bonuses';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Bonuses';

    public $id;

    public $permission = [
        'platform.players.bonuses'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
        }

        $bonus_info = \App\Models\BonusIssue::filters()
            ->filtersApply([IssuedBonusesFilter::class])
            ->leftJoin('bonuses', 'bonus_issue.bonus_id', '=', 'bonuses.id')
            ->leftJoin('currency', 'bonus_issue.currency_id', '=', 'currency.id')
            ->where('bonus_issue.user_id', '=', $model->id)
            ->select('bonus_issue.id', DB::Raw('IF(bonuses.title IS NULL, bonus_issue.custom_title, bonuses.title ) as title'), 'bonuses.strategy_id as strategy', 'bonus_issue.amount', 'bonus_issue.locked_amount',
                'currency.code as currency', 'bonus_issue.to_wager', 'bonus_issue.wagered', 'bonus_issue.active_until as expiry_at', 'bonus_issue.stage', 'bonus_issue.status', 'bonus_issue.created_at', 'bonus_issue.cat_type', 'bonus_issue.custom_title as title_frontend')
            ->orderBy('bonus_issue.id', 'DESC')
            ->paginate(100);

        return [
            'bonus_info' => $bonus_info,
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Link::make('Return')
                ->icon('left')
                ->class('btn btn-outline-secondary mb-2')
                ->route('platform.players.profile', $this->id)
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {

        return [
            Layout::table('bonus_info', [
                TD::make('title', 'Title')->sort(),
                TD::make('issued_at', 'Issued at')->render(function ($bonus_info){
                    return $bonus_info->created_at;
                })->sort(),
                TD::make('amount', 'Amount')->sort(),
                TD::make('stage', 'Stage')->render(function ($bonus_info){
                    switch ($bonus_info->stage){
                        case 1:
                            return 'Not activated';
                        case 2:
                            return 'Active';
                        case 3:
                            return 'Wager done';
                        case 4:
                            return 'Lost';
                        default:
                            return '-';
                    }
                })->sort(),
                TD::make('locked_amount', 'Amount locked')->align(TD::ALIGN_CENTER)->sort(),
                TD::make('wager', 'Wager')->render(function ($bonus_info){
                    $percent = ($bonus_info->wagered / $bonus_info->to_wager) * 100;
                    return $bonus_info->wagered . ' / ' . $bonus_info->to_wager .' ('. bcdiv($percent, 1, 0). '%)';
                })->sort(),
                TD::make('expiry_at', 'Expiry date')->sort(),
                TD::make('action', 'Action')->align(TD::ALIGN_CENTER)->render(function ($bonus_info){
                    $params = [];
                    foreach ($bonus_info::STATUS as $idx => $status){
                        if($idx === $bonus_info->status){
                            continue;
                        }
                        $params[] = Button::make(__($status))->class('dropdown-item')
                            ->method('status_state_change')
                            ->confirm(__('Are you sure you want to change status state?'))
                            ->parameters([
                                'status_state_change.id' => $bonus_info->id,
                                'status_state_change.status' => (int)$idx
                            ]);
                    }

                    return DropDown::make($bonus_info::STATUS[$bonus_info->status])
                        ->icon('arrow-down')->class('btn sharp btn-primary tp-btn')
                        ->list($params);
                })->sort(),
            ])
        ];
    }

    public function status_state_change(Request $request)
    {
        $input = $request->all();
        $bonus = \App\Models\BonusIssue::find($input['status_state_change_id']);
        $bonus->status = (int)$input['status_state_change_status'];
        if ((int)$input['status_state_change_status'] === 2) {
            $currency = \App\Models\Currency::find($bonus->currency_id);
            $amount = ($bonus->locked_amount / 2);
            \App\Models\Wallets::query()
                ->where([
                    ['currency_id', '=', $bonus->currency_id],
                    ['user_id', '=', $bonus->user_id]
                ])
                ->update(['balance' => new Expression('balance + ' . ($amount * $currency->rate))]);
            event(new \App\Events\UpdateBalance($bonus->user_id));
        }

        if ((int)$input['status_state_change_status'] === 4) {
            $amount = -1 * ($bonus->locked_amount / 2);

            $this->handler_user_wallet($bonus->user_id, $amount);
            event(new \App\Events\UpdateBalance($bonus->user_id));
        }
        $bonus->save();
        Alert::info('You have successfully.');
    }
}
