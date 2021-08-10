<?php
namespace App\Console;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Jobs\StartTransactionSummaryCreation;
use App\Enums\Financial\TransactionSummaryTypeEnum;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CreateTransactionSummaries::class,
        \App\Console\Commands\DeleteMediafileAssets::class,
        \App\Console\Commands\MakeBlurs::class,
        \App\Console\Commands\MakeThumbnails::class,
        \App\Console\Commands\SetMediafileBasename::class,
        \App\Console\Commands\TruncateData::class,
        \App\Console\Commands\UpdateCanceledSubscriptions::class,
        \App\Console\Commands\UpdateMediafilesNullResource::class,
        \App\Console\Commands\UpdateSlugs::class,
        \App\Console\Commands\SendScheduleMessages::class,
        \App\Console\Commands\WebhooksDispatch::class,
        \App\Console\Commands\WebhooksRetry::class,
        \App\Console\Commands\PublishScheduledPosts::class,
        \App\Console\Commands\PopulateContacts::class,
        \App\Console\Commands\PushTestEvent::class,
        \App\Console\Commands\SetmfSize::class,
        \App\Console\Commands\SetTimestamps::class,
        \App\Console\Commands\SettleFinancialAccounts::class,
        \App\Console\Commands\UpdateStoryqueues::class,

        \App\Console\Commands\Dev\PopulateChargebacks::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        Log::info('Schedule is being run');
        // Transaction Summaries Creations
        $schedule->call(function () {
            $queue = Config::get('transactions.summarizeQueue');
            $batch = Bus::batch([
                new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::DAILY)
            ])->then(function (Batch $batch) {
                Log::info('Summarize Daily Transactions Finished');
            })->name('Summarize Daily Transactions')->onQueue("$queue-low");
        })->dailyAt('0:01');

        $schedule->call(function () {
            $queue = Config::get('transactions.summarizeQueue');
            $batch = Bus::batch([
                new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::WEEKLY)
            ])->then(function (Batch $batch) {
                Log::info('Summarize Weekly Transactions Finished');
            })->name('Summarize Weekly Transactions')->onQueue("$queue-low");
        })->weeklyOn(0, '0:01');

        $schedule->call(function () {
            $queue = Config::get('transactions.summarizeQueue');
            $batch = Bus::batch([
                new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::MONTHLY)
            ])->then(function (Batch $batch) {
                Log::info('Summarize Monthly Transactions Finished');
            })->name('Summarize Monthly Transactions')->onQueue("$queue-low");
        })->monthlyOn(1, '0:01');

        $schedule->call(function () {
            $queue = Config::get('transactions.summarizeQueue');
            $batch = Bus::batch([
                new StartTransactionSummaryCreation(TransactionSummaryTypeEnum::YEARLY)
            ])->then(function (Batch $batch) {
                Log::info('Summarize Yearly Transactions Finished');
            })->name('Summarize Yearly Transactions')->onQueue("$queue-low");
        })->yearlyOn(1, 1, '0:01');

        // $schedule->command('subscription:update-canceled')->everyHour();
        // $schedule->command('send:schdule-messages')->everyMinute()->appendOutputTo(storage_path('logs/publish_posts.log'))->runInBackground();
        $schedule->command('publish:schduled-posts')->everyMinute()->appendOutputTo(storage_path('logs/publish_posts.log'))->runInBackground();
        // $schedule->command('publish:posts')
        //           ->everyMinute()->appendOutputTo(storage_path('logs/publish_posts.log'));
        // $schedule->command('expire:post')
        //     ->everyMinute()->appendOutputTo(storage_path('logs/expire_posts.log'));
    }
    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
