<?php

declare(strict_types=1);

namespace EggChanger\Panel;

use Pterodactyl\Contracts\Servers\ChangesServerEgg;
use Pterodactyl\Contracts\Servers\ReinstallsServers;
use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Facades\Daemon;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Server;

/**
 * The only place this extension touches Panel internals.
 */
final readonly class Panel
{
    public function __construct(private ChangesServerEgg $eggs, private ReinstallsServers $reinstalls) {}

    public function isStopped(Server $server): bool
    {
        try {
            return in_array(Daemon::server($server)->details()['state'] ?? 'offline', ['offline', 'stopped'], true);
        } catch (DaemonConnectionException) {
            return false;
        }
    }

    public function changeEgg(Server $server, Egg $egg, bool $keepVariables): Server
    {
        $previous = $server->egg->name;
        $server = $this->eggs->change($server, $egg, $keepVariables);

        Activity::event('server:startup.edit')->subject($server)->property(['variable' => 'Egg', 'old' => $previous, 'new' => $egg->name])->log();

        return $server;
    }

    public function reinstall(Server $server): void
    {
        $this->reinstalls->reinstall($server);
        Activity::event('server:reinstall')->subject($server)->log();
    }
}
