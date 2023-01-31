<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\FeedExports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{

    public function get_export_file(Request $request)
    {

        $path = 'public/exports/admin_csv/';
        $exists = Storage::disk('local')->exists($path . $request->filename . '.csv');

        if ($exists) {
            $path = Storage::disk('local')->path($path . $request->filename . '.csv');

            $headers = array(
                'Content-Description' => 'File Transfer',
                'Content-Type' => 'application/octet-stream',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-Disposition' => 'attachment; filename="' . basename($path) . '"',
                'Expires' => '0',
                'Pragma' => 'public',
                'Content-Length' => filesize($path)
            );
            $feed_name = FeedExports::query()->where('url', $request->filename)->select('created_at', 'type_name')->first();
            $feed_name = $feed_name->type_name . '_' . $feed_name->created_at;
            return Response::download($path, $feed_name . '.csv', $headers);
        }
    }
}
