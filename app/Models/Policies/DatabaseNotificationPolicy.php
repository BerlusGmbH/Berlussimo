<?php


namespace App\Models\Policies;


use App\Models\Person;
use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotificationPolicy
{
    public function markAsRead(Person $user, DatabaseNotification $notification)
    {
        return $user->id === $notification->notifiable->id;
    }

    public function markAsUnread(Person $user, DatabaseNotification $notification)
    {
        return $user->id === $notification->notifiable->id;
    }
}