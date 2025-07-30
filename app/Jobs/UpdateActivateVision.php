<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class UpdateActivateVision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $vision;
    public bool $isActive;

    public function __construct($vision, $isActive)
    {
        $this->vision = $vision;
        $this->isActive = $isActive;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->vision->update(['is_active' => $this->isActive]);
    }
}
