<?php

namespace App\Orchid\Screens\Affiliate;

use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;

class InviteAffiliates extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Invite Affiliates';

    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'InviteAffiliates';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.affiliate.invite-affiliates'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            'table' => [
                new Repository([
                    'name' => '-',
                    'visits_count' => '-',
                    'created_at' => '-',
                    'copy_link_to_clipboard' => '-',
                ]),
            ],
        ];
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make('New')
                ->icon('plus')
                ->class('btn btn-success'),
        ];
    }

    /**
     * Views.
     *
     * @return Layout[]
     */
    public function layout(): array
    {
        return [
            Layout::wrapper('admin.soloWrapper', [
                'column' => [
                    Layout::table('table', [
                        TD::make('name', 'Name'),
                        TD::make('visits_count', 'Visits count'),
                        TD::make('created_at', 'Created at'),
                        TD::make('copy_link_to_clipboard', 'Copy link to clipboard'),
                    ]),
                ],
            ]),
        ];
    }
}
