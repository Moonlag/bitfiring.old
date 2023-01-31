<?php

namespace App\Orchid\Screens\Affiliate;

use App\Orchid\Filters\CommissionsFilter;
use App\Traits\DbCommissionTestingTrait;
use Faker\Generator as Faker;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;

class Commissions extends Screen
{

    use DbCommissionTestingTrait;

    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Commissions';
    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'Commissions';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.affiliate.commissions'
    ];

    public $request;

    /**
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {

        $commission = \App\Models\Commissions::filters()->filtersApply([CommissionsFilter::class])->orderBy('commissions.id', 'DESC')->groupBy('commissions.id')->paginate(10);

        return [
            'commission' => $commission,
            'filter' => [
                'group' => [
                    Select::make('brand')
                        ->fromModel(\App\Models\Brands::class, 'brand')
                        ->title('Brand')
                        ->empty('No select', '0')
                        ->value((int)$this->request->brand),

                    Select::make('strategy')
                        ->title('Strategy')
                        ->options([
                            0 => 'All',
                            1 => 'Cpa',
                            2 => 'Hybrid',
                            3 => 'Revshare',
                        ])
                        ->value((int)$this->request->strategy),

                    Select::make('schedule_plan')
                        ->options([
                            0 => 'All',
                            1 => 'Monthly',
                            2 => 'Weekly',
                        ])
                        ->title('Schedule plan')
                        ->value((int)$this->request->schedule_plan),

                    Select::make('default')
                        ->options([
                            0 => 'All',
                            1 => 'Yes',
                            2 => 'No',
                        ])
                        ->title('Default')
                        ->value((int)$this->request->default),

                    Select::make('allow_subaffiliates')
                        ->options([
                            0 => 'All',
                            1 => 'No index',
                        ])
                        ->title('Allow sub affiliates')
                        ->value((int)$this->request->allow_subaffiliates),

                    Button::make('Filter')
                        ->vertical()
                        ->class('btn btn-outline-info')
                        ->parameters([
                            'alert' => 1
                        ])
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->method('clear_filter')
                        ->class('btn btn-default')
                        ->vertical()
                ],
                'title' => 'Filter'
            ],
            'navTable' => [
                'group' => [
                    Link::make('New')
                        ->route('platform.affiliate.commissions.create')
                        ->icon('plus')
                        ->class('btn btn-outline-success'),
                ],
                'title' => 'Commissions'
            ]
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
            Button::make("All")
                ->class(!$this->request->state ? 'btn btn-info active' : 'btn')
                ->parameters([
                    'state' => 0,
                    'alert' => 0
                ])
                ->method('apply_filter'),

            Button::make('Published')
                ->class($this->request->state === '1' ? 'btn btn-info active' : 'btn')
                ->parameters([
                    'state' => 1,
                    'alert' => 0
                ])
                ->method('apply_filter'),

            Button::make('Draft')
                ->class($this->request->state === '2' ? 'btn btn-info active' : 'btn')
                ->canSee(false)
                ->parameters([
                    'state' => 2,
                    'alert' => 0
                ])
                ->method('apply_filter'),

            Button::make('Archived')
                ->class($this->request->state === '3' ? 'btn btn-outline-info active' : 'btn')
                ->method('apply_filter')
                ->parameters([
                    'state' => 3,
                    'alert' => 0
                ]),
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
            Layout::wrapper('admin.mainWrapper', [
                'col_left' =>
                    Layout::view('admin.filter'),
                'col_right' => [
                    Layout::view('admin.navTable'),
                    Layout::table('commission', [
                        TD::make('title', 'Title')->render(function (\App\Models\Commissions $model) {
                            return Link::make($model->title)
                                ->route('platform.affiliate.commissions.view', $model->id);
                        }),
                        TD::make('default', 'Default')
                            ->align(TD::ALIGN_CENTER)
                            ->render(function (\App\Models\Commissions $model) {
                                return Button::make()
                                    ->icon($model->default ? 'check' : 'close')
                                    ->method('commission_default')
                                    ->parameters(['params' => ['id' => $model->id, 'default' => 1]]);
                            }),
                        TD::make('strategy', 'Strategy')->render(function (\App\Models\Commissions $model) {
                            switch ($model->strategy) {
                                case '1':
                                    return 'cpa';
                                case '2':
                                    return 'hybrid';
                                case '3':
                                    return 'revshare';
                            }
                            return '-';
                        })->align(TD::ALIGN_CENTER),
                        TD::make('schedule_plan', 'Schedule plan')
                            ->align(TD::ALIGN_CENTER)
                            ->render(function (\App\Models\Commissions $model) {
                                switch ($model->schedule_plan) {
                                    case '1':
                                        return 'monthly';
                                    case '2':
                                        return 'weekly';
                                }
                                return '-';
                            }),
                        TD::make('campaign_counts', 'Campaign count')
                            ->align(TD::ALIGN_CENTER)
                            ->render(function (\App\Models\Commissions $model) {
                                return Link::make($model->campaign_counts)
                                    ->class('text-primary');
                            }),
                        TD::make('state', 'State')
                            ->render(function (\App\Models\Commissions $model) {
                                switch ($model->state) {
                                    case '1':
                                        return 'published';
                                    case '2':
                                        return 'draft';
                                    case '3':
                                        return 'archived';
                                }
                                return '-';
                            }),
                        TD::make('edit', '')
                            ->render(function (\App\Models\Commissions $model) {
                                return Link::make('Edit')
                                    ->route('platform.affiliate.commissions.edit', $model->id)
                                    ->class('text-primary');
                            }),
                    ]),
                ]]),
        ];
    }


    public function commission_default(Request $request)
    {
        $input = $request->all();
        if (isset($input['params'])) {
            $input = $input['params'];

            \App\Models\Commissions::query()->update(['default' => 0]);
            \App\Models\Commissions::query()->where('id', $input['id'])->update(['default' => $input['default']]);

            Toast::info(__('Success'));
        }

    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.affiliate.commissions');
    }

    public function apply_filter(Request $request)
    {
        if ($request->alert) {
            Toast::success(__('Filter apply'));
        }
        return redirect()->route('platform.affiliate.commissions', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
