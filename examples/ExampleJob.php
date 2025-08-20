<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExampleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * Enhanced scanner recognizes this as a legitimate queue property.
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     * Enhanced scanner recognizes this as a legitimate queue property.
     */
    public $maxExceptions = 2;

    /**
     * The number of seconds the job can run before timing out.
     * Enhanced scanner recognizes this as a legitimate queue property.
     */
    public $timeout = 120;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     * Enhanced scanner recognizes this as a legitimate queue property.
     */
    public $retryAfter = 60;

    /**
     * The name of the connection the job should be sent to.
     * Enhanced scanner recognizes this as a legitimate queue property.
     */
    public $connection = 'redis';

    /**
     * The name of the queue the job should be sent to.
     * Enhanced scanner recognizes this as a legitimate queue property.
     */
    public $queue = 'processing';

    private $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     * Enhanced scanner validates that ShouldQueue implementations have handle methods.
     */
    public function handle()
    {
        // Process the job
        // Enhanced scanner recognizes this as implementing the ShouldQueue interface properly
        
        foreach ($this->data as $item) {
            // Process each item
            $this->processItem($item);
        }
    }

    /**
     * Process a single item
     */
    private function processItem($item)
    {
        // Processing logic here
        sleep(1); // Simulate processing time
    }

    /**
     * Handle a job failure.
     * Enhanced scanner recognizes this as a legitimate queue method.
     */
    public function failed(\Throwable $exception)
    {
        // Send user notification of failure, etc...
        Log::error('Job failed: ' . $exception->getMessage());
    }
}
