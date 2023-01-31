<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Code;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Layout;
use Orchid\Screen\Layouts\Listener;

class CodeListener extends Listener
{
    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = ['html'];

    /**
     * What screen method should be called
     * as a source for an asynchronous request.
     *
     * The name of the method must
     * begin with the prefix "async"
     *
     * @var string
     */
    protected $asyncMethod = 'asyncCode';

    /**
     * @return Layout[]
     */
    protected function layouts(): array
    {
        return [
            \Orchid\Support\Facades\Layout::rows([
                Quill::make('html')
                    ->toolbar(["text", "color", "header", "list", "format", "media"]),
                Code::make('code')
                    ->language('html')
                    ->readonly()
                    ->title('Code')
                    ->canSee($this->query->has('code')),
            ]),
        ];
    }
}
