<?php


namespace App\Listeners;


use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Orchid\Platform\Events\UploadFileEvent;

class UploadListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  UploadFileEvent  $event
     * @return void
     */
    public function handle(UploadFileEvent $event)
    {
//        dd($event->attachment);
////        $event->time;
    }
}
