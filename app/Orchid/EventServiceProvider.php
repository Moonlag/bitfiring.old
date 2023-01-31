<?php


namespace App\Orchid;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Orchid\Platform\Events\UploadFileEvent;
use App\Listeners\UploadListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UploadFileEvent::class => [
            UploadListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
        parent::boot();
    }
}
