<?php

namespace App\Orchid\Screens\Feed;

use App\Models\FeedExports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Exports extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Exports';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Exports';

    public $permission = [
        'platform.feed.exports'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $exports = FeedExports::query()
            ->leftJoin('users', 'feed_exports.staff_id', '=', 'users.id')
            ->where('feed_exports.active', '=', 1)
            ->select('feed_exports.id', 'users.email as admin', 'feed_exports.type_name',
                'feed_exports.status', 'feed_exports.created_at', 'feed_exports.url')
            ->paginate();

        return [
            'exports' => $exports
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
            Layout::table('exports', [
                TD::make('id', 'ID')->sort(),
                TD::make('admin', 'Admin User')->sort(),
                TD::make('type_name', 'Type Name')->sort(),
                TD::make('status', 'Status')->render(function (){
                    return 'Finished';
                })->sort(),
                TD::make('created_at', 'Created at')->render(function (\App\Models\FeedExports $model){
                    return $model->created_at ?? '-';
                })->sort(),
                TD::make('')->render(function (\App\Models\FeedExports $model){
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->class('btn sharp btn-primary tp-btn')
                        ->list([
                            Link::make('View')
                                ->class('dropdown-item')
                                ->icon('pencil'),
                            Button::make('Download')
                                ->class('dropdown-item')
                                ->method('download')
                                ->parameters(
                                    ['filename' => $model->url]
                                )
                                ->class('btn btn-link')
                                ->icon('cloud-download'),
                            Button::make('Delete')
                                ->method('delete')
                                ->parameters(['id' => $model->id])
                                ->class('btn btn-link')
                                ->icon('trash'),
                        ]);
                })->sort(),
            ]),
        ];
    }

    public function delete(Request $request){
        FeedExports::query()->where('id', $request->id)->update(['active' => 0]);
    }

    public function download(Request $request){
        return redirect()->route('admin.view', ['filename' => $request->filename]);
    }
}
