<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LogAccess;
use Illuminate\Support\Facades\DB;

class CleanLogAccessData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logaccess:clean {--dry-run : Show what would be cleaned without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean malformed UTF-8 characters from LogAccess request data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in dry-run mode. No data will be modified.');
        }
        
        $this->info('Starting to clean LogAccess data...');
        
        // Get records with potentially problematic request data
        $query = LogAccess::whereNotNull('request');
        $totalRecords = $query->count();
        
        $this->info("Found {$totalRecords} LogAccess records to process.");
        
        $cleanedCount = 0;
        $errorCount = 0;
        
        // Process in chunks to avoid memory issues
        $query->chunk(100, function ($logAccesses) use (&$cleanedCount, &$errorCount, $dryRun) {
            foreach ($logAccesses as $logAccess) {
                try {
                    $originalRequest = $logAccess->getRawOriginal('request');
                    
                    if (is_string($originalRequest)) {
                        // Try to decode the current JSON
                        $decoded = json_decode($originalRequest, true);
                        
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            // There's an issue with the JSON
                            $this->warn("Record ID {$logAccess->id}: JSON decode error - " . json_last_error_msg());
                            
                            if (!$dryRun) {
                                // Clean the string and re-encode
                                $cleanedString = $this->cleanUtf8($originalRequest);
                                
                                // Try to decode again
                                $decoded = json_decode($cleanedString, true);
                                
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    // Successfully cleaned, save it
                                    DB::table('log_accesses')
                                        ->where('id', $logAccess->id)
                                        ->update(['request' => json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE)]);
                                    
                                    $cleanedCount++;
                                    $this->info("✓ Cleaned record ID {$logAccess->id}");
                                } else {
                                    // Still can't decode, set to empty array
                                    DB::table('log_accesses')
                                        ->where('id', $logAccess->id)
                                        ->update(['request' => '{}']);
                                    
                                    $cleanedCount++;
                                    $this->warn("⚠ Reset record ID {$logAccess->id} to empty object (couldn't recover data)");
                                }
                            } else {
                                $this->info("Would clean record ID {$logAccess->id}");
                                $cleanedCount++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("✗ Error processing record ID {$logAccess->id}: " . $e->getMessage());
                }
            }
        });
        
        $this->info("\nCleaning completed!");
        $this->info("Records processed: {$totalRecords}");
        $this->info("Records cleaned: {$cleanedCount}");
        $this->info("Errors encountered: {$errorCount}");
        
        if ($dryRun && $cleanedCount > 0) {
            $this->info("\nRun without --dry-run to apply the changes.");
        }
    }
    
    /**
     * Clean malformed UTF-8 characters from a string
     */
    private function cleanUtf8($string)
    {
        if (!is_string($string)) {
            return $string;
        }
        
        // Remove or replace invalid UTF-8 characters
        $clean = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        
        // Remove null bytes
        $clean = str_replace("\0", '', $clean);
        
        // Remove control characters except newlines and tabs
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean);
        
        return $clean;
    }
}
