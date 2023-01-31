<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Payments extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Payments';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'Payments';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $payments = \App\Models\Payments::filters()->paginate(20);

        return ['payments' => $payments];
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
            Layout::table('payments', [
                TD::make('email', 'Suspect')->sort(),
                TD::make('amount','Amount')
                    ->sort(),
                TD::make('currency', 'Currency')->sort(),
                TD::make('player_action', 'Action')
                    ->sort(),
                TD::make('source', 'Source')->sort(),
                TD::make('success', 'Success')->sort(),
                TD::make('comments', 'Comments')->sort(),
                TD::make('finished_at', 'Finished at')->sort(),
                TD::make('admin_id', 'Admin user')->sort(),
            ]),
        ];
    }
}
