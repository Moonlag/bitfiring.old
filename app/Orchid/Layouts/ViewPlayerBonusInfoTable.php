<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ViewPlayerBonusInfoTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'bonus_info';

    protected $title = 'Bonus info';
    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('No data found');
    }


    /**
     * @return string
     */
    protected function iconNotFound(): string
    {
        return 'table';
    }

    /**
     * Enable a hover state on table rows.
     *
     * @return bool
     */
    protected function hoverable(): bool
    {
        return true;
    }

    protected function striped(): bool
    {
        return true;
    }

    protected function action()
    {
        return [
            ModalToggle::make('Issue Personal Bonus')
                ->modal('Issue Personal Bonus')
                ->method('action_issue')
                ->icon('full-screen')->class('btn btn-rounded btn-outline-primary btn-sm me-3'),
            ModalToggle::make('Issue Bonus')
                ->modal('Issue Bonus')
                ->method('action_bonus')
                ->icon('full-screen')->class('btn btn-rounded btn-outline-primary btn-sm me-3'),
            Link::make('All Bonuses')
                ->route('platform.players.bonuses', $this->query->get('player')->id)
                ->icon('link')->class('btn btn-rounded btn-outline-primary btn-sm'),
        ];
    }

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): array
    {
        return [
                TD::make('title_frontend', 'Title')->align(TD::ALIGN_CENTER)->render(function ($bonus_info){
                    if($bonus_info->cat_type === 2 && $bonus_info->title !== $bonus_info->title_frontend){
                        return "<span>" . $bonus_info->title . '</span></br>' . "($bonus_info->title_frontend)" ?? '-';
                    }
                    return $bonus_info->title ?? '-';
                })->sort(),
                TD::make('issued_at', 'Issued at')->align(TD::ALIGN_CENTER)->render(function ($bonus_info){
                    return $bonus_info->created_at;
                })->sort(),
                TD::make('amount', 'Amount')->align(TD::ALIGN_CENTER)->sort(),
                TD::make('stage', 'Stage')->align(TD::ALIGN_CENTER)->render(function ($bonus_info){
                    switch ($bonus_info->status){
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
                TD::make('wager', 'Wager')->align(TD::ALIGN_CENTER)->render(function ($bonus_info){
                    $percent = ($bonus_info->wagered / $bonus_info->to_wager) * 100;
                    return $bonus_info->wagered . ' / ' . $bonus_info->to_wager .' ('. bcdiv($percent, 1, 0). '%)';
                })->sort(),
                TD::make('currency', 'Currency')->align(TD::ALIGN_CENTER)->render(function ($bonus_info){
                    return $bonus_info->currency ?? '-';
                })
                    ->sort(),
                TD::make('expiry_at', 'Expiry date')->align(TD::ALIGN_CENTER)->render(function ($bonus_info){
                    return $bonus_info->expiry_at ?? '-';
                })->sort(),
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
        ];
    }

}
