<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PersonMerged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $left, $right, $merged;

    /**
     * Create a new notification instance.
     *
     * @param array $left
     * @param array $right
     * @param array $merged
     */
    public function __construct(array $left, array $right, array $merged)
    {
        $this->left = $left;
        $this->right = $right;
        $this->merged = $merged;
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
            'left' => $this->left,
            'right' => $this->right,
            'merged' => $this->merged
        ];
    }
}
