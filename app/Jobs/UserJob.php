<?php

namespace App\Jobs;

use Auth;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class UserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user, $logout = false;

    /**
     * Create a new user job instance.
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->beforeHandle();
        $this->handleJob();
        $this->afterHandle();
    }

    /**
     * Tasks previous to the job.
     *
     * @return void
     */
    public function beforeHandle()
    {
        if (!Auth::check() && $this->user) {
            Auth::login($this->user);
            $this->logout = true;
        }
    }

    abstract public function handleJob();

    /**
     * Tasks after to the job.
     *
     * @return void
     */
    public function afterHandle()
    {
        if ($this->logout) {
            Auth::logout();
        }
    }
}
