<?php

namespace App\Notifications;

use App\Models\Objekte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PropertyCopied extends Notification implements ShouldQueue
{
    use Queueable;

    protected $source, $target;

    /**
     * Create a new notification instance.
     *
     * @param Objekte $source
     * @param Objekte $target
     */
    public function __construct(Objekte $source, Objekte $target)
    {
        $this->source = $source;
        $this->target = $target;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'source' => $this->source,
            'target' => $this->target
        ];
    }
}
