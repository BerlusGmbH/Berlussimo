<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.Person.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

Broadcast::channel('Notification.Person.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

Broadcast::channel('test', function () {
    return true;
});