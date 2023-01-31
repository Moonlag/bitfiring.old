<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\PaymentSystem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CashReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'CashReport';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'CashReport';

    public $permission = [
        'platform.finance.cash-report'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        switch ($request->sum_by) {
            case 1:
                $start = $request->date_range ? Carbon::parse($request->date_range['start']) : Carbon::now()->startOfMonth();
                $end = $request->date_range ? Carbon::parse($request->date_range['end']) : Carbon::now();
                $format = 'Y-m-d';
                break;
            case 2:
                $start = $request->date_range ? Carbon::parse($request->date_range['start'])->startofWeek() : Carbon::now()->startOfMonth();
                $end = $request->date_range ? Carbon::parse($request->date_range['end'])->endOfWeek() : Carbon::now();
                $format = 'Y';
                break;
            case 3:
                $start = $request->date_range ? Carbon::parse($request->date_range['start'])->startOfMonth() : Carbon::now()->startOfMonth();
                $end = $request->date_range ? Carbon::parse($request->date_range['end'])->endOfMonth() : Carbon::now()->endOfMonth();
                $format = 'F Y';
                break;
            case 4:
                $start = $request->date_range ? Carbon::parse($request->date_range['start'])->startOfYear() : Carbon::now()->startOfYear();
                $end = $request->date_range ? Carbon::parse($request->date_range['end'])->endOfYear() : Carbon::now()->endOfYear();
                $format = 'Y';
                break;
            default:
                $start = $request->date_range ? Carbon::parse($request->date_range['start']) : Carbon::now()->startOfMonth();
                $end = $request->date_range ? Carbon::parse($request->date_range['end']) : Carbon::now();
                $format = 'Y-m-d';
        }

        $currency = Currency::query()
            ->Leftjoin('currency_exchange', 'currency.id', '=', 'currency_exchange.currency_id')
            ->where('currency_exchange.currency_to', '=', 3)
            ->select('currency.id', 'currency.code', 'currency_exchange.rate', 'currency.name')
            ->get();

        $payment_system = PaymentSystem::query()
            ->select('payment_system.name as pay_system', 'payment_system.id as pay_id')
            ->get()->toArray();

        $country = Countries::query()->where('status', 1)->get()->toArray();

        $transaction = \App\Models\Transactions::query()
            ->leftJoin('payments', function ($join) {
                $join->where('transactions.reference_type_id', 1);
                $join->on('transactions.reference_id', '=', 'payments.id');
            })
            ->leftJoin('players', 'transactions.player_id', '=', 'players.id')
            ->select(
                'transactions.amount',
                'transactions.created_at',
                'transactions.currency_id',
                'transactions.type_id',
                'transactions.reference_type_id',
                'payments.payment_system_id',
                'players.country_id'
            )
            ->whereIn('transactions.reference_type_id', [5, 1])
            ->groupBy('transactions.id')
            ->get()->toArray();

        $check_box_filter = [];
        foreach ($currency as $kye => $value) {
            if ($kye === 0) {
                $check_box_filter[] = CheckBox::make("currency[$value->id]")
                    ->placeholder($value->code . ' - ' . $value->name)
                    ->value($request->currency[$value->id] ?? false)
                    ->title('Currency');
            } else {
                $check_box_filter[] = CheckBox::make("currency[$value->id]")
                    ->placeholder($value->code . ' - ' . $value->name)
                    ->value($request->currency[$value->id] ?? false);
            }
        }

        if ($request->currency) {
            $currency_request = $request->currency;
            $currency = array_filter($currency->toArray(), function ($v, $k) use ($currency_request) {
                return in_array($v['id'], array_keys($currency_request));
            }, 1);
        }

        $date = $this->set_date($start, $end, $format, $currency, $request->sum_by);
        $payment_service_provider = $this->set_payment_system($payment_system, $date);
        $payment_service_provider = $this->get_payment_system($payment_service_provider, $transaction, $format);
        $cash_summary = $this->get_cash_summary($date, $transaction, $format);
        $by_country = $this->set_countries($date, $currency, $transaction, $country, $format);
        return [
            'currency' => $currency,
            'payment_service_provider' => $payment_service_provider,
            'cash_summary' => $cash_summary,
            'by_country' => $by_country,
            'filter' => [
                'title' => 'Filter',
                'group' => array_merge([
                    DateRange::make('date_range')
                        ->title('Date Range')
                        ->value($request->date_range ?? ['start' => Carbon::now()->startOfMonth()->format($format), 'end' => Carbon::now()->format('Y-m-d')]),

                ], $check_box_filter, [

                    Select::make('sum_by')
                        ->title('Sum By')
                        ->options([
                            1 => 'Day',
                            2 => 'Week',
                            3 => 'Month',
                            4 => 'Year'
                        ])
                        ->empty('No select', 0)
                        ->value((int)$request->sum_by),]),
                'action' => [
                    Button::make('Filter')
                        ->vertical()
                        ->icon('filter')
                        ->class('btn btn-primary btn-sm btn-block')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->icon('refresh')
                        ->method('clear_filter')
                        ->class('btn btn-sm btn-dark')
                        ->vertical(),
                ]
            ],
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::wrapper('orchid.wrapper-col2', [
                'col_left' => [
                    Layout::view('orchid.filter'),
                    Layout::view('orchid.finance.cash-report')
                ]
            ]),
        ];
    }

    public function get_payment_system($date, $transaction, $format)
    {
        return array_map(function ($pay_system) use ($transaction, $format) {
            $currency_id = $pay_system['id'];
            $pay_id = $pay_system['pay_id'];
            $date = $pay_system['date'];
            $data = array_filter($transaction, function ($v, $k) use ($currency_id, $pay_id, $format, $date) {
                return $currency_id === $v['currency_id'] && $pay_id === $v['payment_system_id'] && Carbon::parse($v['created_at'])->format($format) === $date;;
            }, 1);

            $deposit = array_filter($data, function ($v, $k) {
                return ($v['type_id'] === 1 && $v['reference_type_id'] === 5) || ($v['type_id'] === 3 && $v['reference_type_id'] === 1);
            }, 1);

            $deposit = array_sum(array_column($deposit, 'amount'));

            $cashout = array_filter($data, function ($v, $k) {
                return ($v['type_id'] === 2 && $v['reference_type_id'] === 5) || ($v['type_id'] === 4 && $v['reference_type_id'] === 1);
            }, 1);

            $cashout = array_sum(array_column($cashout, 'amount'));

            $chargebacks = array_filter($data, function ($v, $k) {
                return ($v['type_id'] === 4 && $v['reference_type_id'] === 5) || ($v['type_id'] === 6 && $v['reference_type_id'] === 1);
            }, 1);

            $chargebacks = array_sum(array_column($chargebacks, 'amount'));

            $reversals = array_filter($data, function ($v, $k) {
                return ($v['type_id'] === 5 && $v['reference_type_id'] === 5) || ($v['type_id'] === 8 && $v['reference_type_id'] === 1);
            }, 1);

            $reversals = array_sum(array_column($reversals, 'amount'));

            $gifts = array_filter($data, function ($v, $k) {
                return ($v['type_id'] === 5 && $v['reference_type_id'] === 1);
            }, 1);

            $gifts = array_sum(array_column($gifts, 'amount'));

            $saldo = $deposit - abs($cashout) + $chargebacks + $reversals - $gifts;

            $saldo_usd = $saldo * $pay_system['rate'];

            return array_merge($pay_system, ['deposit' => $deposit, 'cashout' => abs($cashout), 'chargebacks' => $chargebacks,
                'reversals' => $reversals, 'gifts' => $gifts, 'saldo' => $saldo, 'saldo_usd' => $saldo_usd]);
        }, $date);
    }

    public function get_cash_summary($date, $transaction, $format)
    {
        return array_map(function ($currency) use ($transaction, $format) {
            $currency_id = $currency['id'];
            $date = $currency['date'];
            $data = array_filter($transaction, function ($v, $k) use ($currency_id, $date, $format) {
                return $currency_id === $v['currency_id'] && Carbon::parse($v['created_at'])->format($format) === $date;
            }, 1);

            $deposit = array_filter($data, function ($v, $k) {
                return ($v['type_id'] === 1 && $v['reference_type_id'] === 5) || ($v['type_id'] === 3 && $v['reference_type_id'] === 1);
            }, 1);

            $deposit = array_sum(array_column($deposit, 'amount'));

            $cashout = array_filter($data, function ($v, $k) {
                return ($v['type_id'] === 2 && $v['reference_type_id'] === 5) || ($v['type_id'] === 4 && $v['reference_type_id'] === 1);
            }, 1);

            $cashout = array_sum(array_column($cashout, 'amount'));

            $chargebacks = array_filter($data, function ($v, $k) {
                return ($v['type_id'] === 4 && $v['reference_type_id'] === 5) || ($v['type_id'] === 6 && $v['reference_type_id'] === 1);
            }, 1);

            $chargebacks = array_sum(array_column($chargebacks, 'amount'));

            $reversals = array_filter($data, function ($v, $k) {
                return ($v['type_id'] === 5 && $v['reference_type_id'] === 5) || ($v['type_id'] === 8 && $v['reference_type_id'] === 1);
            }, 1);

            $reversals = array_sum(array_column($reversals, 'amount'));

            $gifts = array_filter($data, function ($v, $k) {
                return ($v['type_id'] === 5 && $v['reference_type_id'] === 1);
            }, 1);

            $gifts = array_sum(array_column($gifts, 'amount'));

            $saldo = $deposit - abs($cashout) + $chargebacks + $reversals - $gifts;

            $saldo_usd = $saldo * $currency['rate'];
            return array_merge($currency, ['deposit' => $deposit, 'cashout' => abs($cashout), 'chargebacks' => $chargebacks,
                'reversals' => $reversals, 'affiliate' => 0, 'gifts' => $gifts, 'saldo' => $saldo, 'saldo_usd' => $saldo_usd]);
        }, $date);
    }


    public function set_date($start, $end, $format, $currency, $type = 0)
    {
        $data = [];
        while ($start->lte($end)) {
            $date = $start->copy()->format($format);
            switch ($type) {
                case 1:
                    $start->addDay();
                    break;
                case 2:
                    $date = $start->copy()->day . ' ' . $start->copy()->format('F') . ' - ' . $start->copy()->endOfWeek()->day . '  ' . $start->copy()->endOfWeek()->format('F') . ' ' . $date;
                    $start->addWeek();
                    break;
                case 3:
                    $start->addMonth();
                    break;
                case 4:
                    $start->addYear();
                    break;
                default:
                    $start->addDay();
            }
            foreach ($currency as $cur) {
                $data[] = [
                    'date' => $date,
                    'id' => $cur['id'],
                    'code' => $cur['code'],
                    'rate' => $cur['rate']
                ];
            }
        }
        return $data;
    }


    public function set_countries($date, $currencies, $transaction, $countries, $format)
    {
        $date_country = array_values(array_unique(array_column($date, 'date')));

        $result = [];
        foreach ($date_country as $k => $d) {
            $result[$k]['date'] = $d;
            foreach ($countries as $key => $country) {
                $saldo_usd = 0;
                foreach ($currencies as $currency) {
                    if (!isset($result[$k]['country'][$key]['name'])) {
                        $result[$k]['country'][$key]['name'] = $country['name'];
                    }
                    $result[$k]['country'][$key][$currency['code']] = $this->get_countries($transaction, $currency, $country, $format, $d);
                    $saldo_usd += $result[$k]['country'][$key][$currency['code']] * $currency['rate'];
                }
                $result[$k]['country'][$key]['saldo_usd'] = $saldo_usd;
            }
        }

        return $result;
    }

    public function get_countries($transaction, $currency, $countries, $format, $date)
    {
        $currency_id = $currency['id'];
        $countries_id = $countries['id'];

        $data = array_filter($transaction, function ($v, $k) use ($currency_id, $countries_id, $format, $date) {
            return $currency_id === $v['currency_id'] && $v['country_id'] === $countries_id && Carbon::parse($v['created_at'])->format($format) === $date;
        }, 1);

        $deposit = array_filter($data, function ($v, $k) {
            return ($v['type_id'] === 1 && $v['reference_type_id'] === 5) || ($v['type_id'] === 3 && $v['reference_type_id'] === 1);
        }, 1);

        $deposit = array_sum(array_column($deposit, 'amount'));

        $cashout = array_filter($data, function ($v, $k) {
            return ($v['type_id'] === 2 && $v['reference_type_id'] === 5) || ($v['type_id'] === 4 && $v['reference_type_id'] === 1);
        }, 1);

        $cashout = array_sum(array_column($cashout, 'amount'));

        $chargebacks = array_filter($data, function ($v, $k) {
            return ($v['type_id'] === 4 && $v['reference_type_id'] === 5) || ($v['type_id'] === 6 && $v['reference_type_id'] === 1);
        }, 1);

        $chargebacks = array_sum(array_column($chargebacks, 'amount'));

        $reversals = array_filter($data, function ($v, $k) {
            return ($v['type_id'] === 5 && $v['reference_type_id'] === 5) || ($v['type_id'] === 8 && $v['reference_type_id'] === 1);
        }, 1);

        $reversals = array_sum(array_column($reversals, 'amount'));

        $gifts = array_filter($data, function ($v, $k) {
            return ($v['type_id'] === 5 && $v['reference_type_id'] === 1);
        }, 1);

        $gifts = array_sum(array_column($gifts, 'amount'));

        return $deposit - abs($cashout) + $chargebacks + $reversals - $gifts;
    }

    public function set_payment_system($payments, $date)
    {
        $result = [];
        foreach ($payments as $pay) {
            foreach ($date as $cur) {
                $result [] = array_merge($pay, $cur);
            }

        }
        return $result;
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.cash-report');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.cash-report', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
