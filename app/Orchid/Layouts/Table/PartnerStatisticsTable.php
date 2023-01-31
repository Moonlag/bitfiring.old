<?php

namespace App\Orchid\Layouts\Table;

use App\Models\Campaign;
use App\Models\Partner;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Illuminate\Http\Request;

class PartnerStatisticsTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'partner';
    public $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request ?? request();
    }

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    public function columns(): array
    {

        $TD = [
            TD::make('partner', 'Partner')->render(function ($target){
                return Link::make($target->partner)
                    ->parameters(['partner_id' => $target->id])
                    ->class('btn btn-primary');
            })->sort()]
        ;

        if ($this->request->get('visit_count')) {
            $TD[] = TD::make('visit_count','Visit count')->sort();
        }

        if ($this->request->get('registration_count')) {
            $TD[] = TD::make('registration_count', 'Registration Count')->sort();
        }

        if ($this->request->get('qualified_account')) {
            $TD[] = TD::make('Qualified Account')->sort();
        }

        if ($this->request->get('depositing_players_count')) {
            $TD[] = TD::make('depositing_players_count', 'Depositing Players Count')->sort();
        }

        if ($this->request->get('first_deposits_count')) {
            $TD[] = TD::make('first_deposits_count', 'First Deposits Count')->sort();
        }

        if ($this->request->get('first_deposits_sum')) {
            $TD[] = TD::make('first_deposits_sum', 'First Deposits Sum')->render(function ($target){
                return $target->first_deposits_sum ?? '0.00';
            })->sort();
        }

        if ($this->request->get('deposits_sum')) {
            $TD[] = TD::make('deposits_sum', 'Deposits Sum')->render(function ($target){
                return $target->deposits_sum ?? '0.00';
            })->sort();
        }

        if ($this->request->get('deposits_count')) {
            $TD[] = TD::make('deposits_count', 'Deposits Count')->sort();
        }

        if ($this->request->get('cashouts_count')) {
            $TD[] = TD::make('Cashouts Count')->sort();
        }

        if ($this->request->get('cashouts_sum')) {
            $TD[] = TD::make('Cashouts Sum')->sort();
        }

        if ($this->request->get('chargebacks_count')) {
            $TD[] = TD::make('Chargebacks Count')->sort();
        }

        if ($this->request->get('chargebacks_sum')) {
            $TD[] = TD::make('Chargebacks Sum')->sort();
        }

        if ($this->request->get('payment_system_fees_sum')) {
            $TD[] = TD::make('Payment system fees sum')->sort();
        }

        if ($this->request->get('casino_wager')) {
            $TD[] = TD::make('Casino Wager')->sort();
        }

        if ($this->request->get('bets_sum')) {
            $TD[] = TD::make('Bets sum')->sort();
        }

        if ($this->request->get('wins_sum')) {
            $TD[] = TD::make('Wins sum')->sort();
        }

        if ($this->request->get('real_ggr')) {
            $TD[] = TD::make('Real GGR')->sort();
        }

        if ($this->request->get('ggr')) {
            $TD[] = TD::make('GGR')->sort();
        }

        if ($this->request->get('bonus_issues_sum')) {
            $TD[] = TD::make('Bonus issues sum')->sort();
        }

        if ($this->request->get('additional_deductions_sum')) {
            $TD[] = TD::make('Additional deductions sum')->sort();
        }

        if ($this->request->get('ngr')) {
            $TD[] = TD::make('NGR')->sort();
        }

        if ($this->request->get('partner_income')) {
            $TD[] = TD::make('partner_income','Partner income')->sort();
        }


        return
            $TD;
    }
}
