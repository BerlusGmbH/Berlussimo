<?php

namespace App\Observers;

use Illuminate\Notifications\DatabaseNotification;
use Nuwave\Lighthouse\Execution\Utils\Subscription;

class DatabaseNotificationObserver
{
    public function created(DatabaseNotification $notification)
    {
        Subscription::broadcast('notificationAdded', $notification);
    }
}