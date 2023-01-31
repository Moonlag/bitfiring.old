<?php

namespace App\Orchid\Screens\Affiliate;

use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;

class Brands extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Brands';

    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'Brands';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.affiliate.brands'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {

        $brands = \App\Models\Brands::filters()->select('brand', 'url')->paginate(15);
        return [
            'brands' => $brands
        ];
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [];
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
                    Layout::table('brands', [
                        TD::make('brand', 'Name'),
                        TD::make('url', 'Url'),
                        TD::make('enabled', 'Enabled')->render(function (){
                            return 'true';
                        }),
                    ]),
                ],
            ]),
        ];
    }
}
