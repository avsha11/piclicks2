<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\CollageServices;
use App\Repository\Eloquent\DesignCollageRepository;
use App\Repository\FrontEnd\Cart\CartRepository;
use Illuminate\Support\Facades\Log;

class GeneratePrintFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uniqueId;
    protected $masterData;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(string $uniqueId, $masterData)
    {
        $this->uniqueId = $uniqueId;
        $this->masterData = $masterData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Increase memory limit for print file generation
        ini_set('memory_limit', '1024M');
        
        Log::info("GeneratePrintFilesJob started", [
            'unique_id' => $this->uniqueId,
            'job_id' => $this->job->getJobId()
        ]);

        try {
            // Get repository instances
            $designCollageRepository = app(DesignCollageRepository::class);
            $cartRepository = app(CartRepository::class);
            
            // Create CollageServices instance with both repositories
            $collageService = new CollageServices($designCollageRepository, $cartRepository);
            
            // Generate print files
            $collageService->generatePrintFilesForCollageAsync($this->uniqueId, $this->masterData);
            
            Log::info("GeneratePrintFilesJob completed successfully", [
                'unique_id' => $this->uniqueId
            ]);
        } catch (\Exception $e) {
            Log::error("GeneratePrintFilesJob failed", [
                'unique_id' => $this->uniqueId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GeneratePrintFilesJob failed permanently", [
            'unique_id' => $this->uniqueId,
            'error' => $exception->getMessage()
        ]);
    }
}
