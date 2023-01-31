<?php


namespace App\Traits;


use App\Models\Attendances;

trait AttendancesTrait
{
    public function add_admin($request){
        $data = [
            'path' => $request->path(),
            'staff_id' => $request->user()->id,
            'user_agent' => $request->userAgent(),
            'session_sid' => $request->session()->getId(),
            'http_method' => $request->method(),
            'referer' => $request->headers->get('referer'),
            'params' => json_encode($request->all())
        ];

        Attendances::query()->insert($data);
    }
}
