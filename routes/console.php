<?php

use App\Jobs\Banking\MembershipAuditJob;
use App\Jobs\DiscordAuditJob;
use App\Jobs\Gatekeeper\ZoneOccupantResetJob;
use App\Jobs\Membership\AuditYoungHackersJob;
use App\Jobs\Membership\RetentionEmailJob;
use App\Jobs\PostGitDeployedJob;
use HMS\Facades\Features;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('hms:auto-deploy', function () {
    PostGitDeployedJob::dispatchSync();
})->describe('Run the GitDeployedJob');

Artisan::command('hms:reset-zones', function () {
    ZoneOccupantResetJob::dispatchSync();
})->describe('Audit Zone Occupant and reset people back to Off-site');

Artisan::command('hms:members:audit', function () {
    MembershipAuditJob::dispatch();
})->describe('Audit the members against current banking records');

Artisan::command('hms:members:youngHackerAudit', function () {
    AuditYoungHackersJob::dispatchSync();
})->describe('Audit young hackers for any that have turned 18');

Artisan::command('hms:discord:audit', function () {
    DiscordAuditJob::dispatchSync();
})->describe('Audit Discord member roles');

if (Features::isEnabled('retention_email')) {
    Artisan::command('hms:members:retentionEmail', function () {
        RetentionEmailJob::dispatchSync();
    })->describe('Email members shortly after becoming members');
}
