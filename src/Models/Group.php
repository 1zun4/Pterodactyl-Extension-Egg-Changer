<?php

declare(strict_types=1);

namespace EggChanger\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Pterodactyl\Models\Egg;

/**
 * Eggs a server may switch between. A server can move to any egg that shares a group with its current one.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 */
final class Group extends Model
{
    protected $table = 'ext_egg_changer_groups';

    protected $fillable = ['name', 'description'];

    /** @return BelongsToMany<Egg, $this> */
    public function eggs(): BelongsToMany
    {
        return $this->belongsToMany(Egg::class, 'ext_egg_changer_group_eggs', 'group_id', 'egg_id');
    }
}
