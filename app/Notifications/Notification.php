<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification as IlluminateNotification;

class Notification extends IlluminateNotification {

    public function toSendgrid($notifiable)
    {
        return [];
    }
}