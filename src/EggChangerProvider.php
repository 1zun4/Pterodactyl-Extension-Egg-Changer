<?php

declare(strict_types=1);

namespace EggChanger;

use Pterodactyl\Extensions\ExtensionProvider;

class EggChangerProvider extends ExtensionProvider
{
    public function boot(): void
    {
        $this->registerApiRoutes();
        $this->loadExtensionMigrations();

        $this->registerPermissions('Egg changer.', [
            'change' => 'Switch the server to another egg the administrator allows.',
        ]);
    }
}
