<?php

namespace App\Console\Commands;

use App\Models\UserPreRegistration;
use Illuminate\Console\Command;

class CleanupExpiredPreRegistrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pre-registrations:cleanup {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired user pre-registrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $count = UserPreRegistration::where('expires_at', '<=', now())->count();
            $this->info("Would delete {$count} expired pre-registrations.");
            return;
        }

        $deletedCount = UserPreRegistration::cleanupExpired();

        $this->info("Deleted {$deletedCount} expired pre-registrations.");
    }
}
