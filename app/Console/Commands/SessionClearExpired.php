<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SessionClearExpired extends Command
{
    protected $signature = 'session:clear-expired';
    protected $description = 'Clear expired sessions from the database';

    public function handle()
    {
        $this->info('Clearing expired sessions...');
        
        try {
            // Clear expired sessions from database
            $deleted = DB::table('sessions')
                ->where('last_activity', '<', now()->subMinutes(config('session.lifetime'))->timestamp)
                ->delete();
            
            $this->info("Cleared {$deleted} expired sessions.");
            Log::info("Session cleanup completed: {$deleted} expired sessions cleared.");
            
            // Also clear old sessions (older than 30 days regardless of activity)
            $oldDeleted = DB::table('sessions')
                ->where('created_at', '<', now()->subDays(30))
                ->delete();
            
            if ($oldDeleted > 0) {
                $this->info("Cleared {$oldDeleted} old sessions (older than 30 days).");
                Log::info("Old session cleanup completed: {$oldDeleted} old sessions cleared.");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Failed to clear expired sessions: ' . $e->getMessage());
            Log::error('Session cleanup failed: ' . $e->getMessage());
            return 1;
        }
    }
}
