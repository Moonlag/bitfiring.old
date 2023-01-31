<?php

namespace App\Orchid\Layouts;

use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ViewPlayerDocumentTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'document';
    protected $title = 'Documents';
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


    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): array
    {

        return [
                TD::make('description', 'Description')->render(function ($document){
                    $path = Storage::url('public'. $document->image);

                    return "<img src='$path' alt='$document->description'>";
                })->sort(),
                TD::make('created_at', 'Created at')->render(function ($document){
                    return $document->created_at;
                })->sort(),
                TD::make('updated_at', 'Updated at')->sort(),
                TD::make('status', 'Status')->sort()
        ];
    }

}
