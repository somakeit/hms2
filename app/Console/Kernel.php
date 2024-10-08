<?php

namespace App\Console;

use App\Jobs\Banking\MembershipAuditJob;
use App\Jobs\DiscordAuditJob;
use App\Jobs\EmailTeamReminderJob;
use App\Jobs\Gatekeeper\TemporaryAcccessCheckZoneOccupancyJob;
use App\Jobs\Gatekeeper\UpdateTemporaryAccessRoleJob;
use App\Jobs\Gatekeeper\ZoneOccupantResetJob;
use App\Jobs\Governance\RecalculateMeetingQuorumJob;
use App\Jobs\Governance\RemoveTempoarayRegisterOfAccessJob;
use App\Jobs\Membership\AuditYoungHackersJob;
use App\Jobs\Membership\RetentionEmailJob;
use App\Jobs\Snackspace\LogDebtJob;
use App\Jobs\Snackspace\MemberDebtNotificationJob;
use HMS\Facades\Features;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('invites:purge')
                 ->daily();

        $schedule->job(new MembershipAuditJob)
                 ->weekdays()
                 ->dailyAt('23:55');

        $schedule->job(new AuditYoungHackersJob)
                ->dailyAt('06:00');

        $schedule->command('auth:clear-resets')
                ->weekly();

        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command('telescope:prune --hours=48')->daily();
        $schedule->command('passport:purge')->daily();

        $schedule->job(new RemoveTempoarayRegisterOfAccessJob)->daily();
        $schedule->job(new LogDebtJob)->daily();
        $schedule->job(new ZoneOccupantResetJob)->hourly();

        if (Features::isEnabled('snackspace')) {
            $schedule->job(new MemberDebtNotificationJob)->monthlyOn(1, '7:00');
        }

        $schedule->job(new EmailTeamReminderJob)->dailyAt('7:27');
        $schedule->job(new RecalculateMeetingQuorumJob)->everyFiveMinutes()
            ->environments(['local', 'rommie', 'production']);
        $schedule->job(new UpdateTemporaryAccessRoleJob)->everyFiveMinutes()
            ->environments(['local', 'rommie', 'production']);
        $schedule->job(new TemporaryAcccessCheckZoneOccupancyJob)->everyFiveMinutes()
            ->environments(['local', 'rommie', 'production']);

        if (config('services.healthchecks.check_uuid')) {
            $schedule->call(function () {
                file_get_contents(
                    'https://hc-ping.com/' . config('services.healthchecks.check_uuid')
                    . ($this->isHorizonActive() ? '' : '/fail')
                );
            })
            ->everyFiveMinutes();
        }

        if (config('services.discord.token')) {
            $schedule->job(new DiscordAuditJob)
                     ->dailyAt('12:15');
        }

        if (Features::isEnabled('retention_email')) {
            $schedule->job(new RetentionEmailJob)
                     ->weekdays()
                     ->dailyAt('11:00');
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected function isHorizonActive(): bool
    {
        if (! $masters = app(MasterSupervisorRepository::class)->all()) {
            return false;
        }

        return collect($masters)->some(function ($master) {
            return $master->status !== 'paused';
        });
    }
}
