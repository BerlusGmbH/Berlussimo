<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\PersonenRequest;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function toggle(PersonenRequest $request, DatabaseNotification $notification)
    {
        if ($notification->read()) {
            $notification->forceFill(['read_at' => null])->save();
        } else {
            $notification->markAsRead();
        }
        return '';
    }
}