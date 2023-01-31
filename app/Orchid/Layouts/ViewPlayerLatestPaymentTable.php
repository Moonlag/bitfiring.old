<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ViewPlayerLatestPaymentTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'latest_payments';

    protected $title = 'Latest payments';

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
            Link::make('All Payments')
                ->route('platform.payments', ['player_id' => $this->query->get('player')->id])
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
            TD::make('')->align(TD::ALIGN_CENTER)->render(function ($latest_payments) {
                $payment =\App\Models\Payments::find($latest_payments->id);
                $payment_system = $payment->payment_system;
                if(!$payment_system){
                    return '';
                }
                $href = $payment_system->link_tx . $payment->source_transaction;
                $status = $latest_payments->status !== 1 ? 'btn disabled p-0' : '';
                switch ($latest_payments->action){
                    case 3:
                        return "<a href='$href' target='_blank' class='$status'>
																	<svg width='20' height='20' viewBox='0 0 63 63'
                                                                         fill='none' xmlns='http://www.w3.org/2000/svg'>
																		<rect width='63' height='63' rx='14'
                                                                              fill='#71B945'></rect>
																		<path
                                                                            d='M40.6186 32.7207L40.6186 32.7207L40.6353 39.6289C40.6354 39.6328 40.6354 39.6363 40.6353 39.6396M40.6186 32.7207L40.1353 39.6341L40.6353 39.635C40.6353 39.6481 40.6347 39.6583 40.6345 39.6627L40.6344 39.6642C40.6346 39.6609 40.6351 39.652 40.6353 39.6407C40.6353 39.6403 40.6353 39.64 40.6353 39.6396M40.6186 32.7207C40.6167 31.9268 39.9717 31.2847 39.1777 31.2866C38.3838 31.2885 37.7417 31.9336 37.7436 32.7275L37.7436 32.7275L37.7519 36.1563M40.6186 32.7207L37.7519 36.1563M40.6353 39.6396C40.6329 40.4282 39.9931 41.0705 39.2017 41.0726C39.2 41.0726 39.1983 41.0727 39.1965 41.0727L39.1944 41.0727L39.1773 41.0726L32.2834 41.056L32.2846 40.556L32.2834 41.056C31.4897 41.054 30.8474 40.4091 30.8494 39.615C30.8513 38.8211 31.4964 38.179 32.2903 38.1809L32.2903 38.1809L35.719 38.1892L22.5364 25.0066C21.975 24.4452 21.975 23.5351 22.5364 22.9737C23.0978 22.4123 24.0079 22.4123 24.5693 22.9737L37.7519 36.1563M40.6353 39.6396C40.6353 39.6376 40.6353 39.6356 40.6353 39.6336L37.7519 36.1563M39.1964 41.0726C39.1957 41.0726 39.1951 41.0726 39.1944 41.0726L39.1964 41.0726Z'
                                                                            fill='white' stroke='white'></path>
																	</svg>
																</a>";
                    case 4:
                        return "<a href='$href' target='_blank' class='$status'>
																	<svg width='20' height='20' viewBox='0 0 63 63'
                                                                         fill='none' xmlns='http://www.w3.org/2000/svg'>
																		<rect width='63' height='63' rx='14'
                                                                              fill='#FF5757'></rect>
																		<path
                                                                            d='M22.1318 30.9043L22.1318 30.9043L22.1151 23.9961C22.1151 23.9922 22.1151 23.9887 22.1152 23.9854M22.1318 30.9043L22.6152 23.9909L22.1152 23.99C22.1152 23.9769 22.1157 23.9667 22.116 23.9623L22.1161 23.9608C22.1159 23.9641 22.1154 23.973 22.1152 23.9843C22.1152 23.9847 22.1152 23.985 22.1152 23.9854M22.1318 30.9043C22.1338 31.6982 22.7788 32.3403 23.5728 32.3384C24.3667 32.3365 25.0088 31.6914 25.0069 30.8975L25.0069 30.8975L24.9986 27.4687M22.1318 30.9043L24.9986 27.4687M22.1152 23.9854C22.1176 23.1968 22.7574 22.5545 23.5488 22.5524C23.5504 22.5524 23.5522 22.5523 23.554 22.5523L23.5561 22.5523L23.5732 22.5524L30.4671 22.569L30.4658 23.069L30.4671 22.569C31.2608 22.571 31.903 23.2159 31.9011 24.01C31.8992 24.8039 31.2541 25.446 30.4602 25.4441L30.4602 25.4441L27.0315 25.4358L40.2141 38.6184C40.7755 39.1798 40.7755 40.0899 40.2141 40.6513C39.6527 41.2127 38.7426 41.2127 38.1812 40.6513L24.9986 27.4687M22.1152 23.9854C22.1152 23.9874 22.1152 23.9894 22.1152 23.9914L24.9986 27.4687M23.5541 22.5524C23.5547 22.5524 23.5554 22.5524 23.5561 22.5524L23.5541 22.5524Z'
                                                                            fill='white' stroke='white'></path>
																	</svg>
																</a>";
                    default:
                        return '';
                }
            })->sort(),
            TD::make('action', 'Action')->align(TD::ALIGN_CENTER)->render(function ($latest_payments) {
                return $latest_payments::ACTION[$latest_payments->action];
            })->sort()->sort(),
            TD::make('created_at', 'Date')->align(TD::ALIGN_CENTER)->render(function ($latest_payments) {
                return $latest_payments->created_at;
            })->sort(),
            TD::make('source', 'Source')->align(TD::ALIGN_CENTER)->sort(),
            TD::make('success', 'Success')->align(TD::ALIGN_CENTER)->render(function ($latest_payments) {
                $class = '';
                switch ($latest_payments->status) {
                    case 1:
                        $class = 'badge-success';
                        break;
                    case 2:
                        $class = 'badge-primary';
                        break;
                    case 3:
                        $class = 'badge-info';
                        break;
                    case 4:
                        $class = 'badge-danger';
                        break;
                    case 5:
                        $class = 'badge-info';
                        break;
                }
                return "<span class='badge light $class'>" . $latest_payments::STATUS[$latest_payments->status] . "</span>";
            })->sort(),
            TD::make('wallet', 'Recipient wallet')->render(function ($latest_payments) {
                return Link::make($latest_payments->wallet)
                    ->class('link-primary')
                    ->target('_blank')
                    ->href($latest_payments->link . $latest_payments->wallet);
            })->align(TD::ALIGN_CENTER)->sort(),
            TD::make('amount', 'Amount')->align(TD::ALIGN_CENTER)->render(function ($latest_payments) {
                if($latest_payments->amount_usd){
                    $amount =  $latest_payments->code !== 'USDT' ? $latest_payments->amount_usd : false;
                }else{
                    $amount = $latest_payments->code !== 'USDT' ? usdt_helper($latest_payments->rate ? $latest_payments->amount / $latest_payments->rate : 0, '') : false;
                }
                return usdt_helper($latest_payments->amount, $latest_payments->code) . ($amount ? " ( " . usdt_helper($amount, 'USDT') . " USDT)" : '');
            })->sort(),
        ];
    }

}
