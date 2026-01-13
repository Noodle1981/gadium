<?php

namespace App\Jobs;

use App\Services\CsvImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes

    protected $filePath;
    protected $type;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, string $type, int $userId)
    {
        $this->filePath = $filePath;
        $this->type = $type;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(CsvImportService $importService): void
    {
        try {
            Log::info("Starting import job for {$this->type}. File: {$this->filePath}");

            // Ensure file exists
            if (!file_exists($this->filePath)) {
                throw new Exception("File not found: {$this->filePath}");
            }

            $handle = fopen($this->filePath, 'r');
            
            // Read Header to map columns correctly
            $fileHeaders = fgetcsv($handle);
            if (!$fileHeaders) {
                throw new Exception("Empty file or no headers");
            }
            
            // Trim headers to match CsvImportService expectations
            $headers = array_map('trim', $fileHeaders);
            // Remove BOM
            if (isset($headers[0])) {
                $headers[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $headers[0]);
            }

            $chunkSize = 1000;
            $chunk = [];
            
            $totalInserted = 0;
            $totalSkipped = 0;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Skip empty lines
                if (array_filter($data) && count($data) === count($headers)) {
                    $chunk[] = array_combine($headers, $data);
                }

                if (count($chunk) >= $chunkSize) {
                    $stats = $importService->importChunk($chunk, $this->type);
                    $totalInserted += $stats['inserted'];
                    $totalSkipped += $stats['skipped'];
                    $chunk = [];
                }
            }

            // Process remaining
            if (!empty($chunk)) {
                $stats = $importService->importChunk($chunk, $this->type);
                $totalInserted += $stats['inserted'];
                $totalSkipped += $stats['skipped'];
            }

            fclose($handle);
            
            // Clean up file
            @unlink($this->filePath);

            Log::info("Import completed. Inserted: {$totalInserted}, Skipped: {$totalSkipped}");
            
            // TODO: Notify user via database notification or similar

        } catch (Exception $e) {
            Log::error("Import Job Failed: " . $e->getMessage());
            $this->fail($e);
        }
    }


}
