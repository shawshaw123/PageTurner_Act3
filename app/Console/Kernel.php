<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily backup at 02:00 AM
        $schedule->command('backup:run-custom --type=daily')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onSuccess(function () {
                     \Log::info('Daily backup completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('Daily backup failed');
                 });

        // Weekly backup every Sunday at 02:00 AM
        $schedule->command('backup:run-custom --type=weekly')
                 ->weekly()
                 ->sundays()
                 ->at('02:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onSuccess(function () {
                     \Log::info('Weekly backup completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('Weekly backup failed');
                 });

        // Monthly backup on 1st day at 02:00 AM
        $schedule->command('backup:run-custom --type=monthly')
                 ->monthly()
                 ->when(function () {
                     return now()->day === 1;
                 })
                 ->at('02:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onSuccess(function () {
                     \Log::info('Monthly backup completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('Monthly backup failed');
                 });

        // Clean old backups daily at 03:00 AM
        $schedule->command('backup:clean-custom --force')
                 ->dailyAt('03:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onSuccess(function () {
                     \Log::info('Backup cleanup completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('Backup cleanup failed');
                 });

        // Cancel pending orders older than 24 hours - runs hourly
        $schedule->command('order:cleanup-pending')
                 ->hourly()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onSuccess(function () {
                     \Log::info('Pending orders cleanup completed');
                 })
                 ->onFailure(function () {
                     \Log::error('Pending orders cleanup failed');
                 });

        // Clear expired sessions daily at 04:00 AM
        $schedule->command('session:clear-expired')
                 ->dailyAt('04:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Archive and compress old logs weekly
        $schedule->command('log:rotate')
                 ->weekly()
                 ->sundays()
                 ->at('05:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Generate daily sales report at 06:00 AM
        $schedule->command('report:generate-daily')
                 ->dailyAt('06:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onSuccess(function () {
                     \Log::info('Daily sales report generated successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('Daily sales report generation failed');
                 });

        // Prune old notification records weekly
        $schedule->command('model:prune', [
                    '--model' => [\App\Models\ImportLog::class, \App\Models\ExportLog::class]
                 ])
                 ->weekly()
                 ->sundays()
                 ->at('07:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Archive audit logs monthly
        $schedule->command('audit:archive')
                 ->monthly()
                 ->on(1)
                 ->at('08:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onSuccess(function () {
                     \Log::info('Audit logs archived successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('Audit logs archiving failed');
                 });

        // Clean up expired export files daily at 23:00
        $schedule->command('export:cleanup-expired')
                 ->dailyAt('23:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // System health check every 6 hours
        $schedule->command('system:health-check')
                 ->cron('0 */6 * * *') // Every 6 hours
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onSuccess(function () {
                     \Log::info('System health check completed');
                 })
                 ->onFailure(function () {
                     \Log::error('System health check failed');
                 });

        // Database optimization weekly
        $schedule->command('db:optimize')
                 ->weekly()
                 ->saturdays()
                 ->at('02:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onSuccess(function () {
                     \Log::info('Database optimization completed');
                 })
                 ->onFailure(function () {
                     \Log::error('Database optimization failed');
                 });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
