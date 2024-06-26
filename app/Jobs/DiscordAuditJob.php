<?php

namespace App\Jobs;

use Exception;
use HMS\Entities\Profile;
use HMS\Entities\Role;
use HMS\Helpers\Discord;
use HMS\Repositories\ProfileRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DiscordAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Nothing to do here...
    }

    /**
     * Sends a DM to the Discord user letting them know roles have been removed.
     *
     * @param Discord $discord
     * @param DiscordUser $discordMember
     *
     * @return void
     */
    private function notifyDiscordUser(Discord $discord, $discordMember)
    {
        $link = route('redirector.user.edit');
        $message = <<<EOF
        __**Discord username not found in HMS**__

        Unfortunately we were not able to locate your Discord username in HMS.

        Have you recently changed your Discord username? Please ensure you update this on your HMS profile.

        $link
        EOF;

        $channel = $discord->getDiscordClient()->user->createDm([
            'recipient_id' => (int) $discordMember['user']['id'],
        ]);

        $discord->getDiscordClient()->channel->createMessage([
            'channel.id' => (int) $channel['id'],
            'content' => $message,
        ]);
    }

    /**
     * Removes all roles from the discord user.
     *
     * @param Discord $discord
     * @param DiscordUser $discordMember
     *
     * @return void
     */
    private function stripRoles(Discord $discord, $discordMember)
    {
        foreach ($discordMember['roles'] as $discordRoleId) {
            $discord->getDiscordClient()->guild->removeGuildMemberRole([
                'guild.id' => config('services.discord.guild_id'),
                'user.id' => (int) $discordMember['user']['id'],
                'role.id' => (int) $discordRoleId,
            ]);
        }
    }

    /**
     * Execute the job.
     *
     * @param ProfileRepository $profileRepository
     *
     * @return void
     */
    public function handle(
        ProfileRepository $profileRepository
    ) {
        $discord = new Discord(
            config('services.discord.token'),
            config('services.discord.guild_id')
        );

        $currentMember = $discord->findRoleByName('Current Member')['id'];
        $members = $discord->listGuildMembers();

        foreach ($members as $member) {
            // If the Discord user doesn't have the "Current Member" role, we can skip past them.
            if (! in_array($currentMember, $member['roles'])) {
                continue;
            }

            // Attempt to find the user by their snowflake.
            $profile = $profileRepository->findOneByDiscordUserSnowflake($member['user']['id']);

            // Attempt to find the user using just their Discord username
            if (! $profile) {
                $profile = $profileRepository->findOneByDiscordUsername($member['user']['username']);
            }

            // Still stuck, try with discriminator
            if (! $profile) {
                if ((int) $member['user']['discriminator'] > 0) {
                    $discordUsername = $member['user']['username'] . '#' . $member['user']['discriminator'];
                    $profile = $profileRepository->findOneByDiscordUsername($discordUsername);
                }
            }

            // Assuming we found a profile, make sure they are
            // actually a current member, otherwise strip off any Discord
            // roles.
            $found = false;
            if ($profile) {
                // If we got a profile but their snowflake is empty, populate it.
                if ($profile->getDiscordUserSnowflake() != $member['user']['id']) {
                    $profile->setDiscordUserSnowflake($member['user']['id']);
                    $profileRepository->save($profile);
                }

                // We can also check the username in case it changed, but they have a snowflake set.
                if ($profile->getDiscordUsername() != $member['user']['username']) {
                    // Just in case, I think discriminators for users are gone though.
                    if ($member['user']['discriminator']) {
                        $profile->setDiscordUsername($member['user']['username'] . '#' . $member['user']['discriminator']);
                    } else {
                        $profile->setDiscordUsername($member['user']['username']);
                    }
                    $profileRepository->save($profile);
                }

                $roles = $profile->getUser()->getRoles();

                foreach ($roles as $role) {
                    if ($role->getName() == 'member.current') {
                        $found = true;
                        break;
                    }
                }
            }

            // If they're not found, send them a message to notify
            // them, and then strip the roles.
            if (! $found) {
                Log::info('DiscordAuditJob@handle: Removing roles from unknown Discord user ' .
                          $member['user']['username'] . '#' . $member['user']['discriminator']);

                try {
                    $this->stripRoles($discord, $member); // Remove roles first (more likely to succeed than messaging)
                    $this->notifyDiscordUser($discord, $member);
                } catch (Exception $exception) {
                    Log::info('DiscordAuditJob@handle: Failed to remove roles or message user. ' .
                              $exception->getMessage());
                }
            }
        }
    }
}
