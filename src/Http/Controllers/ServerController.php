<?php

declare(strict_types=1);

namespace EggChanger\Http\Controllers;

use EggChanger\Models\Group;
use EggChanger\Panel\Panel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Server;

final class ServerController
{
    public function __construct(private readonly Panel $panel) {}

    public function options(Request $request, Server $server): JsonResponse
    {
        $this->authorize($request, $server);

        return new JsonResponse([
            'current' => ['id' => $server->egg->id, 'name' => $server->egg->name],
            'eggs' => $this->choices($server)->map(static fn (Egg $egg): array => [
                'id' => $egg->id,
                'name' => $egg->name,
                'description' => $egg->description,
            ])->values(),
            'can_reinstall' => (bool) $request->user()?->can('settings.reinstall', $server),
        ]);
    }

    public function change(Request $request, Server $server): JsonResponse
    {
        $this->authorize($request, $server);

        $data = $request->validate([
            'egg' => ['required', 'integer'],
            'keep_variables' => ['required', 'boolean'],
            'reinstall' => ['required', 'boolean'],
        ]);

        $egg = $this->choices($server)->firstWhere('id', (int) $data['egg']);
        abort_if($egg === null, 422, 'This server cannot switch to that egg.');
        abort_unless($this->panel->isStopped($server), 409, 'Stop the server before changing its egg.');
        abort_if($data['reinstall'] && ! $request->user()?->can('settings.reinstall', $server), 403, 'You do not have permission to reinstall this server.');

        $server = $this->panel->changeEgg($server, $egg, (bool) $data['keep_variables']);

        if ($data['reinstall']) {
            $this->panel->reinstall($server);
        }

        return new JsonResponse(['current' => ['id' => $egg->id, 'name' => $egg->name], 'reinstalling' => (bool) $data['reinstall']]);
    }

    /** @return Collection<int, Egg> */
    private function choices(Server $server): Collection
    {
        return Group::query()
            ->whereHas('eggs', static fn ($query) => $query->where('eggs.id', $server->egg_id))
            ->with('eggs')
            ->get()
            ->flatMap(static fn (Group $group) => $group->eggs)
            ->unique('id')
            ->reject(static fn (Egg $egg): bool => $egg->id === $server->egg_id)
            ->sortBy('name')
            ->values();
    }

    private function authorize(Request $request, Server $server): void
    {
        abort_unless((bool) $request->user()?->can('ext.egg-changer.change', $server), 403, 'You do not have permission to change the egg.');
    }
}
