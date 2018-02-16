<?php

namespace App\Observers;


use App\Notifications\NotificationsUpdated;
use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotificationObserver
{
    public function created(DatabaseNotification $notification)
    {
        $notification->notifiable->notify(new NotificationsUpdated());
    }
}