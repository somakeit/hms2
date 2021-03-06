<?php

namespace App\Listeners;

use App\Jobs\PostGitDeployedJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Orphans\GitDeploy\Events\GitDeployed;

class GitDeployedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param GitDeployed  $event
     *
     * @return void
     */
    public function handle(GitDeployed $event)
    {
        PostGitDeployedJob::dispatch();
    }
}
